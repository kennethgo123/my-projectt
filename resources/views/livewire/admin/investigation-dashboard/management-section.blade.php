<!-- Investigation Management Panel -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 font-raleway">Investigation Management</h3>
        
        @if($investigation->isLocked())
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">Investigation Locked</p>
                        <p class="text-sm">This investigation has been completed and is now locked. No further changes can be made.</p>
                    </div>
                </div>
            </div>
        @endif
        
        @if($investigation->status === 'assigned')
            <button wire:click="startInvestigation" 
                    class="w-full mb-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-open-sans">
                Start Investigation
            </button>
        @endif

        <form wire:submit.prevent="updateInvestigation" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Status</label>
                <select wire:model="newStatus" class="block w-full border-gray-300 rounded-md text-sm font-open-sans"
                        @if($investigation->isLocked()) disabled @endif>
                    <option value="assigned">Assigned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="pending_review">Pending Review</option>
                    <option value="completed">Completed</option>
                    <option value="closed">Closed</option>
                </select>
                @error('newStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Priority</label>
                <select wire:model="newPriority" class="block w-full border-gray-300 rounded-md text-sm font-open-sans"
                        @if($investigation->isLocked()) disabled @endif>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                @error('newPriority') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Investigation Notes</label>
                <textarea wire:model="investigationNotes" rows="4" 
                          class="block w-full border-gray-300 rounded-md text-sm font-open-sans"
                          placeholder="Record your investigation notes here..."
                          @if($investigation->isLocked()) readonly @endif></textarea>
                @error('investigationNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Findings</label>
                <textarea wire:model="findings" rows="3" 
                          class="block w-full border-gray-300 rounded-md text-sm font-open-sans"
                          placeholder="Document your findings..."
                          @if($investigation->isLocked()) readonly @endif></textarea>
                @error('findings') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Recommendations</label>
                <textarea wire:model="recommendations" rows="3" 
                          class="block w-full border-gray-300 rounded-md text-sm font-open-sans"
                          placeholder="Your recommendations..."
                          @if($investigation->isLocked()) readonly @endif></textarea>
                @error('recommendations') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 font-open-sans"
                    @if($investigation->isLocked()) disabled @endif>
                Update Investigation
            </button>
        </form>
    </div>
</div>

<!-- Investigation Timeline -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 font-raleway">Investigation Timeline</h3>
        <div class="space-y-3 text-sm font-open-sans">
            @if($investigation->assigned_at)
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span class="text-gray-600">Assigned: {{ $investigation->assigned_at->format('M j, Y g:i A') }}</span>
                </div>
            @endif
            @if($investigation->started_at)
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                    <span class="text-gray-600">Started: {{ $investigation->started_at->format('M j, Y g:i A') }}</span>
                </div>
            @endif
            @if($investigation->completed_at)
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-gray-600">Completed: {{ $investigation->completed_at->format('M j, Y g:i A') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Investigation Attachments -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 font-raleway">Investigation Attachments</h3>
        
        @if(!$investigation->isLocked())
            <!-- File Upload Form -->
            <form wire:submit.prevent="uploadAttachments" class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Select Files</label>
                    <input type="file" wire:model="attachments" multiple 
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Maximum 10MB per file. Multiple files allowed.</p>
                    @error('attachments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Attachment Type</label>
                    <select wire:model="attachmentType" class="block w-full border-gray-300 rounded-md text-sm font-open-sans">
                        <option value="document">Document</option>
                        <option value="evidence">Evidence</option>
                        <option value="image">Image</option>
                        <option value="other">Other</option>
                    </select>
                    @error('attachmentType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 font-raleway">Description (Optional)</label>
                    <textarea wire:model="attachmentDescription" rows="2" 
                              class="block w-full border-gray-300 rounded-md text-sm font-open-sans"
                              placeholder="Describe the attachment..."></textarea>
                    @error('attachmentDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-open-sans">
                    Upload Attachments
                </button>
            </form>
        @endif

        <!-- Existing Attachments -->
        @if($investigationAttachments->isNotEmpty())
            <div class="space-y-3">
                <h4 class="text-sm font-medium text-gray-800 font-raleway">Uploaded Files</h4>
                @foreach($investigationAttachments as $attachment)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $attachment->attachment_type_color }}">
                                    {{ $attachment->attachment_type_label }}
                                </span>
                                <span class="text-sm font-medium text-gray-900 font-open-sans">{{ $attachment->original_filename }}</span>
                            </div>
                            <p class="text-xs text-gray-500 font-open-sans mt-1">
                                {{ $attachment->formatted_file_size }} • 
                                Uploaded by {{ $attachment->uploader->first_name ?? $attachment->uploader->name }} • 
                                {{ $attachment->created_at->format('M j, Y g:i A') }}
                            </p>
                            @if($attachment->description)
                                <p class="text-xs text-gray-600 font-open-sans mt-1">{{ $attachment->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ $attachment->download_url }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Download
                            </a>
                            @if(!$investigation->isLocked())
                                <button wire:click="deleteAttachment({{ $attachment->id }})" 
                                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                                        onclick="return confirm('Are you sure you want to delete this attachment?')">
                                    Delete
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 font-open-sans">No attachments uploaded yet.</p>
        @endif
    </div>
</div>

<!-- Completion Confirmation Modal -->
@if($showConfirmComplete)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="cancelComplete">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 19c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 font-raleway mt-4">Complete Investigation</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 font-open-sans">
                        <strong>Warning:</strong> Marking this investigation as completed is irreversible. 
                        Once completed, you will not be able to modify the investigation details, 
                        upload new attachments, or make any changes.
                    </p>
                    <p class="text-sm text-gray-500 font-open-sans mt-2">
                        Are you sure you want to complete this investigation?
                    </p>
                </div>
                <div class="flex items-center px-4 py-3 space-x-3">
                    <button wire:click="cancelComplete" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none font-open-sans">
                        Cancel
                    </button>
                    <button wire:click="confirmComplete" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none font-open-sans">
                        Yes, Complete Investigation
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
