<x-layouts.admin>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('super-admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
        
        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Assign Direct Permissions to Users</h3>
            <p class="mb-4 text-gray-600">
                This allows you to assign permissions directly to individual users, overriding their department-based permissions.
                Direct permissions take precedence over department permissions.
            </p>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Departments</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Direct Permissions</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    {{ $user->name }}
                                    @if ($user->is_super_admin)
                                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">Super Admin</span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">{{ $user->email }}</td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($user->departments as $department)
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $department->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($user->permissions as $permission)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">{{ $permission->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <a href="{{ route('super-admin.user-permissions.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                                        Manage Permissions
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">No admin users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin> 