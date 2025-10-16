<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">Documents</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Shared case documents and files</p>
        </div>
        <button wire:click="showUploadDocumentModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-700 transition">
            + Upload Document
        </button>
    </div>

    <div class="border-t border-gray-200">
        <div class="bg-white shadow overflow-hidden">
            @if($documents->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by uploading a new document.</p>
                </div>
            @else
                <ul role="list" class="divide-y divide-gray-200">
                    @foreach($documents as $document)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @switch($document->file_type)
                                            @case('pdf')
                                                <svg class="h-8 w-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                                </svg>
                                            @break
                                            @case('doc')
                                            @case('docx')
                                                <svg class="h-8 w-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/>
                                                </svg>
                                            @break
                                            @default
                                                <svg class="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/>
                                                </svg>
                                        @endswitch
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $document->title }}</h4>
                                        @if($document->description)
                                            <p class="mt-1 text-sm text-gray-500">{{ $document->description }}</p>
                                        @endif
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <p>Uploaded by {{ ucfirst($document->uploaded_by_type) }} • {{ $document->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="downloadDocument({{ $document->id }})" class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </button>
                                    @if($document->uploaded_by_id === Auth::id())
                                        <button wire:click="deleteDocument({{ $document->id }})" class="text-red-600 hover:text-red-900">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div x-data="{ open: @entangle('showUploadModal') }" x-show="open" class="fixed inset-0 overflow-y-auto z-10" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-headline">
                            Upload Document
                        </h3>
            <div class="space-y-4">
                <div>
                                <label for="documentTitle" class="block text-sm font-medium text-gray-700">Document Title</label>
                                <input id="documentTitle" type="text" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" wire:model="documentTitle">
                                @error('documentTitle')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                
                <div>
                                <label for="documentDescription" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <textarea id="documentDescription" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" wire:model="documentDescription"></textarea>
                                @error('documentDescription')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                
                <div>
                                <label for="document" class="block text-sm font-medium text-gray-700">Document</label>
                    <input type="file" id="document" wire:model="document" class="mt-1 block w-full" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <p class="mt-1 text-sm text-gray-500">Accepted file types: PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB)</p>
                                @error('document')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="uploadDocument" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Upload
                    </button>
                    <button wire:click="$set('showUploadModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 