<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MediaFileController extends Controller
{
    public function editorImages(): JsonResponse
    {
        $images = MediaFile::query()
            ->where('file_type', 'image')
            ->where('status', true)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (MediaFile $image) => [
                'id' => $image->id,
                'name' => $image->original_name,
                'alt' => $image->alt_text ?: $image->original_name,
                'url' => $image->publicUrl(),
            ]);

        return response()->json($images);
    }

    public function editorUpload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $validated['file'];
        $path = $file->store('media', 'public');
        $media = MediaFile::create([
            'user_id' => Auth::id(),
            'name' => pathinfo($file->hashName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'disk' => 'public',
            'file_type' => 'image',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'status' => true,
        ]);

        return response()->json([
            'location' => $media->publicUrl(),
            'id' => $media->id,
        ]);
    }

    public function index()
    {
        $mediaFiles = MediaFile::latest()->paginate(12);

        return view('admin.media-files.index', compact('mediaFiles'));
    }

    public function create()
    {
        return view('admin.media-files.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'nullable|required_without:files|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,ppt,pptx',
            'files' => 'nullable|required_without:file|array|max:20',
            'files.*' => 'file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,ppt,pptx',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $files = $request->hasFile('files')
            ? $request->file('files')
            : [$request->file('file')];

        foreach ($files as $file) {
            $path = $file->store('media', 'public');

            MediaFile::create([
                'user_id' => Auth::id(),
                'name' => pathinfo($file->hashName(), PATHINFO_FILENAME),
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => 'public',
                'file_type' => $this->detectFileType($file->getMimeType()),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'alt_text' => count($files) === 1 ? $request->alt_text : null,
                'description' => count($files) === 1 ? $request->description : null,
                'status' => true,
            ]);
        }

        return redirect()
            ->route('admin.media-files.index')
            ->with('success', count($files) === 1
                ? 'Archivo subido correctamente.'
                : count($files).' archivos subidos correctamente.');
    }

    public function show(MediaFile $mediaFile)
    {
        return view('admin.media-files.show', compact('mediaFile'));
    }

    public function edit(MediaFile $mediaFile)
    {
        return view('admin.media-files.edit', compact('mediaFile'));
    }

    public function update(Request $request, MediaFile $mediaFile)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $mediaFile->update([
            'alt_text' => $request->alt_text,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.media-files.index')
            ->with('success', 'Archivo actualizado correctamente.');
    }

    public function destroy(MediaFile $mediaFile)
    {
        if ($mediaFile->file_path && Storage::disk($mediaFile->disk)->exists($mediaFile->file_path)) {
            Storage::disk($mediaFile->disk)->delete($mediaFile->file_path);
        }

        $mediaFile->delete();

        return redirect()
            ->route('admin.media-files.index')
            ->with('success', 'Archivo eliminado correctamente.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'media_ids' => 'required|array|min:1',
            'media_ids.*' => 'integer|exists:media_files,id',
        ]);

        $mediaFiles = MediaFile::whereIn('id', $validated['media_ids'])->get();

        foreach ($mediaFiles as $mediaFile) {
            if ($mediaFile->file_path && Storage::disk($mediaFile->disk)->exists($mediaFile->file_path)) {
                Storage::disk($mediaFile->disk)->delete($mediaFile->file_path);
            }

            $mediaFile->delete();
        }

        return redirect()
            ->route('admin.media-files.index')
            ->with('success', $mediaFiles->count().' archivos eliminados correctamente.');
    }

    private function detectFileType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if ($mimeType === 'application/pdf') {
            return 'pdf';
        }

        if (str_contains($mimeType, 'word')) {
            return 'document';
        }

        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
            return 'spreadsheet';
        }

        if (str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation')) {
            return 'presentation';
        }

        return 'other';
    }
}
