<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Client Report Management</h1>
        <div class="text-sm text-gray-600">
            <span class="font-semibold">Total Reports:</span> {{ $stats['total'] }}
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">Total Reports</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">Pending</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">Under Review</h3>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['under_review'] }}</p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">Resolved</h3>
            <p class="text-2xl font-bold text-green-600">{{ $stats['resolved'] }}</p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">Dismissed</h3>
            <p class="text-2xl font-bold text-red-600">{{ $stats['dismissed'] }}</p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">This Month</h3>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['this_month'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" 
                       wire:model.debounce.300ms="search" 
                       placeholder="Search reports..." 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            
            <div>
                <select wire:model="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="under_review">Under Review</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>
            
            <div>
                <select wire:model="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">All Categories</option>
                    <option value="professional_misconduct">Professional Misconduct</option>
                    <option value="billing_disputes">Billing Disputes</option>
                    <option value="communication_issues">Communication Issues</option>
                    <option value="ethical_violations">Ethical Violations</option>
                    <option value="competency_concerns">Competency Concerns</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <select wire:model="reportedType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Lawyer & Law Firm</option>
                    <option value="lawyer">Lawyer Only</option>
                    <option value="law_firm">Law Firm Only</option>
                </select>
            </div>
            
            <div class="flex space-x-2">
                <button wire:click="$set('search', '')" class="px-3 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Report Details
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Reported Entity
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Category
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reports as $report)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ $report->reporter_name }}</div>
                                <div class="text-gray-500">{{ $report->reporter_email }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ Str::limit($report->description, 100) }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ $report->reported_name }}</div>
                                <div class="text-gray-500 capitalize">{{ str_replace('_', ' ', $report->reported_type) }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $report->category_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                @if($report->investigationCase && $report->investigationCase->status === 'completed')
                                    <!-- Show only investigation completed status -->
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        🔍 Investigation Completed
                                    </span>
                                @elseif($report->investigationCase)
                                    <!-- Show investigation in progress -->
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $report->investigationCase->status === 'assigned' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $report->investigationCase->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $report->investigationCase->status === 'pending_review' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $report->investigationCase->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        🔍 {{ $report->investigationCase->status_label }}
                                    </span>
                                    <div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $report->status === 'under_review' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $report->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $report->status === 'dismissed' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $report->status_label }}
                                        </span>
                                    </div>
                                @else
                                    <!-- Show regular report status -->
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $report->status === 'under_review' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $report->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $report->status === 'dismissed' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $report->status_label }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $report->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                            <button wire:click="viewReport({{ $report->id }})" 
                                    class="text-blue-600 hover:text-blue-900">
                                View
                            </button>
                            @if(!($report->investigationCase && $report->investigationCase->status === 'completed'))
                                <button wire:click="editReport({{ $report->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900">
                                    Edit
                                </button>
                            @endif
                            <button wire:click="investigateReport({{ $report->id }})" 
                                    class="text-purple-600 hover:text-purple-900 font-medium">
                                🔍 Investigate
                            </button>
                            @if ($report->status === 'pending' && !($report->investigationCase && $report->investigationCase->status === 'completed'))
                                <button wire:click="markAsUnderReview({{ $report->id }})" 
                                        class="text-orange-600 hover:text-orange-900">
                                    Review
                                </button>
                            @endif
                            @if (in_array($report->status, ['pending', 'under_review']) && !($report->investigationCase && $report->investigationCase->status === 'completed'))
                                <button wire:click="resolveReport({{ $report->id }})" 
                                        class="text-green-600 hover:text-green-900">
                                    Resolve
                                </button>
                                <button wire:click="dismissReport({{ $report->id }})" 
                                        class="text-red-600 hover:text-red-900">
                                    Dismiss
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No reports found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reports->links() }}
        </div>
    </div>

    <!-- View Report Modal -->
    @if($showViewModal && $selectedReport)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeViewModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Report Details - #{{ $selectedReport->id }}
                            </h3>
                            <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Reporter Information -->
                            <div class="space-y-4">
                                <h4 class="font-semibold text-gray-900">Reporter Information</h4>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                                    <p><span class="font-medium">Name:</span> {{ $selectedReport->reporter_name }}</p>
                                    <p><span class="font-medium">Email:</span> {{ $selectedReport->reporter_email }}</p>
                                    @if($selectedReport->reporter_phone)
                                        <p><span class="font-medium">Phone:</span> {{ $selectedReport->reporter_phone }}</p>
                                    @endif
                                    @if($selectedReport->service_date)
                                        <p><span class="font-medium">Service Date:</span> {{ $selectedReport->service_date->format('M d, Y') }}</p>
                                    @endif
                                    @if($selectedReport->legal_matter_type)
                                        <p><span class="font-medium">Legal Matter:</span> {{ $selectedReport->legal_matter_type }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Reported Entity Information -->
                            <div class="space-y-4">
                                <h4 class="font-semibold text-gray-900">Reported Entity</h4>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                                    <p><span class="font-medium">Name:</span> {{ $selectedReport->reported_name }}</p>
                                    <p><span class="font-medium">Type:</span> {{ ucfirst(str_replace('_', ' ', $selectedReport->reported_type)) }}</p>
                                    <p><span class="font-medium">Category:</span> {{ $selectedReport->category_label }}</p>
                                    @if($selectedReport->investigationCase && $selectedReport->investigationCase->status === 'completed')
                                        <p><span class="font-medium">Status:</span> 
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                🔍 Investigation Completed
                                            </span>
                                        </p>
                                    @elseif($selectedReport->investigationCase)
                                        <p><span class="font-medium">Investigation Status:</span> 
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $selectedReport->investigationCase->status === 'assigned' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $selectedReport->investigationCase->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $selectedReport->investigationCase->status === 'pending_review' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $selectedReport->investigationCase->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                🔍 {{ $selectedReport->investigationCase->status_label }}
                                            </span>
                                        </p>
                                        <p><span class="font-medium">Report Status:</span> 
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $selectedReport->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $selectedReport->status === 'under_review' ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $selectedReport->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $selectedReport->status === 'dismissed' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ $selectedReport->status_label }}
                                            </span>
                                        </p>
                                    @else
                                        <p><span class="font-medium">Status:</span> 
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $selectedReport->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $selectedReport->status === 'under_review' ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $selectedReport->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $selectedReport->status === 'dismissed' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ $selectedReport->status_label }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mt-6">
                            <h4 class="font-semibold text-gray-900 mb-2">Description</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700">{{ $selectedReport->description }}</p>
                            </div>
                        </div>
                        
                        <!-- Timeline -->
                        @if($selectedReport->timeline_of_events)
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-900 mb-2">Timeline of Events</h4>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-gray-700">{{ $selectedReport->timeline_of_events }}</p>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Supporting Documents -->
                        @if($selectedReport->supporting_documents)
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-900 mb-2">Supporting Documents</h4>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                                    @foreach($selectedReport->supporting_documents as $document)
                                        <div class="flex items-center justify-between p-2 bg-white rounded border">
                                            <span class="text-sm text-gray-700">{{ $document['original_name'] }}</span>
                                            <button wire:click="downloadDocument('{{ $document['path'] }}')" 
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                Download
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- Admin Notes -->
                        @if($selectedReport->admin_notes)
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-900 mb-2">Admin Notes</h4>
                                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                    <p class="text-gray-700">{{ $selectedReport->admin_notes }}</p>
                                    @if($selectedReport->reviewer)
                                        <p class="text-sm text-gray-500 mt-2">
                                            Reviewed by {{ $selectedReport->reviewer->name }} on {{ $selectedReport->reviewed_at->format('M d, Y g:i A') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="closeViewModal" type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Report Modal -->
    @if($showEditModal && $selectedReport)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeEditModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="updateReport">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Edit Report #{{ $selectedReport->id }}
                                </h3>
                                <button type="button" wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="newStatus" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select wire:model="newStatus" id="newStatus" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="pending">Pending Review</option>
                                        <option value="under_review">Under Review</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="dismissed">Dismissed</option>
                                    </select>
                                    @error('newStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="adminNotes" class="block text-sm font-medium text-gray-700">Admin Notes</label>
                                    <textarea wire:model="adminNotes" id="adminNotes" rows="4" 
                                              placeholder="Add administrative notes about this report..."
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    @error('adminNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update Report
                            </button>
                            <button type="button" wire:click="closeEditModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
