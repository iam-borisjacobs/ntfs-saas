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

        </div>
    </div>
</x-app-layout>
