<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPictureTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_pictures_come_back_as_full_links(): void
    {
        $uploaded = 'https://res.cloudinary.com/demo/image/upload/v1/categories/tea.png';
        Category::query()->create(['name' => 'Honey', 'image_url' => 'seed/categories/honey.webp']);
        Category::query()->create(['name' => 'Tea', 'image_url' => $uploaded]);
        Category::query()->create(['name' => 'Rice']);

        $pictures = collect($this->getJson('/api/categories?all=1')->assertOk()->json('data'))
            ->pluck('image_url', 'name');

        $this->assertSame(url('seed/categories/honey.webp'), $pictures['Honey']);
        $this->assertSame($uploaded, $pictures['Tea']);
        $this->assertNull($pictures['Rice']);
    }

    public function test_existing_categories_get_a_seeded_picture_unless_they_have_one(): void
    {
        foreach (['Vegetables', 'Fruits', 'Grains', 'Dairy', 'Herbs', 'Organic', 'Legumes', 'Spices', 'Mushrooms', 'Honey'] as $name) {
            Category::query()->create(['name' => $name]);
        }
        $uploaded = 'https://res.cloudinary.com/demo/image/upload/v1/categories/fruit.png';
        Category::query()->where('name', 'Fruits')->update(['image_url' => $uploaded]);

        (require database_path('migrations/2026_09_28_133828_fill_category_pictures.php'))->up();

        $pictures = Category::query()->pluck('image_url', 'name');
        $this->assertSame($uploaded, $pictures['Fruits']);
        foreach ($pictures->except('Fruits') as $path) {
            $this->assertStringStartsWith('seed/categories/', $path);
            $this->assertFileExists(public_path($path));
        }
    }
}
