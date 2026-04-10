<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('queues.incoming') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-primary leading-tight">{{ __('Receive ') . App\Models\SystemTerminology::getTerm('file_jacket', 'File Jacket') }}</h2>
        </div>
    </x-slot>

    <div class="py-8 h-full flex flex-col" x-data="receiveJacketForm()">
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
                        <span class="block text-xs font-semibold text-gray-400 uppercase">From @term('department', 'Department')</span>
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
            <form method="POST" action="{{ route('jacket-movements.receive', $movement->id) }}" x-ref="receiveJacketForm">
                @csrf
                <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                    <div class="bg-blue-50 border border-blue-100 rounded p-3 mb-4">
                        <p class="text-xs text-blue-700">
                            <strong>What happens next:</strong> Receiving this jacket will transfer it and all documents
                            inside to your @term('department', 'department'). You will become the current holder.
                        </p>
                    </div>
                    <button type="button" @click="showConfirmModal = true"
                        class="w-full px-6 py-4 bg-green-600 text-white font-bold text-sm uppercase tracking-widest rounded hover:bg-green-700 transition flex items-center justify-center shadow-lg">
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
        </div> {{-- End of max-w-3xl --}}
        
        {{-- Receive Confirmation Modal --}}
        <div x-show="showConfirmModal" x-cloak
            class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95">
            <div class="relative bg-white rounded-lg shadow-2xl max-w-md w-full mx-auto p-6 text-center"
                @click.away="showConfirmModal = false">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Confirm Receipt</h3>
                <p class="text-sm text-gray-500 mb-6">Are you sure you want to accept custody of this document jacket and all files within it?</p>
                
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showConfirmModal = false"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded hover:bg-gray-200 transition">Cancel</button>
                    <button type="button" @click="$refs.receiveJacketForm.submit()"
                        class="px-5 py-2.5 bg-green-600 text-white font-semibold rounded hover:bg-green-700 shadow-md transition">
                        Yes, Receive Jacket
                    </button>
                </div>
            </div>
        </div>
    </div> {{-- End of x-data --}}

    <script>
        function receiveJacketForm() {
            return {
                showConfirmModal: false,
            };
        }
    </script>
</x-app-layout>
