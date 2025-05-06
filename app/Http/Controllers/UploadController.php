<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadedFile;
use App\Jobs\ProcessCsvUpload;
use App\Http\Resources\UploadedFileResource;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function index()
    {
        return view('uploads.index');
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');
        $contents = file_get_contents($file);
        $hash = md5($contents);
        $filename = $file->getClientOriginalName();

        $upload = UploadedFile::updateOrCreate(
            ['hash' => $hash],
            [
                'filename' => $filename,
                'path' => $hash . '_' . $filename,
                'uploaded_at' => now(),
                'status' => 'Pending'
            ]
        );

        $file->storeAs('uploads', $hash . '_' . $filename, 'public');
        ProcessCsvUpload::dispatch($upload->id, 'uploads/' . $hash . '_' . $filename);

        ProcessCsvUpload::dispatch($upload->id, 'uploads/' . $upload->path);

        return back()->with('success', 'File uploaded and processing started.');
    }

    public function status()
    {
        return UploadedFileResource::collection(UploadedFile::latest()->get());
    }
}
