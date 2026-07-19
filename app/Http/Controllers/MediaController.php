<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    public function show(Request $request, Media $media)
    {
        abort_unless(Property::query()->visibleTo($request->user())->whereKey($media->property_id)->exists(), 403);
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $media->local_path);
        $base = rtrim((string) config('crm.crawl_data_path'), '\\/');
        $path = $base.DIRECTORY_SEPARATOR.$relative;
        $realBase = realpath($base);
        $realPath = realpath($path);
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
