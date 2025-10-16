<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Create New Case</h2>

    <form wire:submit.prevent="createCase">
        <!-- Case Title -->
        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Case Title</label>
            <input type="text" id="title" wire:model="title" 
                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter case title (e.g. Criminal Case, Divorce Case)">
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Case Type (Optional) -->
        <div class="mb-6">
            <label for="case_type" class="block text-sm font-medium text-gray-700 mb-1">Case Type (Optional)</label>
            <select id="case_type" wire:model="case_type" 
                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Type (Optional)</option>
                <option value="civil">Civil</option>
                <option value="criminal">Criminal</option>
                <option value="corporate">Corporate</option>
                <option value="family">Family</option>
                <option value="immigration">Immigration</option>
                <option value="intellectual_property">Intellectual Property</option>
                <option value="real_estate">Real Estate</option>
                <option value="tax">Tax</option>
                <option value="other">Other</option>
            </select>
        </div>

        <!-- Categories Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Case Categories</label>
            <p class="text-gray-500 text-sm mb-4">Select one or more categories that apply to this case</p>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-2">
                @foreach($categories as $category)
                    <label class="inline-flex items-center p-3 border rounded-md 
                        {{ in_array($category->id, $selectedCategories) ? 'bg-blue-50 border-blue-500' : 'border-gray-300' }}">
                        <input type="checkbox" 
                            value="{{ $category->id }}" 
                            wire:model="selectedCategories" 
                            class="h-5 w-5 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedCategories') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Category Descriptions -->
        @if(count($selectedCategories) > 0)
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Category Descriptions</h3>
                <p class="text-gray-500 text-sm mb-4">Provide specific details for each selected category</p>
                
                <div class="space-y-4">
                    @foreach($selectedCategories as $categoryId)
                        @php $category = $categories->firstWhere('id', $categoryId); @endphp
                        <div class="p-4 border rounded-md bg-gray-50">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $category->name }} Description
                            </label>
                            <textarea 
                                wire:model="categoryDescriptions.{{ $categoryId }}" 
                                rows="3" 
                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter specific details for {{ $category->name }}..."></textarea>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Submit Button -->
        <div class="mt-8">
            <button type="submit" 
                class="w-full px-4 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Create Case
            </button>
        </div>
    </form>

    @if(session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 text-green-700 rounded-md">
            {{ session('message') }}
        </div>
    @endif
</div>
