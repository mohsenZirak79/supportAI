<?php

namespace App\Services;

use App\Domains\Shared\Models\TempUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class FileUploadService
{
    public function upload(UploadedFile $file, string $collection = 'uploads')
    {
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('فایل بیش از 5 مگابایت است.');
        }

        $temp = TempUpload::create(['user_id' => optional(Auth::user())->id]);

        return $temp
            ->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection($collection, 'public'); // ← هم کالکشن، هم دیسک
    }

    public function finalize(string $fileId, array $metadata = []): Media
    {
        $media = Media::findOrFail($fileId);
        $media->update(['custom_properties' => $metadata]);
        return $media;
    }
}
