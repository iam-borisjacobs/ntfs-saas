<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">{{ __('Create File Jacket') }}</h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 w-full">
            <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                    <div class="h-10 w-10 rounded-full bg-[#003B73]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#003B73]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">New File Jacket</h3>
                        <p class="text-sm text-gray-500">Department: <strong>{{ $department->name }}</strong></p>
                    </div>
                </div>

                <form method="POST" action="{{ route('file-jackets.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Title <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm"
                                placeholder="e.g. Budget Review 2026">
                            @error('title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Description <span
                                    class="text-gray-400 font-normal">(Optional)</span></label>
                            <textarea name="description" rows="3"
                                class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm"
                                placeholder="Brief description of the case or subject matter">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-blue-50 border border-blue-100 rounded p-3">
                            <p class="text-xs text-blue-700">
                                <strong>Note:</strong> A unique jacket code will be generated automatically based on
                                your department code and sequence number (e.g.
                                <code>{{ strtoupper($department->code ?? substr($department->name, 0, 3)) }}/{{ date('Y') }}/001</code>).
                            </p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                            <a href="{{ route('file-jackets.index') }}"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-sm text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                            <button type="submit"
                                class="px-6 py-2 bg-[#003B73] text-white text-sm font-semibold rounded-sm hover:bg-[#00294d] transition">Create
                                Jacket</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
