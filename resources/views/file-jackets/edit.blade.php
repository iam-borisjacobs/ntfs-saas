<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('file-jackets.show', $jacket->id) }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-[#003B73] leading-tight">{{ __('Edit File Jacket') }}</h2>
        </div>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 w-full">
            <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                <div class="mb-6">
                    <span class="font-mono text-sm font-bold text-[#003B73]">{{ $jacket->jacket_code }}</span>
                    <span class="text-xs text-gray-500 ml-2">· {{ ucfirst($jacket->status) }}</span>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                        <ul class="text-sm text-red-700 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('file-jackets.update', $jacket->id) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-semibold text-[#003B73] mb-1">Jacket Title</label>
                        <input type="text" name="title" value="{{ old('title', $jacket->title) }}"
                            class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#003B73] mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">{{ old('description', $jacket->description) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('file-jackets.show', $jacket->id) }}"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-sm text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                        <button type="submit"
                            class="px-6 py-2 bg-[#003B73] text-white rounded-sm text-sm font-bold hover:bg-[#00294d] transition">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
