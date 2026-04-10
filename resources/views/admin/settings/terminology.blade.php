<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('Terminology & Localization Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">SaaS Identity & Vocabulary</h3>
                    <p class="text-sm text-gray-500 mb-6">Change the core terminologies used across the system so it fits your organization perfectly. For example, change '@term('file_jacket', 'File Jacket')' to 'Patient Chart' or 'Case File'.</p>

                    <form method="POST" action="{{ route('admin.settings.terminology.update') }}">
                        @csrf
                        @method('PUT')

                        @php
                            $grouped = $terminologies->groupBy('group_name');
                        @endphp

                        @foreach($grouped as $group => $terms)
                            <h4 class="font-semibold text-primary uppercase tracking-wider text-sm mt-8 mb-4 border-b pb-2">{{ $group }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($terms as $term)
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700">
                                            {{ ucwords(str_replace('_', ' ', $term->key)) }}
                                        </label>
                                        <input type="text" name="terms[{{ $term->id }}][value]" value="{{ old('terms.'.$term->id.'.value', $term->value) }}"
                                            class="border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full">
                                        @if($term->description)
                                            <p class="text-xs text-gray-400 mt-1">{{ $term->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-800 shadow transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Save Terminology Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
