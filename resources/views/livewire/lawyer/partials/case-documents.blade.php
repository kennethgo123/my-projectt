<div>
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900">Case Documents</h3>
        @if(!$isReadOnly ?? false)
            <button 
                x-data="{}"
                x-on:click="$dispatch('open-modal', 'add-document-modal')"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Upload Document
            </button>
        @endif
    </div>

    @if (count($documents) === 0)
        <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(!$isReadOnly ?? false)
                    Get started by uploading a document to this case.
                @else
                    This case has no documents.
                @endif
            </p>
        </div>
    @else
        <!-- Documents List -->
        <div class="overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Size
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Uploaded By
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($documents as $document)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $document->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($document->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($document->file_type)
                                    {{ Str::upper(Str::afterLast($document->file_name, '.')) }}
                                @else
                                    Unknown
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($document->file_size)
                                    {{ number_format($document->file_size / 1024, 2) }} KB
                                @else
                                    Unknown
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @php
                                    $uploader = null;
                                    if ($document->uploaded_by_type === 'App\\Models\\User') {
                                        $uploader = App\Models\User::with(['lawFirmLawyer', 'lawyerProfile', 'lawFirmProfile'])->find($document->uploaded_by_id);
                                    }
                                    
                                    $uploaderName = 'Unknown';
                                    if ($uploader) {
                                        if ($uploader->lawFirmLawyer) {
                                            $uploaderName = $uploader->lawFirmLawyer->first_name . ' ' . $uploader->lawFirmLawyer->last_name;
                                        } elseif ($uploader->lawyerProfile) {
                                            $uploaderName = $uploader->lawyerProfile->first_name . ' ' . $uploader->lawyerProfile->last_name;
                                        } elseif ($uploader->lawFirmProfile) {
                                            $uploaderName = $uploader->lawFirmProfile->firm_name;
                                        } else {
                                            $uploaderName = $uploader->name;
                                        }
                                    }
                                @endphp
                                {{ $uploaderName }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($document->created_at)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                    View
                                </a>
                                <a href="{{ Storage::url($document->file_path) }}" download class="text-green-600 hover:text-green-900">
                                    Download
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Add Document Modal -->
    <div
        x-data="{ shown: false }"
        x-on:open-modal.window="$event.detail == 'add-document-modal' ? shown = true : null"
        x-on:close-modal.window="$event.detail == 'add-document-modal' ? shown = false : null"
        x-show="shown"
        x-cloak
        class="fixed z-10 inset-0 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div
                x-show="shown"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                x-show="shown"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Upload New Document
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Please provide the details for the new document.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <form wire:submit.prevent="uploadDocument">
                        <div class="space-y-4">
                            <div>
                                <label for="newDocumentTitle" class="block text-sm font-medium text-gray-700">
                                    Document Title
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="text" 
                                        id="newDocumentTitle" 
                                        wire:model="newDocumentTitle" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                                @error('newDocumentTitle') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="newDocumentDescription" class="block text-sm font-medium text-gray-700">
                                    Description (optional)
                                </label>
                                <div class="mt-1">
                                    <textarea 
                                        id="newDocumentDescription" 
                                        wire:model="newDocumentDescription" 
                                        rows="3" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    ></textarea>
                                </div>
                                @error('newDocumentDescription') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="newDocument" class="block text-sm font-medium text-gray-700">
                                    Document File
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="file" 
                                        id="newDocument" 
                                        wire:model="newDocument" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300"
                                    >
                                </div>
                                <div wire:loading wire:target="newDocument" class="mt-1 text-sm text-gray-500">
                                    Uploading...
                                </div>
                                @error('newDocument') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button 
                                type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                            >
                                Upload Document
                            </button>
                            <button 
                                type="button" 
                                x-on:click="$dispatch('close-modal', 'add-document-modal')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 