<div class="w-full max-w-screen-md mx-auto p-4 space-y-10">
    <div
        x-data="{ uploading: false }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-error="uploading = false">
        <form wire:submit="save" class="flex flex-col space-y-4">
            <div class="flex flex-col space-y-1">
                <input type="file" wire:model="file_upload" class="w-full h-20 bg-neutral-100 rounded-xl border-2 border-dashed text-center">
                @error('file_upload') <p class="text-red-700 font-medium bg-red-200 rounded-md px-2 py-1 text-center">{{ $message }}</p> @enderror
                <p x-cloak x-show="uploading" class="bg-blue-200 text-blue-800 font-semibold rounded-md text-center py-1">Uploading...</p>
            </div>

            <button x-bind:disabled="uploading" type="submit" class="border cursor-pointer font-semibold py-2 px-4 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white transition disabled:bg-neutral-200 disabled:text-neutral-400">Save</button>

        </form>

        @if (session()->has('message'))
            @php
                $message = session('message');
            @endphp
            <div class="{{ $message['color'] }}">
                {{ $message['title'] }}
            </div>
        @endif
    </div>

    <div wire:poll.3s="uploadList">
        @if ($all_files->isEmpty())
            <div>
                <p>No data</p>
            </div>
        @else
            <table class="w-full text-sm text-left border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 border">Time uploaded</th>
                        <th class="px-3 py-2 border">Name</th>
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($all_files as $all_file)
                        <tr>
                            @php
                                $carbon = \Carbon\Carbon::parse($all_file->created_at);
                            @endphp
                            <td class="px-3 py-1 border">
                                <p>{{ $carbon->toDayDateTimeString() }} ({{ $carbon->diffForHumans() }})</p>
                            </td>
                            <td class="px-3 py-1 border">{{ $all_file->original_file_name }}</td>
                            <td class="px-3 py-1 border">
                                {{ $all_file->status }} ({{ round($all_file->progress) }}%)
                            </td>
                            <td class="px-3 py-1 border">
                                <button type="button" wire:click="deleteFile({{ $all_file->id }})" class="border cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
