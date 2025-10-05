<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class StructuredPathGenerator implements PathGenerator
{
    /** مسیر عمومی فایل اصلی */
    public function getPath(Media $media): string
    {
        $date = optional($media->created_at)->format('Y/m/d') ?? now()->format('Y/m/d');
        $collection = $media->collection_name ?: 'files';

        // در نسخه‌های جدید ستون uuid داریم؛ اگر نبود از id استفاده کن
        $folder = $media->uuid ?? $media->id;

        // مثال خروجی: message_voices/2025/10/05/UUID/
        return "{$collection}/{$date}/{$folder}/";
    }

    /** مسیر تبدیل‌ها (conversions) — کنار فایل اصلی در پوشهٔ جدا */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    /** مسیر responsive images — لازمهٔ interface در نسخه‌های جدید */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
