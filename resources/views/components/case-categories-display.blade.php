@props(['case'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm p-4']) }}>
    <h3 class="text-lg font-semibold mb-3 text-gray-800">Case Categories</h3>
    
    @if($case->categories->count() > 0)
        <div class="space-y-4">
            @foreach($case->caseCategories as $caseCategory)
                <div class="border-b pb-3 last:border-b-0 last:pb-0">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $caseCategory->category->name }}
                        </span>
                    </div>
                    
                    @if($caseCategory->description)
                        <div class="text-sm text-gray-600 pl-2 border-l-2 border-gray-200">
                            {{ $caseCategory->description }}
                        </div>
                    @else
                        <div class="text-sm text-gray-400 italic pl-2 border-l-2 border-gray-200">
                            No specific details provided.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 text-sm">No categories have been assigned to this case.</p>
    @endif
</div> 