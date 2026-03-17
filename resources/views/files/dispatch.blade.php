<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
            {{ __('Dispatch Physical File') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="mb-6 flex space-x-4 items-center border-b pb-4">
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">File
                                Reference</span>
                            <span
                                class="block text-lg font-mono text-[#003B73]">{{ $file->file_reference_number }}</span>
                        </div>
                        <div class="flex-1 pl-4 border-l">
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Subject</span>
                            <span class="block text-md text-gray-900">{{ $file->title }}</span>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Dispatch validation failed:</h3>
                                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('files.dispatch.store', $file->uuid) }}" class="space-y-6"
                        x-data="{
                            submitting: false,
                            selectedDept: '{{ old('to_department_id', '') }}',
                            selectedUser: '{{ old('to_user_id', '') }}',
                            users: @js($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'department_id' => $u->department_id, 'dept_code' => $u->department?->code ?? 'N/A'])),
                            get filteredUsers() {
                                if (!this.selectedDept) return [];
                                return this.users.filter(u => u.department_id == this.selectedDept);
                            },
                            get dispatchLabel() {
                                if (!this.selectedDept) return 'Select a department first';
                                if (this.selectedUser) {
                                    const user = this.users.find(u => u.id == this.selectedUser);
                                    return user ? 'Direct dispatch to: ' + user.name : 'Direct dispatch';
                                }
                                return 'Dispatch to: Department Inbox';
                            },
                            get isDeptInbox() { return this.selectedDept && !this.selectedUser; }
                        }">
                        @csrf
                        <input type="hidden" name="request_uuid" value="{{ Str::uuid() }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="to_department_id" class="block text-sm font-semibold text-[#003B73]">Target
                                    Department <span class="text-red-500">*</span></label>
                                <x-custom-select>
                                    <select id="to_department_id" name="to_department_id" required x-model="selectedDept"
                                        x-on:change="selectedUser = ''"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                                        <option value="">Select Destination Department...</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}">
                                                {{ $dept->name }} ({{ $dept->code }})</option>
                                        @endforeach
                                    </select>
                                </x-custom-select>
                            </div>

                            <div>
                                <label for="to_user_id" class="block text-sm font-semibold text-[#003B73]">Target
                                    Recipient <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                                <x-custom-select>
                                    <select id="to_user_id" name="to_user_id" x-model="selectedUser"
                                        x-bind:disabled="!selectedDept"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition disabled:bg-gray-100 disabled:cursor-not-allowed">
                                        <option value="">-- Department Inbox (any officer) --</option>
                                        <template x-for="user in filteredUsers" :key="user.id">
                                            <option :value="user.id" x-text="user.name + ' (' + user.dept_code + ')'">
                                            </option>
                                        </template>
                                    </select>
                                </x-custom-select>
                                <p class="text-xs text-gray-400 mt-1">Leave empty to dispatch to the department inbox.
                                    Any officer in the department may acknowledge receipt.</p>
                            </div>
                        </div>

                        <!-- Dynamic dispatch target label -->
                        <div class="p-3 rounded border text-sm font-medium"
                            x-bind:class="isDeptInbox ? 'bg-amber-50 border-amber-200 text-amber-800' :
                                'bg-blue-50 border-blue-200 text-blue-800'"
                            x-show="selectedDept" x-cloak>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="dispatchLabel"></span>
                            </span>
                        </div>

                        <div>
                            <label for="remarks" class="block text-sm font-semibold text-[#003B73]">Movement Remarks /
                                Instructions</label>
                            <textarea id="remarks" name="remarks" rows="3"
                                class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">{{ old('remarks') }}</textarea>
                            <p class="text-xs text-gray-400 mt-1">Any instructions or notes for the recipient. Placed
                                permanently on the ledger.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('queues.pending') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-sm font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150 mr-4">
                                Cancel
                            </a>
                            <button x-on:click="submitting = true"
                                x-bind:class="{ 'opacity-50 cursor-not-allowed': submitting }" type="submit"
                                class="inline-flex items-center px-6 py-2 bg-[#003B73] border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                <span x-show="!submitting">Dispatch File</span>
                                <span x-show="submitting" x-cloak>Dispatching...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
