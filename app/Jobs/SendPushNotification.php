<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public array $data,
    ) {}

    public function handle(NotificationService $notifications): void
    {
        $user = User::query()->find($this->userId);

        if (! $user) {
            return;
        }

        $notifications->push($user, $this->title, $this->body, $this->data);
    }
}
