<div>
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900">Case Documents</h3>
        <a href="{{ route('client.documents.upload', isset($caseId) ? $caseId : $case->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Upload Document
        </a>
    </div>

    @if (count($documents) === 0)
        <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
            <p class="mt-1 text-sm text-gray-500">No documents have been uploaded for this case yet.</p>
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
                            Uploaded
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
                                <div class="text-sm font-medium text-gray-900">{{ $document['title'] ?? 'Untitled' }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($document['description'], 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if(isset($document['file_name']))
                                    {{ Str::upper(Str::afterLast($document['file_name'], '.')) }}
                                @else
                                    Unknown
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if(isset($document['file_size']))
                                    {{ number_format($document['file_size'] / 1024, 1) }} KB
                                @else
                                    Unknown
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    // Determine if document was uploaded by client
                                    $isClientUpload = false;
                                    if (isset($document['uploaded_by_id']) && isset($case)) {
                                        $isClientUpload = $document['uploaded_by_id'] == $case->client_id;
                                    } elseif (isset($document->uploaded_by_id) && isset($case)) {
                                        $isClientUpload = $document->uploaded_by_id == $case->client_id;
                                    }
                                @endphp

                                @if($isClientUpload)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Client
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Lawyer
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($document['created_at'])->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                {{-- Provide a download link --}}
                                <a href="{{ Storage::url($document['file_path']) }}" download="{{ $document['file_name'] ?? 'document' }}" class="text-indigo-600 hover:text-indigo-900">
                                    Download
                                </a>
                                {{-- Remove Delete for client --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Remove Add Document Modal for client view --}}
</div> 