<?php

namespace App\Services;

use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class FarmerService
{
    public function __construct(private CloudinaryService $cloudinary) {}

    public function getFarmers(Request $request)
    {
        $query = Farmer::query()->with('user');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('business_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('market_id')) {
            $query->where('market_id', $request->market_id);
        }

        if ($request->boolean('all')) {
            return $query->orderBy('business_name', 'asc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
    }

    public function createFarmer(array $data)
    {
        return DB::transaction(fn () => Farmer::create($data));
    }

    public function updateFarmer(Farmer $farmer, array $data)
    {
        return DB::transaction(function () use ($farmer, $data) {
            $farmer->update($data);

            return $farmer->fresh();
        });
    }

    public function updateCover(Farmer $farmer, UploadedFile $file): Farmer
    {
        $url = $this->cloudinary->upload($file, 'covers');

        try {
            $farmer->update(['cover_url' => $url]);
        } catch (\Throwable $exception) {
            $this->cloudinary->deleteByUrl($url);

            throw $exception;
        }

        return $farmer->fresh('user');
    }

    public function deleteFarmer(Farmer $farmer)
    {
        return DB::transaction(function () use ($farmer) {
            if ($farmer->products()->count() > 0) {
                throw new \Exception('Không thể xóa nông dân đang có sản phẩm.');
            }

            return $farmer->delete();
        });
    }
}
