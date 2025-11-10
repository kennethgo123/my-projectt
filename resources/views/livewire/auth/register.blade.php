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
                                    <svg class="h-5 w-5 eye-open transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12c1.5-4.5 6-7.5 9.75-7.5S20.25 7.5 21.75 12c-1.5 4.5-6 7.5-9.75 7.5S3.75 16.5 2.25 12z" />
                                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                                    </svg>
                                    <svg class="h-5 w-5 eye-closed hidden transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 7.418A7.93 7.93 0 0112 7.5c3.75 0 8.25 3 9.75 7.5a13.16 13.16 0 01-3.18 4.59M6.34 6.34C4.3 7.67 2.87 9.56 2.25 12c.54 1.65 1.56 3.15 2.88 4.41M9.88 14.12A3 3 0 0012 15c1.657 0 3-1.343 3-3 0-.42-.084-.82-.236-1.184" />
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
                                    <svg class="h-5 w-5 eye-open transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12c1.5-4.5 6-7.5 9.75-7.5S20.25 7.5 21.75 12c-1.5 4.5-6 7.5-9.75 7.5S3.75 16.5 2.25 12z" />
                                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                                    </svg>
                                    <svg class="h-5 w-5 eye-closed hidden transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 7.418A7.93 7.93 0 0112 7.5c3.75 0 8.25 3 9.75 7.5a13.16 13.16 0 01-3.18 4.59M6.34 6.34C4.3 7.67 2.87 9.56 2.25 12c.54 1.65 1.56 3.15 2.88 4.41M9.88 14.12A3 3 0 0012 15c1.657 0 3-1.343 3-3 0-.42-.084-.82-.236-1.184" />
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
            if (openIcon) { openIcon.classList.add('hidden'); }
            if (closedIcon) { closedIcon.classList.remove('hidden'); closedIcon.classList.add('opacity-0'); requestAnimationFrame(() => { closedIcon.classList.remove('opacity-0'); }); }
        } else {
            input.type = 'password';
            if (openIcon) { openIcon.classList.remove('hidden'); openIcon.classList.add('opacity-0'); requestAnimationFrame(() => { openIcon.classList.remove('opacity-0'); }); }
            if (closedIcon) { closedIcon.classList.add('hidden'); }
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