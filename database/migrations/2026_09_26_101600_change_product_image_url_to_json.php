<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        DB::table('products')->select('id', 'image_url')->orderBy('id')->lazy()->each(function (object $product): void {
            DB::table('products')->where('id', $product->id)->update([
                'image_url' => json_encode($this->toList($product->image_url)),
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        DB::table('products')->select('id', 'image_url')->orderBy('id')->lazy()->each(function (object $product): void {
            $images = $this->toList($product->image_url);

            DB::table('products')->where('id', $product->id)->update([
                'image_url' => $images[0] ?? null,
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->change();
        });
    }

    /**
     * @return array<int, string>
     */
    private function toList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, 'is_string'));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values(array_filter($decoded, 'is_string'));
        }

        return [$value];
    }
};
