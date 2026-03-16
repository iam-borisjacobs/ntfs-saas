<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('file-jackets.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <svg class="w-6 h-6 text-[#003B73]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <h2 class="font-semibold text-xl text-[#003B73] leading-tight">{{ $jacket->jacket_code }}</h2>
            </div>
            <span
                class="px-3 py-1 text-xs font-semibold rounded-full
                {{ $jacket->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                {{ $jacket->status === 'closed' ? 'bg-gray-100 text-gray-600' : '' }}
                {{ $jacket->status === 'archived' ? 'bg-amber-100 text-amber-700' : '' }}">
                {{ ucfirst($jacket->status) }}
            </span>
        </div>
    </x-slot>

    <div class="py-8 h-full flex flex-col" x-data="jacketActions()">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded">
                    {{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Jacket Info Card --}}
            <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Title</span>
                        <span class="block text-md font-medium text-gray-900 mt-1">{{ $jacket->title }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Department</span>
                        <span
                            class="block text-md font-medium text-gray-900 mt-1">{{ $jacket->department->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Created By</span>
                        <span
                            class="block text-md font-medium text-gray-900 mt-1">{{ $jacket->creator->name ?? '—' }}</span>
                        <span class="text-xs text-gray-400">{{ $jacket->created_at->format('d M Y') }}</span>
                    </div>
                </div>

                {{-- Location Details --}}
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-6 bg-gray-50 p-4 rounded-md border">
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Current
                            Location</span>
                        <div class="flex items-center mt-1">
                            <svg class="w-4 h-4 text-gray-400 mr-1.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            <span
                                class="text-md font-medium text-gray-900">{{ $jacket->currentDepartment->name ?? $jacket->department->name }}</span>
                        </div>
                    </div>
                    @if ($jacket->current_holder_user_id)
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Current
                                Holder</span>
                            <div class="flex items-center mt-1">
                                <svg class="w-4 h-4 text-gray-400 mr-1.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span
                                    class="text-md font-medium text-gray-900">{{ $jacket->currentHolder->name }}</span>
                            </div>
                        </div>
                    @endif
                    @if ($jacket->hasPendingDispatch())
                        <div class="ml-auto">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5 mr-1.5 animate-pulse" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Dispatch Pending Receipt
                            </span>
                        </div>
                    @endif
                </div>
                @if ($jacket->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <span
                            class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Description</span>
                        <p class="text-sm text-gray-700">{{ $jacket->description }}</p>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2 justify-end">
                    @if (
                        $jacket->status === 'active' &&
                            $jacket->current_department_id === Auth::user()->department_id &&
                            !$jacket->hasPendingDispatch())
                        <a href="{{ route('file-jackets.dispatch.create', $jacket->id) }}"
                            class="inline-flex items-center px-4 py-1.5 bg-[#003B73] text-white text-xs font-semibold rounded hover:bg-[#00294d] transition shadow-sm mr-auto">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Dispatch Jacket
                        </a>
                    @endif

                    <a href="{{ route('file-jackets.edit', $jacket->id) }}"
                        class="inline-flex items-center px-3 py-1.5 bg-white border border-[#003B73] text-[#003B73] text-xs font-semibold rounded hover:bg-[#003B73] hover:text-white transition">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Edit
                    </a>

                    @if ($jacket->status === 'active')
                        <form method="POST" action="{{ route('file-jackets.close', $jacket->id) }}" class="inline" x-ref="closeForm">
                            @csrf
                            <button type="button"
                                class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-400 text-gray-600 text-xs font-semibold rounded-sm hover:bg-gray-100 transition"
                                @click="openModal('close')">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                Close
                            </button>
                        </form>
                        <form method="POST" action="{{ route('file-jackets.archive', $jacket->id) }}"
                            class="inline" x-ref="archiveForm">
                            @csrf
                            <button type="button"
                                class="inline-flex items-center px-3 py-1.5 bg-white border border-amber-400 text-amber-700 text-xs font-semibold rounded-sm hover:bg-amber-50 transition"
                                @click="openModal('archive')">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                    </path>
                                </svg>
                                Archive
                            </button>
                        </form>
                    @endif

                    @if (in_array($jacket->status, ['closed', 'archived']))
                        <form method="POST" action="{{ route('file-jackets.reactivate', $jacket->id) }}"
                            class="inline" x-ref="reactivateForm">
                            @csrf
                            <button type="button"
                                class="inline-flex items-center px-3 py-1.5 bg-white border border-green-500 text-green-700 text-xs font-semibold rounded-sm hover:bg-green-50 transition"
                                @click="openModal('reactivate')">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Reactivate
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Documents in Jacket --}}
            <div class="bg-white shadow-sm border border-gray-200 rounded" x-data="{ showFileModal: false }">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Documents in Jacket
                        <span class="text-sm font-normal text-gray-500">({{ $files->count() }})</span>
                    </h3>
                    @if ($jacket->status === 'active')
                        <div class="flex gap-2">
                            @if ($availableFiles->count())
                                <button type="button" @click="showFileModal = true"
                                    class="inline-flex items-center px-3 py-1.5 bg-white border border-[#003B73] text-[#003B73] text-xs font-semibold rounded-sm hover:bg-[#003B73] hover:text-white transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    File Document
                                </button>
                            @endif
                            <a href="{{ route('files.create', ['file_jacket_id' => $jacket->id]) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-[#003B73] text-white text-xs font-semibold rounded-sm hover:bg-[#00294d] transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                New Document
                            </a>
                        </div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    File Reference</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Title</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Priority</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Current Holder</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Last Movement</th>
                                <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($files as $file)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-gray-600">
                                        {{ $file->file_reference_number }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900 font-medium line-clamp-1">{{ $file->title }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($file->priority_level === 3)
                                            <span class="text-xs font-bold text-red-600">Critical</span>
                                        @elseif ($file->priority_level === 2)
                                            <span class="text-xs font-bold text-orange-600">Urgent</span>
                                        @else
                                            <span class="text-xs text-gray-500">Routine</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $file->currentOwner->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-400">
                                        {{ $file->movements->first() ? $file->movements->first()->dispatched_at->format('d M Y') : $file->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2" x-data="{ showRemoveModal: false }">
                                            <a href="{{ route('files.show', $file->uuid) }}"
                                                class="text-[#003B73] hover:underline font-semibold text-xs">View</a>
                                            @if ($jacket->status === 'active')
                                                <form method="POST"
                                                    action="{{ route('file-jackets.unfile-document', $jacket->id) }}"
                                                    class="inline" x-ref="removeForm">
                                                    @csrf
                                                    <input type="hidden" name="file_id" value="{{ $file->id }}">
                                                    <button type="button"
                                                        class="text-red-500 hover:text-red-700 text-xs font-semibold"
                                                        @click="showRemoveModal = true">Remove</button>
                                                </form>

                                                {{-- Inline Remove Confirmation Modal --}}
                                                <div x-show="showRemoveModal" x-cloak
                                                    class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
                                                    x-transition:enter="transition ease-out duration-300"
                                                    x-transition:enter-start="opacity-0 transform scale-95"
                                                    x-transition:enter-end="opacity-100 transform scale-100"
                                                    x-transition:leave="transition ease-in duration-200"
                                                    x-transition:leave-start="opacity-100 transform scale-100"
                                                    x-transition:leave-end="opacity-0 transform scale-95">
                                                    <div class="relative bg-white rounded-lg shadow-2xl max-w-md w-full mx-auto p-6 text-center"
                                                        @click.away="showRemoveModal = false">
                                                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </div>
                                                        <h3 class="text-xl font-bold text-gray-900 mb-2">Remove Document</h3>
                                                        <p class="text-sm text-gray-500 mb-6">Are you sure you want to remove <strong class="font-mono">{{ $file->file_reference_number }}</strong> from this jacket?</p>
                                                        
                                                        <div class="flex justify-center gap-3">
                                                            <button type="button" @click="showRemoveModal = false"
                                                                class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded hover:bg-gray-200 transition">Cancel</button>
                                                            <button type="button" @click="$refs.removeForm.submit()"
                                                                class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded hover:bg-red-700 shadow-md transition">
                                                                Yes, Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">No
                                        documents in this jacket.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- File Document Modal --}}
                @if ($jacket->status === 'active' && $availableFiles->count())
                    <div x-show="showFileModal" x-cloak
                        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
                        <div class="relative bg-white rounded-lg shadow-xl max-w-lg mx-auto w-full"
                            @click.away="showFileModal = false">
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">File Document Into Jacket</h3>
                                <p class="text-sm text-gray-500 mb-4">Select an unfiled document from your department.
                                </p>
                                <form method="POST" action="{{ route('file-jackets.file-document', $jacket->id) }}">
                                    @csrf
                                    <select name="file_id"
                                        class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm mb-4">
                                        <option value="">Select Document...</option>
                                        @foreach ($availableFiles as $af)
                                            <option value="{{ $af->id }}">{{ $af->file_reference_number }} —
                                                {{ $af->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" @click="showFileModal = false"
                                            class="px-4 py-2 bg-white border border-gray-300 rounded-sm text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-[#003B73] text-white rounded-sm text-sm font-semibold hover:bg-[#00294d] transition">File
                                            Document</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Activity Timeline --}}
            @if ($timeline->count())
                <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                    <h3 class="font-bold text-gray-900 mb-4">Activity Timeline</h3>
                    <div class="relative">
                        <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-gray-200"></div>
                        @foreach ($timeline->take(20) as $event)
                            <div class="mb-4 flex items-start gap-4 relative">
                                <div
                                    class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center z-10
                                    {{ $event['status'] === 'ACCEPTED' ? 'bg-green-100' : '' }}
                                    {{ $event['status'] === 'PENDING' ? 'bg-yellow-100' : '' }}
                                    {{ $event['status'] === 'REJECTED' ? 'bg-red-100' : '' }}
                                    {{ !in_array($event['status'], ['ACCEPTED', 'PENDING', 'REJECTED']) ? 'bg-gray-100' : '' }}">
                                    <div
                                        class="w-2 h-2 rounded-full
                                        {{ $event['status'] === 'ACCEPTED' ? 'bg-green-500' : '' }}
                                        {{ $event['status'] === 'PENDING' ? 'bg-yellow-500' : '' }}
                                        {{ $event['status'] === 'REJECTED' ? 'bg-red-500' : '' }}
                                        {{ !in_array($event['status'], ['ACCEPTED', 'PENDING', 'REJECTED']) ? 'bg-gray-400' : '' }}">
                                    </div>
                                </div>
                                <div class="flex-1 bg-gray-50 rounded p-3 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <a href="{{ route('files.show', $event['file_uuid']) }}"
                                            class="text-sm font-semibold text-[#003B73] hover:underline">{{ $event['file_ref'] }}</a>
                                        <span
                                            class="text-xs text-gray-400">{{ optional($event['date'])->format('d M Y H:i') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <span class="font-medium">{{ $event['from'] }}</span> →
                                        <span class="font-medium">{{ $event['to'] }}</span>
                                        <span
                                            class="ml-1 text-xs px-1.5 py-0.5 rounded
                                            {{ $event['status'] === 'ACCEPTED' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $event['status'] === 'PENDING' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $event['status'] === 'REJECTED' ? 'bg-red-100 text-red-700' : '' }}">{{ $event['status'] }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Jacket Movement History --}}
            @if ($jacketMovements->count())
                <div class="bg-white shadow-sm border border-gray-200 rounded">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900">Jacket Movement History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        From</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        To</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Dispatched</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Received</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($jacketMovements as $mov)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3">
                                            <span
                                                class="block text-gray-900 font-medium">{{ $mov->fromDepartment->name }}</span>
                                            <span
                                                class="block text-xs text-gray-500">{{ $mov->fromUser->name }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="block text-gray-900 font-medium">{{ $mov->toDepartment->name }}</span>
                                            @if ($mov->toUser)
                                                <span
                                                    class="block text-xs text-gray-500">{{ $mov->toUser->name }}</span>
                                            @else
                                                <span class="block text-xs text-gray-400 italic">Department
                                                    Inbox</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600">
                                            {{ $mov->dispatched_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600">
                                            @if ($mov->received_at)
                                                <span
                                                    class="block">{{ $mov->received_at->format('d M Y, H:i') }}</span>
                                                <span class="block text-gray-400">by
                                                    {{ $mov->receivedBy->name }}</span>
                                            @else
                                                <span class="text-gray-400 italic">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                {{ $mov->status === 'RECEIVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $mov->status === 'RECEIVED' ? 'Received' : 'Pending' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        {{-- Global Confirmation Modal for Jacket Actions --}}
        <div x-show="showModal" x-cloak
            class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95">
            <div class="relative bg-white rounded-lg shadow-2xl max-w-sm mx-auto w-full p-6 text-center"
                @click.away="showModal = false">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4"
                     :class="{'bg-[#003B73]/10 text-[#003B73]': modalType === 'close', 'bg-amber-100 text-amber-600': modalType === 'archive', 'bg-green-100 text-green-600': modalType === 'reactivate'}">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="modalTitle"></h3>
                <p class="text-sm text-gray-500 mb-6" x-text="modalText"></p>
                
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showModal = false"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded hover:bg-gray-200 transition">Cancel</button>
                    <button type="button" @click="confirmAction()"
                        class="px-5 py-2.5 text-white font-semibold rounded shadow-md transition"
                        :class="{'bg-[#003B73] hover:bg-[#00294d]': modalType === 'close', 'bg-amber-500 hover:bg-amber-600': modalType === 'archive', 'bg-green-600 hover:bg-green-700': modalType === 'reactivate'}">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('jacketActions', () => ({
                showModal: false,
                modalType: '',
                modalTitle: '',
                modalText: '',
                confirmAction() {
                    if (this.modalType === 'close') this.$refs.closeForm.submit();
                    if (this.modalType === 'archive') this.$refs.archiveForm.submit();
                    if (this.modalType === 'reactivate') this.$refs.reactivateForm.submit();
                },
                openModal(type) {
                    this.modalType = type;
                    if (type === 'close') {
                        this.modalTitle = 'Close Jacket';
                        this.modalText = 'Close this jacket? It will stop accepting new documents.';
                    } else if (type === 'archive') {
                        this.modalTitle = 'Archive Jacket';
                        this.modalText = 'Archive this jacket? It will be moved to long-term storage.';
                    } else if (type === 'reactivate') {
                        this.modalTitle = 'Reactivate Jacket';
                        this.modalText = 'Reactivate this jacket?';
                    }
                    this.showModal = true;
                }
            }));
        });
    </script>
</x-app-layout>
