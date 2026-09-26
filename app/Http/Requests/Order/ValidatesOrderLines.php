<?php

namespace App\Http\Requests\Order;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesOrderLines
{
    protected function validateOrderLines(Validator $validator, mixed $farmerId, bool $enforceFarmer = true): void
    {
        $items = $this->input('items', []);
        if (! is_array($items) || $items === []) {
            return;
        }

        $productIds = collect($items)->pluck('product_id')->filter()->unique()->values();
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $product = $products->get($item['product_id'] ?? null);
            if (! $product) {
                continue;
            }

            if ($enforceFarmer && (int) $product->farmer_id !== (int) $farmerId) {
                $validator->errors()->add(
                    "items.$index.product_id",
                    'Sản phẩm không thuộc nông dân của đơn hàng.',
                );
            }

            if ((int) $item['quantity'] > (int) $product->stock_qty) {
                $validator->errors()->add(
                    "items.$index.quantity",
                    'Số lượng vượt quá tồn kho.',
                );
            }

            if (! $this->amountsMatch($item['unit_price'] ?? null, $product->price)) {
                $validator->errors()->add(
                    "items.$index.unit_price",
                    'Đơn giá không khớp giá sản phẩm.',
                );
            }

            $expected = (float) ($item['unit_price'] ?? 0) * (int) ($item['quantity'] ?? 0);
            if (! $this->amountsMatch($item['line_total'] ?? null, $expected)) {
                $validator->errors()->add(
                    "items.$index.line_total",
                    'Thành tiền không khớp đơn giá nhân số lượng.',
                );
            }
        }
    }

    protected function amountsMatch(mixed $left, mixed $right): bool
    {
        return abs((float) $left - (float) $right) < 0.01;
    }
}
