<x-layouts.admin>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('super-admin.user-permissions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to User Permissions
            </a>
        </div>
        
        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                <!-- User Info Section -->
                <div class="md:col-span-1 bg-gray-50 p-4 rounded">
                    <h3 class="font-semibold text-lg mb-4">User Information</h3>
                    <p class="text-sm mb-2"><span class="font-medium">Name:</span> {{ $user->name }}</p>
                    <p class="text-sm mb-2"><span class="font-medium">Email:</span> {{ $user->email }}</p>
                    
                    <div class="mt-6">
                        <h4 class="font-medium text-sm mb-2">Departments:</h4>
                        <ul class="space-y-1">
                            @forelse ($user->departments as $department)
                                <li class="text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $department->name }}</li>
                            @empty
                                <li class="text-sm text-gray-500">No departments assigned</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                
                <!-- Permissions Management Section -->
                <div class="md:col-span-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg">Manage Direct Permissions for {{ $user->name }}</h3>
                    </div>
                    
                    <!-- Information about permissions -->
                    <div class="bg-yellow-50 p-4 mb-4 rounded border border-yellow-200">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> Direct permissions override department permissions. Any permission checked here will be granted to the user regardless of their department assignments.
                        </p>
                    </div>
                    
                    <form action="{{ route('super-admin.user-permissions.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-6">
                            @foreach ($permissionsByModule as $module => $modulePermissions)
                                <div class="border rounded overflow-hidden">
                                    <div class="px-4 py-2 bg-gray-100 font-medium text-sm">
                                        {{ ucfirst($module) }}
                                    </div>
                                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach ($modulePermissions as $permission)
                                            <div class="relative">
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input 
                                                            id="permission_{{ $permission->id }}" 
                                                            name="permissions[]" 
                                                            type="checkbox" 
                                                            value="{{ $permission->id }}" 
                                                            {{ in_array($permission->id, $directPermissions) ? 'checked' : '' }}
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        >
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="permission_{{ $permission->id }}" class="font-medium text-gray-700">{{ $permission->name }}</label>
                                                        <p class="text-gray-500">{{ $permission->description }}</p>
                                                        
                                                        @if(in_array($permission->id, $departmentPermissions))
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-blue-800" fill="currentColor" viewBox="0 0 8 8">
                                                                    <circle cx="4" cy="4" r="3" />
                                                                </svg>
                                                                From Department
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-800 disabled:opacity-25 transition">
                                Save Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin> 