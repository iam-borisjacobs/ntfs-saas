<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#003B73] leading-tight flex items-center gap-3">
                <a href="javascript:history.back()" class="text-gray-400 hover:text-[#003B73] transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                {{ __('File Details: ') }} <span
                    class="font-mono text-gray-600">{{ $file->file_reference_number }}</span>
            </h2>
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-[#003B73]">
                {{ $file->status->name ?? 'UNKNOWN' }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- File Metadata Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Core Metadata</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Subject /
                                Title</span>
                            <span class="block text-md font-medium text-gray-900 mt-1">{{ $file->title }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Priority
                                Level</span>
                            <span class="block text-md font-medium text-gray-900 mt-1">
                                @if ($file->priority_level == 1)
                                    <span class="text-gray-600">Standard</span>
                                @elseif($file->priority_level == 2)
                                    <span class="text-yellow-600 font-bold">Urgent</span>
                                @elseif($file->priority_level == 3)
                                    <span class="text-red-600 font-bold">Emergency</span>
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Data
                                Classification</span>
                            <span class="block text-md font-medium text-gray-900 mt-1">
                                Level {{ $file->classification_level }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Originating
                                Department</span>
                            <span
                                class="block text-md font-medium text-gray-900 mt-1">{{ $file->originatingDepartment->name ?? 'System' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Current
                                Custodian</span>
                            <span class="block text-md font-medium text-gray-900 mt-1">
                                {{ $file->currentOwner->name ?? 'System' }}
                                <span
                                    class="text-sm text-gray-500">({{ $file->currentDepartment->code ?? 'N/A' }})</span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Creation
                                Date</span>
                            <span
                                class="block text-md font-medium text-gray-900 mt-1">{{ \Carbon\Carbon::parse($file->created_at)->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Audit Trail / Ledger --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-6">Custody & Movement Ledger</h3>

                    <div class="relative wrap overflow-hidden p-2 h-full">
                        <div class="border-2-2 absolute border-opacity-20 border-gray-700 h-full border-l-2"
                            style="left: 15px;"></div>

                        @forelse($file->movements as $movement)
                            <div class="mb-8 flex justify-between items-start w-full relative">
                                <div
                                    class="w-8 h-8 rounded-full bg-[#003B73] shadow-md flex items-center justify-center absolute -left-1 z-10">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                                <div class="ml-10 w-full bg-gray-50 rounded-lg shadow-sm p-4 border border-gray-100">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-bold text-gray-900">{{ $movement->movement_type }}</h4>
                                        <span
                                            class="text-xs font-semibold px-2 py-1 rounded 
                                            @if ($movement->acknowledgment_status == 'ACCEPTED') bg-green-100 text-green-800 
                                            @elseif($movement->acknowledgment_status == 'REJECTED') bg-red-100 text-red-800 
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $movement->acknowledgment_status }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 text-sm">
                                        <div>
                                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">From</p>
                                            <p class="font-medium text-gray-900">
                                                {{ $movement->fromUser->name ?? 'System' }}</p>
                                            <p class="text-gray-500 text-xs">
                                                {{ $movement->fromDepartment->name ?? 'N/A' }}</p>
                                            <p class="text-gray-400 text-xs mt-1">
                                                {{ optional($movement->dispatched_at)->format('d M Y, H:i:s') }}</p>
                                        </div>
                                        @if ($movement->to_user_id)
                                            <div>
                                                <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">To
                                                </p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $movement->toUser->name ?? 'Unknown' }}</p>
                                                <p class="text-gray-500 text-xs">
                                                    {{ $movement->toDepartment->name ?? 'N/A' }}</p>
                                                <p class="text-gray-400 text-xs mt-1">
                                                    {{ optional($movement->received_at)->format('d M Y, H:i:s') ?? 'Pending Acceptance' }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($movement->remarks)
                                        <div class="mt-4 pt-3 border-t border-gray-200">
                                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">
                                                Remarks</p>
                                            <p class="text-gray-700 italic text-sm">{{ $movement->remarks }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 italic">No movement history found.</p>
                        @endforelse
                    </div>

                </div>
            </div>

            {{-- Phase 12: Digital Attachments --}}
            @if (config('digital_module.enabled', true))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center border-b pb-2 mb-6">
                            <h3 class="text-lg font-bold text-gray-900">Digital Attachments</h3>

                            @can('update', $file)
                                <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-[#003B73] text-white text-sm font-semibold rounded hover:bg-[#002b54] transition shadow-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                        </path>
                                    </svg>
                                    Attach Document
                                </button>
                            @endcan
                        </div>

                        <div class="space-y-4">
                            @forelse($file->documents as $doc)
                                @can('view', $doc)
                                    <div
                                        class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-lg shadow-sm hover:shadow-md transition">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-10 h-10 rounded bg-blue-100 text-[#003B73] flex items-center justify-center font-bold">
                                                {{ strtoupper(pathinfo($doc->file_name, PATHINFO_EXTENSION)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ $doc->file_name }} <span
                                                        class="text-xs text-gray-500 font-normal ml-2">v{{ number_format($doc->version_number, 1) }}</span>
                                                </p>
                                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                                                    <span>{{ number_format($doc->file_size / 1024 / 1024, 2) }} MB</span>
                                                    <span>&bull;</span>
                                                    <span>{{ $doc->document_type }}</span>
                                                    <span>&bull;</span>
                                                    <span>Uploaded by {{ $doc->uploader->name ?? 'Unknown' }} on
                                                        {{ $doc->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('documents.download', $doc) }}"
                                                class="p-2 text-gray-500 hover:text-[#003B73] hover:bg-blue-50 rounded transition"
                                                title="Download">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endcan
                            @empty
                                <div
                                    class="text-center py-8 text-gray-500 bg-gray-50 border border-gray-100 border-dashed rounded-lg">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p class="font-medium">No digital attachments found.</p>
                                    <p class="text-xs mt-1">Physical records can be scanned and securely attached here.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Upload Modal --}}
                <div id="upload-modal" class="fixed inset-0 z-50 hidden overflow-y-auto"
                    aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div
                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                            onclick="document.getElementById('upload-modal').classList.add('hidden')"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                            aria-hidden="true">&#8203;</span>

                        <div
                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <form action="{{ route('documents.store', $file) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                        Attach Digital Document</h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label for="document_type"
                                                class="block text-sm font-medium text-gray-700">Document Type</label>
                                            <select id="document_type" name="document_type" required
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#003B73] focus:border-[#003B73] sm:text-sm rounded-md">
                                                <option value="Memo">Memo</option>
                                                <option value="Official Letter">Official Letter</option>
                                                <option value="Approval">Approval</option>
                                                <option value="Attachment">Attachment</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="document"
                                                class="block text-sm font-medium text-gray-700 mb-2">Select
                                                File</label>
                                            <div
                                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-[#003B73] transition">
                                                <div class="space-y-1 text-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor"
                                                        fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                        <path
                                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                    <div class="flex text-sm text-gray-600 justify-center">
                                                        <label for="document-upload"
                                                            class="relative cursor-pointer bg-white rounded-md font-medium text-[#003B73] hover:text-[#002b54] focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#003B73]">
                                                            <span>Upload a file</span>
                                                            <input id="document-upload" name="document"
                                                                type="file" class="sr-only" required
                                                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                        </label>
                                                        <p class="pl-1">or drag and drop</p>
                                                    </div>
                                                    <p class="text-xs text-gray-500">PDF, JPG, PNG, DOCX up to 10MB</p>
                                                    <!-- Dynamic File Name Display -->
                                                    <p id="file-chosen-name"
                                                        class="text-sm font-bold text-green-600 mt-2 hidden"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="submit"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#003B73] text-base font-medium text-white hover:bg-[#002b54] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#003B73] sm:ml-3 sm:w-auto sm:text-sm">Secure
                                        Upload</button>
                                    <button type="button"
                                        onclick="document.getElementById('upload-modal').classList.add('hidden')"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#003B73] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    document.getElementById('document-upload').addEventListener('change', function(e) {
                        var fileName = e.target.files[0].name;
                        var label = document.getElementById('file-chosen-name');
                        label.textContent = "Selected: " + fileName;
                        label.classList.remove('hidden');
                    });
                </script>
            @endif

        </div>
    </div>
</x-app-layout>
