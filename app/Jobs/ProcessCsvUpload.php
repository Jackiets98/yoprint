<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadedFile;
use App\Models\Product;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uploadId, $path;

    public function __construct($uploadId, $path)
    {
        $this->uploadId = $uploadId;
        $this->path = $path;
    }

    public function handle()
    {
        $upload = UploadedFile::find($this->uploadId);
        $upload->update(['status' => 'Processing']);

        try {
            $file = Storage::disk('public')->get($this->path);

            // Stronger BOM removal
            $bom = pack('H*','EFBBBF');
            $file = preg_replace("/^$bom/", '', $file);

            $file = @iconv(mb_detect_encoding($file, mb_detect_order(), true), 'UTF-8//IGNORE', $file);
            
            // Split into rows and parse CSV
            $rows = array_map('str_getcsv', explode("\n", $file));
            $header = array_map('trim', array_shift($rows));
            
            // Debug logs
            Log::debug('Parsed CSV file content:', ['file' => substr($file, 0, 200)]); // log preview only
            Log::debug('Upload ID', ['upload' => $upload]);
            

            $required = ['UNIQUE_KEY', 'PRODUCT_TITLE', 'PRODUCT_DESCRIPTION', 'STYLE#', 'SANMAR_MAINFRAME_COLOR', 'SIZE', 'COLOR_NAME', 'PIECE_PRICE'];

            foreach ($required as $column) {
                if (!in_array($column, $header)) {
                    throw new \Exception("Missing required column: $column");
                }
            }

            foreach ($rows as $row) {
                if (count($row) < count($header)) continue;
                $data = array_combine($header, array_map('trim', $row));

                if (!isset($data['UNIQUE_KEY'])) continue;

                $data = array_intersect_key($data, array_flip($required));

                Product::updateOrCreate(
                    ['unique_key' => $data['UNIQUE_KEY']],
                    [
                        'product_title' => $data['PRODUCT_TITLE'] ?? null,
                        'product_description' => $data['PRODUCT_DESCRIPTION'] ?? null,
                        'style' => $data['STYLE#'] ?? null,
                        'sanmar_mainframe_color' => $data['SANMAR_MAINFRAME_COLOR'] ?? null,
                        'size' => $data['SIZE'] ?? null,
                        'color_name' => $data['COLOR_NAME'] ?? null,
                        'piece_price' => $data['PIECE_PRICE'] ?? null,
                    ]
                );
            }

            $upload->update(['status' => 'Completed']);

        } catch (\Exception $e) {
            Log::error('CSV Processing Error: ' . $e->getMessage());
            $upload->update(['status' => 'Failed']);
        }
    }
}