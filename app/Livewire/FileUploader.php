<?php

namespace App\Livewire;

use App\Jobs\ProcessFileContent;
use App\Models\FileData;
use App\Models\Files;
use Exception;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploader extends Component {
    use WithFileUploads;

    public $file_upload;
    public $all_files;

    protected $rules = [
        'file_upload' => 'required|file|mimes:csv|max:51200'
    ];

    protected $messages = [
        'file_upload.required' => 'No file has been uploaded',
        'file_upload.mimes' => 'Only CSV file are accepted',
        'file_upload.max' => 'File must not exceeding 50 MB',
    ];

    public function updatedFileUpload() {
        $this->resetErrorBag('file_upload');
    }

    public function mount() {
        $this->uploadList();
    }

    public function save() {
        $this->validate();

        try {
            $original_filename = $this->file_upload->getClientOriginalName();
            $unique_filename = uniqid($original_filename);
            $file_path = $this->file_upload->store('uploads', 'public');

            $file = Files::create([
                'filename' => $unique_filename,
                'original_file_name' => $original_filename,
                'stored_file_path' => $file_path,
                'status' => 'new'
            ]);

            ProcessFileContent::dispatch($file);

            $this->file_upload = null;
            $this->uploadList();
            session()->flash('message', [
                'color' => 'text-blue-500',
                'title' => 'File uploaded and stored successfully'
            ]);
        } catch (Exception $e) {
            if ($file_path) {
                Storage::delete($file_path);
            }

            session()->flash('message', [
                'color' => 'text-yellow-500',
                'title' => 'Failed to upload. Message: ' .$e->getMessage()
            ]);
        }
    }

    public function uploadList() {
        $this->all_files = Files::latest('created_at')->get();
    }

    public function deleteFile($upload_id) {
        $file = Files::find($upload_id);
        if ($file) {
            try {
                FileData::where('file_id', $file->id)->delete();
                Storage::disk('public')->delete($file->stored_file_path);
                $file->delete();
                $this->uploadList();
                session()->flash('message', [
                    'color' => 'text-green-500',
                    'title' => 'File deleted successfully'
                ]);
            }catch(Exception $e) {
                session()->flash('message', [
                    'color' => 'text-red-500',
                    'title' => 'Failed to delete file. Message: ' . $e->getMessage()
                ]);
            }
        } else {
            session()->flash('message', [
                'color' => 'text-red-500',
                'title' => 'File not found'
            ]);
        }

    }

    public function render() {
        return view('livewire.file-uploader');
    }
}
