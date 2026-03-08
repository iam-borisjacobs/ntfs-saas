<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('queues.incoming') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-[#003B73] leading-tight">{{ __('Receive File Jacket') }}</h2>
        </div>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 w-full space-y-6">
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-700">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Jacket Info --}}
            <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-12 w-12 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 text-lg">{{ $movement->jacket->jacket_code }}</h3>
                        <p class="text-sm text-gray-500">{{ $movement->jacket->title }}</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700 uppercase">In
                        Transit</span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm border-t border-gray-100 pt-4">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">From Department</span>
                        <span
                            class="block text-gray-900 font-medium mt-0.5">{{ $movement->fromDepartment->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Dispatched By</span>
                        <span class="block text-gray-900 font-medium mt-0.5">{{ $movement->fromUser->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Dispatch Date</span>
                        <span
                            class="block text-gray-900 font-medium mt-0.5">{{ $movement->dispatched_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Documents Inside</span>
                        <span
                            class="block text-gray-900 font-bold mt-0.5">{{ $movement->jacket->currentFiles()->count() }}</span>
                    </div>
                </div>

                @if ($movement->remarks)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Dispatch Remarks</span>
                        <p class="text-sm text-gray-700 mt-1">{{ $movement->remarks }}</p>
                    </div>
                @endif
            </div>

            {{-- Receive Action --}}
            <form method="POST" action="{{ route('jacket-movements.receive', $movement->id) }}">
                @csrf
                <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                    <div class="bg-blue-50 border border-blue-100 rounded p-3 mb-4">
                        <p class="text-xs text-blue-700">
                            <strong>What happens next:</strong> Receiving this jacket will transfer it and all documents
                            inside to your department. You will become the current holder.
                        </p>
                    </div>
                    <button type="submit"
                        class="w-full px-6 py-4 bg-green-600 text-white font-bold text-sm uppercase tracking-widest rounded hover:bg-green-700 transition flex items-center justify-center shadow-lg"
                        onclick="return confirm('Receive this jacket and all its documents?');">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        RECEIVE JACKET
                    </button>
                </div>
            </form>

            <div class="text-center">
                <a href="{{ route('queues.incoming') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">←
                    Back to Incoming</a>
            </div>
        </div>
    </div>
</x-app-layout>
