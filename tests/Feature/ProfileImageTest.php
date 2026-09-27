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
            'business_name' => 'Garden A',
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

        $this->withToken(JWTAuth::fromUser($user))
            ->post('/api/users/'.$user->id.'/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ])->assertOk()
            ->assertJsonPath('data.avatar_url', $customerFirst);

        $this->withToken(JWTAuth::fromUser($user))
            ->post('/api/users/'.$user->id.'/avatar', [
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

        $this->withToken(JWTAuth::fromUser($owner))
            ->post('/api/farmers/'.$farmer->id.'/cover', [
                'cover' => UploadedFile::fake()->image('cover.jpg'),
            ])->assertOk()
            ->assertJsonPath('data.cover_url', $firstCover);

        $this->withToken(JWTAuth::fromUser($owner))
            ->post('/api/farmers/'.$farmer->id.'/cover', [
                'cover' => UploadedFile::fake()->image('cover-2.jpg'),
            ])->assertOk()
            ->assertJsonPath('data.cover_url', $secondCover)
            ->assertJsonPath('data.avatar_url', null);

        $this->assertDatabaseHas('farmers', [
            'id' => $farmer->id,
            'cover_url' => $secondCover,
        ]);

        $this->withToken(JWTAuth::fromUser($owner))
            ->post('/api/users/'.$owner->id.'/avatar', [
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

    public function test_avatar_and_cover_over_one_and_a_half_megabytes_are_rejected(): void
    {
        $user = $this->user('CUSTOMER', 'khach@example.com', '0900000003');
        $owner = $this->user('FARMER', 'farmer@example.com', '0900000001');
        $farmer = Farmer::query()->create([
            'user_id' => $owner->id,
            'business_name' => 'Garden A',
            'is_accepting_orders' => true,
        ]);

        $cloudinary = Mockery::mock(CloudinaryService::class);
        $cloudinary->shouldNotReceive('upload');
        $this->app->instance(CloudinaryService::class, $cloudinary);

        $tooLarge = UploadedFile::fake()->image('big.jpg')->size(1537);

        $this->withToken(JWTAuth::fromUser($user))
            ->post('/api/users/'.$user->id.'/avatar', [
                'avatar' => $tooLarge,
            ])->assertUnprocessable()
            ->assertJsonPath('errors.avatar.0', 'The avatar may not be greater than 1.5 MB.');

        $this->withToken(JWTAuth::fromUser($owner))
            ->post('/api/farmers/'.$farmer->id.'/cover', [
                'cover' => UploadedFile::fake()->image('big-cover.jpg')->size(1537),
            ])->assertUnprocessable()
            ->assertJsonPath('errors.cover.0', 'The cover image may not be greater than 1.5 MB.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'avatar_url' => null,
        ]);
        $this->assertDatabaseHas('farmers', [
            'id' => $farmer->id,
            'cover_url' => null,
        ]);
    }

    public function test_only_the_owner_can_change_avatar_and_cover(): void
    {
        $customer = $this->user('CUSTOMER', 'khach@example.com', '0900000003');
        $other = $this->user('CUSTOMER', 'khac@example.com', '0900000004');
        $owner = $this->user('FARMER', 'farmer@example.com', '0900000001');
        $stranger = $this->user('FARMER', 'la@example.com', '0900000002');
        $farmer = Farmer::query()->create([
            'user_id' => $owner->id,
            'business_name' => 'Garden A',
            'is_accepting_orders' => true,
        ]);

        $cloudinary = Mockery::mock(CloudinaryService::class);
        $cloudinary->shouldNotReceive('upload');
        $this->app->instance(CloudinaryService::class, $cloudinary);

        $image = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $cover = ['cover' => UploadedFile::fake()->image('cover.jpg')];

        $this->post('/api/users/'.$customer->id.'/avatar', $image)
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Token is missing or could not be decoded.');

        $this->post('/api/farmers/'.$farmer->id.'/cover', $cover)
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Token is missing or could not be decoded.');

        $this->withToken(JWTAuth::fromUser($other))
            ->post('/api/users/'.$customer->id.'/avatar', $image)
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to perform this action.');

        $this->withToken(JWTAuth::fromUser($customer))
            ->post('/api/farmers/'.$farmer->id.'/cover', $cover)
            ->assertForbidden();

        $this->withToken(JWTAuth::fromUser($stranger))
            ->post('/api/farmers/'.$farmer->id.'/cover', $cover)
            ->assertForbidden();
    }

    private function user(string $role, string $email, string $phone): User
    {
        return User::query()->create([
            'full_name' => $email,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => 'secret',
            'address' => '1 Street A',
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
}
