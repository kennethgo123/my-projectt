<x-layouts.admin>
    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-6">Department Access Management</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Departments Card -->
                    <div class="bg-blue-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-2">Departments</h2>
                        <p class="text-gray-600 mb-4">Manage department assignments and permissions</p>
                        <p class="text-3xl font-bold text-blue-600 mb-4">{{ $departments }}</p>
                        <a href="{{ route('super-admin.departments.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage Departments
                        </a>
                    </div>
                    
                    <!-- Users Card -->
                    <div class="bg-green-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-2">Department Users</h2>
                        <p class="text-gray-600 mb-4">Users assigned to specific departments</p>
                        <p class="text-3xl font-bold text-green-600 mb-4">{{ $usersInDepartments }}</p>
                        <a href="{{ route('super-admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage Users
                        </a>
                    </div>
                    
                    <!-- Permissions Card -->
                    <div class="bg-purple-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-2">Permissions</h2>
                        <p class="text-gray-600 mb-4">Define access controls for departments</p>
                        <p class="text-3xl font-bold text-purple-600 mb-4">{{ $permissions }}</p>
                        <a href="{{ route('super-admin.permissions.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage Permissions
                        </a>
                    </div>
                    
                    <!-- Direct User Permissions Card -->
                    <div class="bg-amber-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-2">User Permissions</h2>
                        <p class="text-gray-600 mb-4">Assign permissions directly to individual users</p>
                        <p class="text-3xl font-bold text-amber-600 mb-4">
                            <svg class="w-10 h-10 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </p>
                        <a href="{{ route('super-admin.user-permissions.index') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 focus:bg-amber-700 active:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage User Permissions
                        </a>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-4">Department Overview</h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <ul class="space-y-4">
                            <li class="p-4 bg-white rounded shadow">
                                <h3 class="font-semibold text-lg text-blue-700">User Management Department</h3>
                                <p class="text-gray-600">Responsible for user approvals, monitoring, and user management</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">View User List</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Approve Users</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Deactivate Users</span>
                                </div>
                            </li>
                            <li class="p-4 bg-white rounded shadow">
                                <h3 class="font-semibold text-lg text-green-700">Financial Department</h3>
                                <p class="text-gray-600">Manages financial operations, sales tracking, and subscriptions</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">View Sales Panel</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Manage Subscriptions</span>
                                </div>
                            </li>
                            <li class="p-4 bg-white rounded shadow">
                                <h3 class="font-semibold text-lg text-red-700">Client Support Services</h3>
                                <p class="text-gray-600">Handles client reports filed against lawyers and law firms, provides support and ensures quality service standards</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">View Client Reports</span>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Review Client Reports</span>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Resolve Client Reports</span>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Add Report Notes</span>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">View Report Documents</span>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Contact Report Parties</span>
                                </div>
                            </li>
                            <li class="p-4 bg-white rounded shadow">
                                <h3 class="font-semibold text-lg text-purple-700">Law Services Department</h3>
                                <p class="text-gray-600">Manages the legal service offerings of the platform</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">Manage Law Services</span>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">Delete Law Services</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin> 