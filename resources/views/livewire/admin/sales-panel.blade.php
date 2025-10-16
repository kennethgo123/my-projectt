<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Sales Panel</h2>
        <div class="flex space-x-2">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Sales Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Invoiced -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <h3 class="text-lg font-medium text-gray-800">Total Invoiced</h3>
            <p class="text-3xl font-bold text-gray-900">₱{{ number_format($salesStats['total_invoiced'], 2) }}</p>
            <p class="text-sm text-gray-600 mt-2">All time contract value</p>
        </div>
        
        <!-- Total Paid -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <h3 class="text-lg font-medium text-gray-800">Total Paid</h3>
            <p class="text-3xl font-bold text-gray-900">₱{{ number_format($salesStats['total_paid'], 2) }}</p>
            <p class="text-sm text-gray-600 mt-2">Successfully completed payments</p>
        </div>
        
        <!-- Platform Commission -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <h3 class="text-lg font-medium text-gray-800">Platform Revenue</h3>
            <p class="text-3xl font-bold text-gray-900">₱{{ number_format($salesStats['platform_commission'], 2) }}</p>
            <p class="text-sm text-gray-600 mt-2">4% commission from paid invoices</p>
        </div>
        
        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <h3 class="text-lg font-medium text-gray-800">Outstanding</h3>
            <p class="text-3xl font-bold text-gray-900">₱{{ number_format($salesStats['total_pending'] + $salesStats['total_overdue'], 2) }}</p>
            <p class="text-sm text-gray-600 mt-2">Pending and overdue payments</p>
        </div>
    </div>
    
    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search Box -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Lawyer/Law Firm</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="search" 
                        wire:model.live="search"
                        class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="Search invoice #, lawyer, or law firm..."
                    >
                </div>
            </div>
            
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                <select 
                    id="status" 
                    wire:model.live="filterStatus"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Date Range Filter -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input 
                        type="date" 
                        id="startDate" 
                        wire:model.live="startDate"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input 
                        type="date" 
                        id="endDate" 
                        wire:model.live="endDate"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                </div>
            </div>
        </div>
    </div>
    
    <!-- Invoices Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                All Invoices
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Complete listing of all invoices across the platform
            </p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lawyer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission (4%)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $invoice->issue_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($invoice->client && $invoice->client->clientProfile)
                                    {{ $invoice->client->clientProfile->first_name }} {{ $invoice->client->clientProfile->last_name }}
                                @else
                                    {{ $invoice->client->name ?? 'N/A' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($invoice->lawyer && $invoice->lawyer->lawyerProfile)
                                    {{ $invoice->lawyer->lawyerProfile->first_name }} {{ $invoice->lawyer->lawyerProfile->last_name }}
                                @elseif($invoice->lawyer && $invoice->lawyer->lawFirmProfile)
                                    {{ $invoice->lawyer->lawFirmProfile->firm_name }}
                                @else
                                    {{ $invoice->lawyer->name ?? 'N/A' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱{{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                    ($invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 
                                    'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($invoice->status === 'paid')
                                    ₱{{ number_format($invoice->total * 0.04, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No invoices found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
            {{ $invoices->links() }}
        </div>
    </div>
</div> 