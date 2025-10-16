<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">User Status Management</h1>
        <p class="text-gray-600">Manage user account status and view user information</p>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="w-full md:w-1/3">
                    <input wire:model.live="search" type="text" placeholder="Search users..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="w-full md:w-1/3">
                    <select wire:model.live="roleFilter" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Roles</option>
                        <option value="lawyer">Lawyer</option>
                        <option value="client">Client</option>
                        <option value="law_firm">Law Firm</option>
                    </select>
                </div>
            </div>

                @if (session()->has('message'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->role->name === 'client' && $user->clientProfile)
                                            {{ $user->clientProfile->first_name }} {{ $user->clientProfile->last_name }}
                                        @elseif($user->role->name === 'lawyer' && $user->lawyerProfile)
                                            {{ $user->lawyerProfile->first_name }} {{ $user->lawyerProfile->last_name }}
                                        @elseif($user->role->name === 'law_firm' && $user->lawFirmProfile)
                                            {{ $user->lawFirmProfile->firm_name }}
                                        @else
                                            {{ $user->name ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap capitalize">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($user->role->name === 'client') bg-blue-100 text-blue-800
                                            @elseif($user->role->name === 'lawyer') bg-purple-100 text-purple-800
                                            @elseif($user->role->name === 'law_firm') bg-indigo-100 text-indigo-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ str_replace('_', ' ', $user->role->name) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($user->status === 'approved') bg-green-100 text-green-800
                                            @elseif($user->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($user->status === 'deactivated') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($user->status === 'approved')
                                            <button wire:click="showDeactivateModal({{ $user->id }})" class="text-red-600 hover:text-red-900 font-medium">Deactivate</button>
                                        @elseif($user->status === 'deactivated')
                                            <button wire:click="showReactivateModal({{ $user->id }})" class="text-green-600 hover:text-green-900 font-medium">Reactivate</button>
                                        @else
                                            <span class="text-gray-400">No action available</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Deactivate User Modal -->
    @if($showDeactivateModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Deactivate User Account</h3>
                            <div class="mt-2">
                                <div class="mb-4">
                                    <label for="deactivationReason" class="block text-sm font-medium text-gray-700">Reason for Deactivation</label>
                                    <textarea
                                        wire:model="deactivationReason"
                                        id="deactivationReason"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Please provide a reason for deactivation..."
                                    ></textarea>
                                    @error('deactivationReason')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button wire:click="deactivateUser" type="button" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Deactivate</button>
                            <button wire:click="closeDeactivateModal" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reactivate User Modal -->
    @if($showReactivateModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Reactivate User Account</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to reactivate this user's account?</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button wire:click="reactivateUser" type="button" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">Reactivate</button>
                            <button wire:click="closeReactivateModal" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 