<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileUploadService
{
    public function upload(UploadedFile $file, string $collection = 'default', ?string $disk = null): Media
    {
        // اعتبارسنجی اندازه (حداکثر 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('فایل بیش از 5 مگابایت است.');
        }

        // ذخیره فایل
        return auth()->user()->addMedia($file)
            ->toMediaCollection($collection, $disk ?? config('filesystems.default'));
    }

    public function finalize(string $fileId, array $metadata = []): Media
    {
        $media = Media::findOrFail($fileId);
        $media->update(['custom_properties' => $metadata]);
        return $media;
    }
}
