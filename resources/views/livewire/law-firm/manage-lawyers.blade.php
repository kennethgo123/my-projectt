<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900">Manage Lawyers</h2>
                        <button wire:click="$toggle('showForm')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            {{ $showForm ? 'Cancel' : 'Add Lawyer' }}
                        </button>
                    </div>

                    @if(session()->has('message'))
                        <div class="mt-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if(session()->has('error'))
                        <div class="mt-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($showForm)
                        <form wire:submit="addLawyer" class="mt-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- First Name -->
                                <div>
                                    <x-label for="firstName" value="First Name" />
                                    <x-input id="firstName" type="text" wire:model="firstName" class="mt-1 block w-full" />
                                    @error('firstName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Middle Name -->
                                <div>
                                    <x-label for="middleName" value="Middle Name" />
                                    <x-input id="middleName" type="text" wire:model="middleName" class="mt-1 block w-full" />
                                    @error('middleName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <x-label for="lastName" value="Last Name" />
                                    <x-input id="lastName" type="text" wire:model="lastName" class="mt-1 block w-full" />
                                    @error('lastName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <x-label for="email" value="Email Address" />
                                    <x-input id="email" type="email" wire:model="email" class="mt-1 block w-full" />
                                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Password -->
                                <div>
                                    <x-label for="password" value="Password" />
                                    <x-input id="password" type="password" wire:model="password" class="mt-1 block w-full" />
                                    @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Password Confirmation -->
                                <div>
                                    <x-label for="password_confirmation" value="Confirm Password" />
                                    <x-input id="password_confirmation" type="password" wire:model="password_confirmation" class="mt-1 block w-full" />
                                    @error('password_confirmation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Contact Number -->
                                <div>
                                    <x-label for="contactNumber" value="Contact Number" />
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">
                                            +63
                                        </span>
                                        <x-input id="contactNumber" type="text" wire:model="contactNumber" class="mt-0 block w-full rounded-l-none" placeholder="9123456789" />
                                    </div>
                                    @error('contactNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Address -->
                                <div class="md:col-span-2">
                                    <x-label for="address" value="Address" />
                                    <x-input id="address" type="text" wire:model="address" class="mt-1 block w-full" />
                                    @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- City -->
                                <div>
                                    <x-label for="city" value="City" />
                                    <select id="city" wire:model="city" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select a city</option>
                                        @foreach($cities as $cityOption)
                                            <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Valid ID Type -->
                                <div>
                                    <x-label for="validIdType" value="Valid ID Type" />
                                    <select id="validIdType" wire:model="validIdType" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select ID type</option>
                                        @foreach($validIdTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('validIdType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Valid ID File -->
                                <div>
                                    <x-label for="validIdFile" value="Valid ID File" />
                                    <input type="file" wire:model="validIdFile" class="mt-1 block w-full" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                                    <div class="mt-1 text-sm text-gray-500">
                                        Accepted formats: JPG, PNG, PDF, DOC, DOCX (max 5MB)
                                    </div>
                                    @error('validIdFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Bar Admission Type -->
                                <div>
                                    <x-label for="barAdmissionType" value="Bar Admission Type" />
                                    <select id="barAdmissionType" wire:model="barAdmissionType" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select type</option>
                                        <option value="Bar Admission Id">Bar Admission ID</option>
                                        <option value="Bar Admission Certificate">Bar Admission Certificate</option>
                                    </select>
                                    @error('barAdmissionType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Bar Admission File -->
                                <div>
                                    <x-label for="barAdmissionFile" value="Bar Admission File" />
                                    <input type="file" wire:model="barAdmissionFile" class="mt-1 block w-full" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                                    <div class="mt-1 text-sm text-gray-500">
                                        Accepted formats: JPG, PNG, PDF, DOC, DOCX (max 5MB)
                                    </div>
                                    @error('barAdmissionFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Services Offered -->
                                <div class="md:col-span-2">
                                    <x-label for="selectedServices" value="Services Offered" />
                                    @if(count($availableServices) > 0)
                                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($availableServices as $service)
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input id="service-{{ $service->id }}" type="checkbox" 
                                                            wire:model="selectedServices" value="{{ $service->id }}"
                                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="service-{{ $service->id }}" class="font-medium text-gray-700">{{ $service->name }}</label>
                                                        @if($service->description)
                                                            <p class="text-gray-500">{{ $service->description }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-1 p-4 bg-yellow-50 border border-yellow-300 rounded-md text-yellow-700">
                                            <p>Your law firm doesn't have any services defined yet. Please add services to your firm profile first.</p>
                                        </div>
                                    @endif
                                    @error('selectedServices') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end mt-6">
                                <x-button>
                                    Add Lawyer
                                </x-button>
                            </div>
                        </form>
                    @endif

                    <!-- Lawyers List -->
                    <div class="mt-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Services</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($lawyers as $lawyer)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $lawyer->full_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $lawyer->contact_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $lawyer->city }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($lawyer->services as $service)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        {{ $service->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-gray-500 text-sm">No services assigned</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $lawyer->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                   ($lawyer->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($lawyer->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            No lawyers added yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $lawyers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 