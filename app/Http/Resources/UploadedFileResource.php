<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UploadedFileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'path' => $this->path,
            'status' => $this->status,
            'uploaded_at' => \Carbon\Carbon::parse($this->uploaded_at)->toDateTimeString(),
        ];
    }
}