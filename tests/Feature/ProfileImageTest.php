<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacing_avatar_and_cover_deletes_the_previous_cloudinary_image(): void
    {
        $user = $this->user('CUSTOMER', 'khach@example.com', '0900000003');
        $owner = $this->user('FARMER', 'farmer@example.com', '0900000001');
        $farmer = Farmer::query()->create([
            'user_id' => $owner->id,
            'business_name' => 'Vườn A',
            'is_accepting_orders' => true,
        ]);

        $customerFirst = 'https://res.cloudinary.com/demo/image/upload/v1/avatars/customer-a.jpg';
        $customerSecond = 'https://res.cloudinary.com/demo/image/upload/v1/avatars/customer-b.jpg';
        $farmerAvatar = 'https://res.cloudinary.com/demo/image/upload/v1/avatars/farmer.jpg';
        $firstCover = 'https://res.cloudinary.com/demo/image/upload/v1/covers/a.jpg';
        $secondCover = 'https://res.cloudinary.com/demo/image/upload/v1/covers/b.jpg';

        $cloudinary = Mockery::mock(CloudinaryService::class);
        $cloudinary->shouldReceive('upload')
            ->times(3)
            ->with(Mockery::type(UploadedFile::class), 'avatars')
            ->andReturn($customerFirst, $customerSecond, $farmerAvatar);
        $cloudinary->shouldReceive('upload')
            ->twice()
            ->with(Mockery::type(UploadedFile::class), 'covers')
            ->andReturn($firstCover, $secondCover);
        $cloudinary->shouldReceive('deleteByUrl')->once()->with($customerFirst);
        $cloudinary->shouldReceive('deleteByUrl')->once()->with($firstCover);
        $cloudinary->shouldReceive('deleteByUrl')->once()->with($customerSecond);
        $cloudinary->shouldReceive('deleteByUrl')->once()->with($farmerAvatar);
        $cloudinary->shouldReceive('deleteByUrl')->once()->with($secondCover);
        $this->app->instance(CloudinaryService::class, $cloudinary);

        $this->post('/api/users/'.$user->id.'/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.avatar_url', $customerFirst);

        $this->post('/api/users/'.$user->id.'/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar-2.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.avatar_url', $customerSecond);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'avatar_url' => $customerSecond,
        ]);

        $this->withToken(JWTAuth::fromUser($user))
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.avatar_url', $customerSecond);

        $this->post('/api/farmers/'.$farmer->id.'/cover', [
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.cover_url', $firstCover);

        $this->post('/api/farmers/'.$farmer->id.'/cover', [
            'cover' => UploadedFile::fake()->image('cover-2.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.cover_url', $secondCover)
            ->assertJsonPath('data.avatar_url', null);

        $this->assertDatabaseHas('farmers', [
            'id' => $farmer->id,
            'cover_url' => $secondCover,
        ]);

        $this->post('/api/users/'.$owner->id.'/avatar', [
            'avatar' => UploadedFile::fake()->image('farmer.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.avatar_url', $farmerAvatar);

        $this->getJson('/api/farmers/'.$farmer->id)
            ->assertOk()
            ->assertJsonPath('data.avatar_url', $farmerAvatar)
            ->assertJsonPath('data.cover_url', $secondCover);

        $user->refresh();
        $owner->refresh();
        $farmer->refresh();
        $user->delete();
        $owner->delete();
        $farmer->delete();
    }

    private function user(string $role, string $email, string $phone): User
    {
        return User::query()->create([
            'full_name' => $email,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => 'secret',
            'address' => '1 Đường A',
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
}
