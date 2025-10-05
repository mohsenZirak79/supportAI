<?php

namespace App\Domains\UserPanel\Controllers;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // مرحله ۱: دریافت pre-signed URL (در اینجا فقط مسیر موقت)
    public function store(Request $request)
    {
        $request->validate([
            'purpose' => 'required|in:user,ticket,ai',
            'mime_type' => 'required|string',
            'file_name' => 'required|string',
        ]);

        // ایجاد مسیر موقت
        $user = Auth::user();
        $dir = "uploads/{$request->purpose}/" . ($user ? $user->id : 'anonymous') . '/' . now()->format('Y/m');
        $fileName = Str::uuid() . '.' . explode('/', $request->mime_type)[1];
        $path = "{$dir}/{$fileName}";

        return response()->json([
            'file_id' => Str::uuid(),
            'upload_url' => route('files.upload', ['file_id' => Str::uuid()]), // برای سادگی
            'path' => $path,
            'expires_in' => 600
        ]);
    }

    // مرحله ۲: آپلود واقعی (با فرم)
    public function upload(Request $request, string $file_id)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,webm,mp3'
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        $dir = "uploads/user/" . ($user ? $user->id : 'anonymous') . '/' . now()->format('Y/m');
        $path = $file->store($dir, 'public');

        $model = File::create([
            'user_id' => $user?->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'file_id' => $model->id,
            'url' => $model->full_url
        ]);
    }

    // مرحله ۳: finalize (اختیاری برای metadata)
    public function finalize(Request $request, File $file)
    {
        // اسکن آنتی‌ویروس (ClamAV) - در اینجا skip شده
        $file->update(['status' => 'scanned']);
        return response()->json($file);
    }
}
