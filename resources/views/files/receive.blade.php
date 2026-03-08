<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('queues.incoming') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-[#003B73] leading-tight">{{ __('Receive Document') }}</h2>
        </div>
    </x-slot>

    <div class="py-8 h-full flex flex-col" x-data="receiveForm()">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 w-full space-y-6">
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Document Info Card --}}
            <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-[#003B73]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 text-lg">{{ $movement->file->title }}</h3>
                        <p class="text-sm text-gray-500 font-mono">{{ $movement->file->file_reference_number }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded bg-yellow-100 text-yellow-800">PENDING
                        RECEIPT</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Sent By</p>
                        <p class="text-sm font-medium text-gray-900">{{ $movement->fromUser->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">From Department</p>
                        <p class="text-sm font-medium text-gray-900">{{ $movement->fromDepartment->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Dispatched</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $movement->dispatched_at ? $movement->dispatched_at->format('d M Y, H:i') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Priority</p>
                        <p class="text-sm font-medium text-gray-900">
                            @if ($movement->file->priority_level === 3)
                                <span class="text-red-600 font-bold">Critical</span>
                            @elseif ($movement->file->priority_level === 2)
                                <span class="text-orange-600 font-bold">Urgent</span>
                            @else
                                <span class="text-gray-600">Routine</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Filing Section --}}
            <form method="POST" action="{{ route('movements.receive', $movement->id) }}">
                @csrf

                <div class="bg-white p-6 shadow-sm border border-gray-200 rounded">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-[#003B73]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <h4 class="font-bold text-[#003B73] text-sm uppercase tracking-wider">Document Filing</h4>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Select a file jacket to physically file this document. You can
                        leave this empty to file later.</p>

                    <div class="flex gap-2">
                        <select name="file_jacket_id" id="file_jacket_id"
                            class="block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition text-sm">
                            <option value="">— Not Filing Now —</option>
                            @foreach ($jackets as $jacket)
                                <option value="{{ $jacket->id }}">{{ $jacket->jacket_code }} — {{ $jacket->title }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" @click="showJacketModal = true"
                            class="flex-shrink-0 inline-flex items-center px-3 py-2 bg-white border border-[#003B73] text-[#003B73] text-xs font-semibold rounded-sm hover:bg-[#003B73] hover:text-white transition">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            New
                        </button>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 rounded p-3 mt-4">
                        <p class="text-xs text-blue-700">
                            <strong>Tip:</strong> Filing a document into a jacket records where it is physically stored.
                            This can be changed later if needed.
                        </p>
                    </div>
                </div>

                {{-- Receive Button --}}
                <div class="bg-white p-6 shadow-sm border border-gray-200 rounded mt-6">
                    <button type="submit"
                        class="w-full px-6 py-4 bg-green-600 text-white font-bold text-sm uppercase tracking-widest rounded hover:bg-green-700 transition flex items-center justify-center shadow-lg"
                        onclick="return confirm('Confirm: Accept custody of this document?');">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        RECEIVE DOCUMENT
                    </button>
                </div>
            </form>

            <div class="mt-2 text-center">
                <a href="{{ route('queues.incoming') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    ← Back to Incoming
                </a>
            </div>
        </div>

        {{-- Inline Jacket Creation Modal --}}
        <div x-show="showJacketModal" x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md mx-auto w-full"
                @click.away="showJacketModal = false">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-10 w-10 rounded-full bg-[#003B73]/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#003B73]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Create New File Jacket</h3>
                            <p class="text-sm text-gray-500">Department:
                                {{ Auth::user()->department->name ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jacket Title <span
                                    class="text-red-500">*</span></label>
                            <input type="text" x-model="jacketTitle" placeholder="e.g. Budget Review 2026"
                                class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Description <span
                                    class="text-gray-400 font-normal">(Optional)</span></label>
                            <textarea x-model="jacketDesc" rows="2" placeholder="Brief description"
                                class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm"></textarea>
                        </div>
                        <p class="text-xs text-red-600" x-show="jacketError" x-text="jacketError"></p>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showJacketModal = false"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-sm text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                            <button type="button" @click="createJacket()" :disabled="jacketLoading"
                                class="px-4 py-2 bg-[#003B73] text-white rounded-sm text-sm font-semibold hover:bg-[#00294d] transition disabled:opacity-50">
                                <span x-show="!jacketLoading">Create Jacket</span>
                                <span x-show="jacketLoading">Creating...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function receiveForm() {
            return {
                showJacketModal: false,
                jacketTitle: '',
                jacketDesc: '',
                jacketError: '',
                jacketLoading: false,

                async createJacket() {
                    if (!this.jacketTitle.trim()) {
                        this.jacketError = 'Jacket title is required.';
                        return;
                    }
                    this.jacketError = '';
                    this.jacketLoading = true;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const res = await fetch('{{ route('file-jackets.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                title: this.jacketTitle,
                                description: this.jacketDesc,
                            }),
                        });

                        if (!res.ok) {
                            const err = await res.json();
                            this.jacketError = err.message || 'Failed to create jacket.';
                            return;
                        }

                        const data = await res.json();

                        // Add the new jacket to the dropdown and select it
                        const select = document.getElementById('file_jacket_id');
                        const option = new Option(`${data.jacket_code} — ${data.title}`, data.id, true, true);
                        select.appendChild(option);

                        this.showJacketModal = false;
                        this.jacketTitle = '';
                        this.jacketDesc = '';
                    } catch (e) {
                        this.jacketError = 'Network error. Please try again.';
                    } finally {
                        this.jacketLoading = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
