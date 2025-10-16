<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Case Details
                </h2>
                    </div>
                </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'overview')" class="@if($activeTab === 'overview') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'documents')" class="@if($activeTab === 'documents') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Documents
                </button>
            </nav>
                </div>

        <!-- Tab Content -->
        <div class="mt-6">
            @if($activeTab === 'overview')
                <!-- Case information -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Case Information
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Details and status of the case.
                        </p>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Title
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $case->title }}
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Description
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $case->description }}
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Status
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($case->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($case->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($case->status === 'completed') bg-green-100 text-green-800
                                        @elseif($case->status === 'closed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Priority
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="flex items-center">
                                        <span class="mr-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($case->priority === 'urgent') bg-red-100 text-red-800
                                            @elseif($case->priority === 'high') bg-orange-100 text-orange-800
                                            @elseif($case->priority === 'medium') bg-blue-100 text-blue-800
                                            @elseif($case->priority === 'low') bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($case->priority) }}
                                        </span>
                                        <select wire:model.live="casePriority" class="block w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                                            <option value="low">Low Priority</option>
                                            <option value="medium">Medium Priority</option>
                                            <option value="high">High Priority</option>
                                            <option value="urgent">High Priority/Urgent</option>
                                        </select>
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($case->status === 'accepted')
                    <!-- Case Phase Manager -->
                    @livewire('lawyer.case-phase-manager', ['case' => $case])
                @else
                    <!-- Actions -->
                    <div class="mt-6">
                        <div class="flex space-x-3">
                            <x-button wire:click="acceptCase" wire:loading.attr="disabled">
                                {{ __('Accept Case') }}
                            </x-button>

                            <x-secondary-button wire:click="$set('showRejectModal', true)" wire:loading.attr="disabled">
                                {{ __('Reject Case') }}
                            </x-secondary-button>
                </div>
            </div>
                @endif
            @elseif($activeTab === 'documents')
                <!-- Documents Section -->
                @livewire('shared.case-documents', ['case' => $case])
            @endif
        </div>

        <!-- Reject Modal -->
        <x-dialog-modal wire:model.live="showRejectModal">
            <x-slot name="title">
                {{ __('Reject Case') }}
            </x-slot>

            <x-slot name="content">
                <div class="mt-4">
                    <x-label for="rejectionReason" value="{{ __('Reason for Rejection') }}" />
                    <x-input id="rejectionReason" type="text" class="mt-1 block w-full" wire:model.defer="rejectionReason" />
                    <x-input-error for="rejectionReason" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$set('showRejectModal', false)" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ml-3" wire:click="rejectCase" wire:loading.attr="disabled">
                    {{ __('Reject Case') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </div>
</div> 