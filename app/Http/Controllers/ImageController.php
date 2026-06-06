<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,gif,webp|max:10240',
        ]);

        $file     = $request->file('image');
        $path     = $file->store('chat-images', 'public');
        $mimeType = $file->getMimeType();
        $filename = $file->getClientOriginalName();

        return response()->json([
            'path'     => $path,
            'url'      => Storage::url($path),
            'mimeType' => $mimeType,
            'filename' => $filename,
        ]);
    }
}
