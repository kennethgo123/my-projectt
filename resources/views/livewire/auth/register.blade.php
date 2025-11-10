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
                            <div class="mt-1 relative">
                                <input wire:model="password" id="password" type="password" required
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-10">
                                <button type="button" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="toggleVisibility('password', this)">
                                    <svg class="h-5 w-5 eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="h-5 w-5 eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.142-3.442M6.219 6.219A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.042 5.132M15 12a3 3 0 00-3-3m0 0a3 3 0 013 3m-3-3L3 21" />
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Must be at least 12 characters, include 1 uppercase letter, 1 number, and 1 special character.
                            </p>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <div class="mt-1 relative">
                                <input wire:model="password_confirmation" id="password_confirmation" type="password" required
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-10">
                                <button type="button" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="toggleVisibility('password_confirmation', this)">
                                    <svg class="h-5 w-5 eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="h-5 w-5 eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.142-3.442M6.219 6.219A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.042 5.132M15 12a3 3 0 00-3-3m0 0a3 3 0 013 3m-3-3L3 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="agree" type="checkbox" wire:model="agreeTerms" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            <label for="agree" class="ml-2 block text-sm text-gray-700">
                                I agree to the 
                                <a href="{{ route('terms') }}" target="_blank" class="text-indigo-600 hover:text-indigo-500 underline">Terms and Conditions</a> 
                                and 
                                <a href="{{ route('policy') }}" target="_blank" class="text-indigo-600 hover:text-indigo-500 underline">Privacy Policy</a>
                            </label>
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
<script>
    function toggleVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const openIcon = btn.querySelector('.eye-open');
        const closedIcon = btn.querySelector('.eye-closed');
        if (input.type === 'password') {
            input.type = 'text';
            if (openIcon) openIcon.classList.add('hidden');
            if (closedIcon) closedIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            if (openIcon) openIcon.classList.remove('hidden');
            if (closedIcon) closedIcon.classList.add('hidden');
        }
    }
    // Preserve cursor position on toggle in some browsers
    ['password','password_confirmation'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('focus', function() {
                const val = this.value;
                this.value = '';
                this.value = val;
            });
        }
    });
    // Handle Livewire DOM updates
    document.addEventListener('livewire:initialized', () => {
        window.toggleVisibility = toggleVisibility;
    });
</script>