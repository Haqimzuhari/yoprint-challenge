<?php

namespace App\Jobs;

use App\Models\FileData;
use App\Models\Files;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessFileContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    protected $file;

    /**
     * Create a new job instance.
     */
    public function __construct(Files $file) {
        $this->file = $file;
    }

    protected function get_total_rows($file_path) {
        $handle = fopen($file_path, 'r');
        $totalRows = 0;

        while (fgetcsv($handle) !== false) {
            $totalRows++;
        }

        fclose($handle);
        return $totalRows - 1;
    }

    protected function update_progress($total_rows, $inserted_rows) {
        $progress = $total_rows > 0 ? round(($inserted_rows / $total_rows) * 100, 2) : 0;
        $this->file->update(['progress' => $progress]);
    }

    protected function clear_data($data) {
        array_walk($data, function (&$value) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            $value = preg_replace('/[^\P{C}\n]+/u', '', $value);
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        });

        return $data;
    }

    /**
     * Execute the job.
     */
    public function handle() {
        set_time_limit(0);
        $this->file->update(['status' => 'processing']);
        try {
            // Open the file for reading
            $file_full_path = storage_path("app/public/{$this->file->stored_file_path}");
            $handle = fopen($file_full_path, 'r');

            if ($handle === false) {
                throw new Exception("Unable to open file: {$file_full_path}");
            }

            // Read the headers
            $headers = fgetcsv($handle);
            $headers = $this->clear_data(array_map('trim', $headers));

            $selected_headers = ['UNIQUE_KEY', 'PRODUCT_TITLE', 'PRODUCT_DESCRIPTION', 'STYLE#', 'SIZE', 'COLOR_NAME', 'PIECE_PRICE', 'SANMAR_MAINFRAME_COLOR'];

            $batch = [];
            $batch_size = 100;
            $total_rows = $this->get_total_rows($file_full_path);
            $inserted_rows = 0;

            // Process each row
            while (($row = fgetcsv($handle)) !== false) {
                // Combine headers with row data
                $data = array_combine($headers, $row);

                // Filter the data to keep only selected columns
                $filtered = $this->clear_data(array_intersect_key($data, array_flip($selected_headers)));

                // Store the cleaned and filtered data in the database
                if (!empty($filtered['UNIQUE_KEY'])) {
                    $batch[] = [
                        'file_id' => $this->file->id,
                        'unique_key' => $filtered['UNIQUE_KEY'],
                        'product_title' => $filtered['PRODUCT_TITLE'] ?? null,
                        'product_description' => $filtered['PRODUCT_DESCRIPTION'] ?? null,
                        'style_number' => $filtered['STYLE#'] ?? null,
                        'sanmar_mainframe_color' => $filtered['SANMAR_MAINFRAME_COLOR'] ?? null,
                        'size' => $filtered['SIZE'] ?? null,
                        'color_name' => $filtered['COLOR_NAME'] ?? null,
                        'piece_price' => $filtered['PIECE_PRICE'] ?? null,
                        'updated_at' => now(),
                    ];
                }

                $batch_count = count($batch);
                if ($batch_count >= $batch_size) {
                    FileData::upsert($batch,
                    ['unique_key'],
                    ['file_id', 'product_title',  'product_description', 'style_number', 'sanmar_mainframe_color', 'size', 'color_name', 'piece_price', 'updated_at']);
                    $inserted_rows += $batch_count;
                    $batch = [];
                    $this->update_progress($total_rows, $inserted_rows);
                }
            }

            if (!empty($batch)) {
                FileData::upsert($batch,
                ['unique_key'],
                ['file_id', 'product_title', 'product_description', 'style_number', 'sanmar_mainframe_color', 'size', 'color_name', 'piece_price', 'updated_at']);
                $inserted_rows += count($batch);
            }
            $this->update_progress($total_rows, $inserted_rows);

            // Close the file
            fclose($handle);

            $this->file->update(['status' => 'completed']);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $e): void {
        // Update the file status to 'failed'
        $this->file->update(['status' => 'failed. message: ' .$e->getMessage()]);
        session()->flash('message', [
            'color' => 'text-red-500',
            'title' => 'Failed to upload. Message: ' .$e->getMessage()
        ]);
    }
}
