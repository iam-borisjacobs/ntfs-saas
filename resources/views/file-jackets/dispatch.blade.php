<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('file-jackets.show', $jacket->id) }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-[#003B73] leading-tight">{{ __('Dispatch Jacket') }}</h2>
        </div>
    </x-slot>

    <div class="py-8 h-full flex flex-col" x-data="dispatchForm()">
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
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-lg bg-[#003B73]/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-[#003B73]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 text-lg">{{ $jacket->jacket_code }}</h3>
                        <p class="text-sm text-gray-500">{{ $jacket->title }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 uppercase font-semibold">Documents</p>
                        <p class="text-lg font-bold text-[#003B73]">{{ $jacket->currentFiles()->count() }}</p>
                    </div>
                </div>
            </div>

            {{-- Dispatch Form --}}
            <form method="POST" action="{{ route('file-jackets.dispatch.store', $jacket->id) }}">
                @csrf

                <div class="bg-white p-6 shadow-sm border border-gray-200 rounded space-y-5">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-[#003B73]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <h4 class="font-bold text-[#003B73] text-sm uppercase tracking-wider">Dispatch Details</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Destination Department <span
                                class="text-red-500">*</span></label>
                        <select name="to_department_id" x-model="toDeptId" @change="loadUsers()"
                            class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">
                            <option value="">Select Department...</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Specific Officer <span
                                class="text-gray-400 font-normal">(Optional)</span></label>
                        <select name="to_user_id" x-model="toUserId" :disabled="!toDeptId || loadingUsers"
                            class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm disabled:bg-gray-100">
                            <option value="">Department Inbox (Any Officer)</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="`${user.name} (${user.staff_id})`"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Leave empty to send to the department inbox.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks <span
                                class="text-gray-400 font-normal">(Optional)</span></label>
                        <textarea name="remarks" rows="3" placeholder="Add dispatch remarks..."
                            class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="bg-amber-50 border border-amber-100 rounded p-3">
                        <p class="text-xs text-amber-700">
                            <strong>Note:</strong> Dispatching this jacket will move all
                            {{ $jacket->currentFiles()->count() }} document(s) together. The jacket status will change
                            to <strong>In Transit</strong> until received.
                        </p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="bg-white p-6 shadow-sm border border-gray-200 rounded mt-6">
                    <button type="submit" :disabled="!toDeptId"
                        class="w-full px-6 py-4 bg-[#003B73] text-white font-bold text-sm uppercase tracking-widest rounded hover:bg-[#00294d] transition flex items-center justify-center shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        onclick="return confirm('Dispatch this jacket and all its documents?');">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        DISPATCH JACKET
                    </button>
                </div>
            </form>

            <div class="text-center">
                <a href="{{ route('file-jackets.show', $jacket->id) }}"
                    class="text-sm text-gray-500 hover:text-gray-700 underline">← Back to Jacket</a>
            </div>
        </div>
    </div>

    <script>
        function dispatchForm() {
            return {
                toDeptId: '',
                toUserId: '',
                users: [],
                loadingUsers: false,

                async loadUsers() {
                    this.toUserId = '';
                    this.users = [];
                    if (!this.toDeptId) return;
                    this.loadingUsers = true;
                    try {
                        const res = await fetch(`/api/departments/${this.toDeptId}/users`);
                        this.users = await res.json();
                    } catch (e) {
                        console.error('Failed to load users:', e);
                    } finally {
                        this.loadingUsers = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
