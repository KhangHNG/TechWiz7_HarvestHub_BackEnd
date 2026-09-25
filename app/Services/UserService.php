<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%")
                    ->orWhere('phone', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->boolean('all')) {
            return $query->orderBy('full_name', 'asc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
    }

    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['password_hash'] = $data['password'];
            unset($data['password']);

            return User::create($data);
        });
    }

    public function updateUser(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password'])) {
                $data['password_hash'] = $data['password'];
                unset($data['password']);
            }

            $user->update($data);

            return $user->fresh();
        });
    }

    public function deleteUser(User $user)
    {
        return DB::transaction(function () use ($user) {
            if ($user->role === 'FARMER' && $user->farmer()->exists()) {
                throw new \Exception('Không thể xóa người dùng đang là nông dân.');
            }

            return $user->delete();
        });
    }
}
