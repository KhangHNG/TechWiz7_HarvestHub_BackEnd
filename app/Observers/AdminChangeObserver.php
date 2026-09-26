<?php

namespace App\Observers;

use App\Services\AdminNotifier;
use Illuminate\Database\Eloquent\Model;

class AdminChangeObserver
{
    public function created(Model $model): void
    {
        app(AdminNotifier::class)->changed($model, 'created');
    }

    public function updated(Model $model): void
    {
        if ($model->wasRecentlyCreated) {
            return;
        }

        app(AdminNotifier::class)->changed($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        app(AdminNotifier::class)->changed($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        app(AdminNotifier::class)->changed($model, 'restored');
    }
}
