<?php

namespace App\Services;

use App\Domains\Shared\Models\TempUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class FileUploadService
{
    public function upload(UploadedFile $file, string $collection = 'uploads')
    {
        // اعتبارسنجی اندازه (حداکثر 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('فایل بیش از 5 مگابایت است.');
        }


        $temp = TempUpload::create(['user_id' => optional(Auth::user())->id]);

        // 2) روی TempUpload ذخیره کن
        $media = $temp
            ->addMedia($file)
            ->preservingOriginal()  // اختیاری
            ->toMediaCollection('uploads'); // کالکشن ثابت temp

        // 3) خروجی مورد نیاز فرانت
        return $media;
    }

    public function finalize(string $fileId, array $metadata = []): Media
    {
        $media = Media::findOrFail($fileId);
        $media->update(['custom_properties' => $metadata]);
        return $media;
    }
}
