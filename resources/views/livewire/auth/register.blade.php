<div class="min-h-screen relative">
    <!-- Background Image -->
    <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('/images/bg.png');"></div>
    <!-- Dark overlay -->
    <div class="absolute inset-0 bg-black/55"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white shadow-2xl rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="mb-4 text-center">
                        <x-application-logo class="mx-auto h-10 w-auto" />
                        <h2 class="mt-3 text-2xl font-extrabold text-gray-900">Create your account</h2>
                    </div>

                    <form wire:submit="register" class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                            <div class="mt-1">
                                <input wire:model="email" id="email" type="email" required
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">I want to register as</label>
                            <div class="mt-1">
                                <select wire:model="selectedRole" id="role" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Select a role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('selectedRole')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <div class="mt-1">
                                <input wire:model="password" id="password" type="password" required
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <div class="mt-1">
                                <input wire:model="password_confirmation" id="password_confirmation" type="password" required
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="agree" type="checkbox" wire:model="agreeTerms" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            <label for="agree" class="ml-2 block text-sm text-gray-700">I agree to the Terms and Privacy Policy</label>
                        </div>
                        @error('agreeTerms')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div>
                            <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 