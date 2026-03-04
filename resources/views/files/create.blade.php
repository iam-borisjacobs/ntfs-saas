<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
            {{ __('Generate or Create File') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 flex-1">
            <div class="bg-white p-8 shadow-sm border border-gray-200">
                <p class="text-sm text-gray-500 mb-8 border-b border-gray-100 pb-4">Register a new physical file into the
                    system to begin formal tracking and custody logging.</p>

                <form action="{{ route('files.store') }}" method="POST" class="max-w-2xl space-y-6">
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @csrf
                    <div>
                        <label for="title" class="block text-sm font-semibold text-[#003B73]">File Subject /
                            Title</label>
                        <input type="text" name="title" id="title"
                            class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                        <p class="mt-1 text-xs text-gray-400">The primary descriptive title written on the physical
                            folder.</p>
                    </div>

                    <div>
                        <label for="department_id" class="block text-sm font-semibold text-[#003B73]">Originating
                            Department</label>
                        <select id="department_id" name="department_id"
                            class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                            <option value="">Select Department...</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="priority" class="block text-sm font-semibold text-[#003B73]">Priority
                                Level</label>
                            <select id="priority" name="priority_level"
                                class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] shadow-sm">
                                <option value="1">1 - Routine</option>
                                <option value="2">2 - Urgent</option>
                                <option value="3">3 - Critical</option>
                            </select>
                        </div>
                        <div>
                            <label for="confidentiality"
                                class="block text-sm font-semibold text-[#003B73]">Confidentiality Clearance</label>
                            <select id="confidentiality" name="confidentiality_level"
                                class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] shadow-sm">
                                <option value="1">Level 1 (General)</option>
                                <option value="2">Level 2 (Restricted)</option>
                                <option value="3">Level 3 (Secret)</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit"
                            class="px-6 py-3 bg-[#003B73] border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-[#003B73] focus:ring-offset-2 transition ease-in-out duration-150 flex items-center shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            GENERATE FILE RECORD
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
