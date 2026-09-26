<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationService
{
    public function orderCreated(Order $order): void
    {
        if ($order->status !== 'PENDING') {
            return;
        }

        $this->notify(
            $this->farmerUser($order),
            'order_pending',
            'Đơn hàng mới',
            'Bạn có đơn hàng mới cần xác nhận.',
            ['order_id' => (string) $order->id],
        );
    }

    public function orderStatusChanged(Order $order, string $previousStatus): void
    {
        if ($order->status === $previousStatus || $order->status === 'CART') {
            return;
        }

        [$user, $type, $title, $body] = match ($order->status) {
            'PENDING' => [
                $this->farmerUser($order),
                'order_pending',
                'Đơn hàng mới',
                'Bạn có đơn hàng mới cần xác nhận.',
            ],
            'CONFIRMED' => [
                $order->customer,
                'order_confirmed',
                'Đơn đã được xác nhận',
                'Nông dân đã xác nhận đơn hàng của bạn.',
            ],
            'READY_FOR_PICKUP' => [
                $order->customer,
                'order_ready',
                'Đơn sẵn sàng để lấy',
                'Đơn hàng của bạn đã sẵn sàng để lấy.',
            ],
            'COMPLETED' => [
                $order->customer,
                'order_completed',
                'Đơn đã hoàn thành',
                'Đơn hàng của bạn đã hoàn thành.',
            ],
            'CANCELLED' => $this->cancelledRecipient($order),
            default => [null, null, null, null],
        };

        if (! $user || ! $type) {
            return;
        }

        $this->notify($user, $type, $title, $body, [
            'order_id' => (string) $order->id,
        ]);
    }

    public function stockChanged(Product $product, int $previous, int $current): void
    {
        if ($previous === $current) {
            return;
        }

        $type = null;
        $title = null;
        $body = null;

        if ($current === 0 && $previous > 0) {
            $type = 'stock_out';
            $title = 'Hết hàng';
            $body = $product->name.' đã hết hàng.';
        } elseif ($current >= 1 && $current <= 20 && $previous > 20) {
            $type = 'stock_low';
            $title = 'Sắp hết hàng';
            $body = $product->name.' còn '.$current.' sản phẩm.';
        }

        if (! $type) {
            return;
        }

        $product->loadMissing('farmer.user');

        $this->notify($product->farmer?->user, $type, $title, $body, [
            'product_id' => (string) $product->id,
        ]);
    }

    public function registerToken(User $user, string $token, string $platform): DeviceToken
    {
        return DeviceToken::query()->updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $user->id,
                'platform' => $platform,
            ],
        );
    }

    public function deleteToken(User $user, string $token): void
    {
        DeviceToken::query()
            ->where('user_id', $user->id)
            ->where('token', $token)
            ->delete();
    }

    /**
     * @param  array<string, string>  $data
     */
    public function notify(?User $user, string $type, string $title, string $body, array $data): void
    {
        if (! $user) {
            return;
        }

        $payload = array_merge(['type' => $type], $data);

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $payload,
        ]);

        $this->push($user, $title, $body, $payload);
    }

    /**
     * @param  array<string, string>  $data
     */
    private function push(User $user, string $title, string $body, array $data): void
    {
        $tokens = DeviceToken::query()
            ->where('user_id', $user->id)
            ->pluck('token')
            ->all();

        if ($tokens === []) {
            return;
        }

        $messaging = $this->messaging();

        if (! $messaging) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(['title' => $title, 'body' => $body])
                ->withData($data);

            $report = $messaging->sendMulticast($message, $tokens);

            foreach ($report->failures()->getItems() as $failure) {
                if ($failure->messageTargetWasInvalid() || $failure->messageWasSentToUnknownToken()) {
                    DeviceToken::query()->where('token', $failure->target()->value())->delete();
                }
            }
        } catch (Throwable $e) {
            Log::warning('FCM send failed', ['user_id' => $user->id, 'message' => $e->getMessage()]);
        }
    }

    private function messaging(): ?Messaging
    {
        $account = $this->serviceAccount();

        if ($account === null) {
            return null;
        }

        return (new Factory)->withServiceAccount($account)->createMessaging();
    }

    private function serviceAccount(): string|array|null
    {
        $value = config('services.firebase.credentials');

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $trimmed = trim($value);

        if (str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);

            return is_array($decoded) ? $decoded : null;
        }

        $raw = base64_decode($trimmed, true);

        if (is_string($raw) && str_starts_with(trim($raw), '{')) {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        }

        $path = str_starts_with($trimmed, '/') ? $trimmed : base_path($trimmed);

        return is_file($path) ? $path : null;
    }

    private function farmerUser(Order $order): ?User
    {
        $order->loadMissing('farmer.user');

        return $order->farmer?->user;
    }

    /**
     * @return array{0: ?User, 1: ?string, 2: ?string, 3: ?string}
     */
    private function cancelledRecipient(Order $order): array
    {
        $order->loadMissing('farmer.user', 'customer');
        $actor = $this->actor();
        $farmerUser = $order->farmer?->user;

        if ($actor && (int) $actor->id === (int) $order->customer_id) {
            return [$farmerUser, 'order_cancelled', 'Đơn đã bị hủy', 'Khách hàng đã hủy đơn hàng.'];
        }

        if ($actor && $farmerUser && (int) $actor->id === (int) $farmerUser->id) {
            return [$order->customer, 'order_cancelled', 'Đơn đã bị hủy', 'Nông dân đã hủy đơn hàng.'];
        }

        return [null, null, null, null];
    }

    private function actor(): ?User
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return $user instanceof User ? $user : null;
        } catch (Throwable) {
            return null;
        }
    }
}
