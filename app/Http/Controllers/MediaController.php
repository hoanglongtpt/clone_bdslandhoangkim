<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    private function authorizeProperty(Request $request, Property $property): void
    {
        abort_unless(Property::query()->visibleTo($request->user())->whereKey($property->id)->exists(), 403);
    }

    public function propertyImages(Request $request, Property $property)
    {
        $this->authorizeProperty($request, $property);

        $images = $property->media()
            ->where('media_type', 'image')
            ->orderBy('source_id')
            ->get()
            ->map(fn (Media $media) => array_filter([
                'src' => route('media.show', $media),
                'delete_url' => $request->user()->isAdmin() ? route('properties.images.destroy', [$property, $media]) : null,
            ]));

        return response()->json(['property' => $property->code, 'images' => $images]);
    }

    public function store(Request $request, Property $property)
    {
        $this->authorizeProperty($request, $property);
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'images.required' => 'Vui lòng chọn ít nhất một ảnh.',
            'images.max' => 'Mỗi lần chỉ được tải tối đa 20 ảnh.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'images.*.max' => 'Mỗi ảnh không được vượt quá 10 MB.',
        ]);

        $uploaded = [];
        foreach ($request->file('images', []) as $image) {
            $uuid = (string) Str::uuid();
            $path = $image->storeAs("property-media/{$property->id}", $uuid.'.'.$image->extension(), 'local');
            abort_unless($path, 500, 'Không thể lưu ảnh.');
            $uploaded[] = $path;
            Media::create([
                'media_key' => 'upload-'.$uuid,
                'property_id' => $property->id,
                'media_type' => 'image',
                'source_id' => 'upload-'.$uuid,
                'source_url' => null,
                'local_path' => 'storage:'.$path,
                'download_status' => 'uploaded',
            ]);
        }

        ActivityLog::record('media.uploaded', $property, 'Tải '.count($uploaded)." ảnh lên căn {$property->code}", ['paths' => $uploaded]);

        return back()->with('success', 'Đã tải lên '.count($uploaded).' ảnh.');
    }

    public function destroy(Request $request, Property $property, Media $media)
    {
        $this->authorizeProperty($request, $property);
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless((int) $media->property_id === (int) $property->id && $media->media_type === 'image', 404);

        $storagePath = str_starts_with((string) $media->local_path, 'storage:')
            ? Str::after((string) $media->local_path, 'storage:') : null;
        $media->delete();
        if ($storagePath && str_starts_with($storagePath, 'property-media/')) {
            Storage::disk('local')->delete($storagePath);
        }
        ActivityLog::record('media.deleted', $property, "Xóa ảnh khỏi căn {$property->code}", ['media_key' => $media->getKey()]);

        return back()->with('success', 'Đã xóa ảnh.');
    }

    public function show(Request $request, Media $media)
    {
        abort_unless(Property::query()->visibleTo($request->user())->whereKey($media->property_id)->exists(), 403);
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $media->local_path);
        $base = rtrim((string) config('crm.crawl_data_path'), '\\/');
        $path = $base.DIRECTORY_SEPARATOR.$relative;
        $realBase = realpath($base);
        $realPath = realpath($path);
        if (str_starts_with((string) $media->local_path, 'storage:')) {
            $storagePath = Str::after((string) $media->local_path, 'storage:');
            abort_unless(str_starts_with($storagePath, 'property-media/') && Storage::disk('local')->exists($storagePath), 404);

            return response()->file(Storage::disk('local')->path($storagePath), [
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }
        if ($media->local_path && $realBase && $realPath
            && str_starts_with(mb_strtolower($realPath), mb_strtolower($realBase.DIRECTORY_SEPARATOR))
            && File::isFile($realPath)) {
            return response()->file($realPath);
        }
        if ($media->source_url) {
            return redirect()->away($media->source_url);
        }
        abort(404);
    }
}
