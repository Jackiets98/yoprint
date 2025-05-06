<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    protected $fillable = ['filename', 'hash', 'path', 'status', 'uploaded_at'];
}

