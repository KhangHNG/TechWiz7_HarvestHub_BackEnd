<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait SeedsAudit
{
    protected function adminId(): int
    {
        return (int) User::query()->where('email', 'admin@example.com')->value('id');
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $attributes
     */
    protected function createAudited(string $model, array $attributes): Model
    {
        $adminId = $this->adminId();
        $attributes['created_by'] = $adminId;
        $attributes['updated_by'] = $adminId;

        return $model::unguarded(fn () => $model::query()->create($attributes));
    }
}
