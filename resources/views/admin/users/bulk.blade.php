<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('Bulk Onboard Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-8 shadow-sm border border-gray-200">
                
                <div class="mb-6 border-b pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Upload CSV File</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Use this tool to onboard a large number of staff simultaneously. Please ensure your file conforms to the strict CSV format.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <ul class="list-disc pl-5 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.users.bulk.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div class="border-2 border-dashed border-gray-300 p-8 text-center rounded-sm bg-gray-50 hover:bg-gray-100 transition">
                        <label for="csv_file" class="cursor-pointer">
                            <span class="block text-sm font-semibold text-primary mb-2">Select CSV File</span>
                            <div class="flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <input type="file" name="csv_file" id="csv_file" class="hidden" accept=".csv" required onchange="document.getElementById('file-name').textContent = this.files[0].name">
                            <span id="file-name" class="block text-xs text-gray-600 mt-3 font-mono">No file chosen</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
                        <div class="text-sm">
                            <h4 class="font-bold text-gray-900">Required CSV Columns:</h4>
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded text-red-600 font-mono mt-1 block">name,email,phone_number,system_identifier,department_id,clearance_level,role</code>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900 font-semibold text-sm mt-1">Cancel</a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-sm font-bold text-sm uppercase tracking-wide hover:bg-blue-800 transition shadow-md">
                                Process Upload
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
