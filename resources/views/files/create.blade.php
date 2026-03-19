<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
            {{ __('Generate or Create File') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 flex-1">
            <div class="bg-white p-8 shadow-sm border border-gray-200 rounded-sm">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <div class="lg:col-span-2" x-data="generateForm()">
                        <div class="mb-8 border-b border-gray-100 pb-4">
                            <h3 class="text-lg font-bold text-[#003B73] mb-1">File Metadata</h3>
                            <p class="text-sm text-gray-500">Register a new physical file into the system to begin
                                formal
                                tracking and custody logging.</p>
                        </div>

                        <form action="{{ route('files.store') }}" method="POST" class="space-y-6"
                            enctype="multipart/form-data">
                            @if ($errors->any())
                                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">There were errors with your
                                                submission
                                            </h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @csrf

                            {{-- Reference Document (Optional) --}}
                            <div class="mb-6">
                                <label for="reference_file_id" class="block text-sm font-semibold text-[#003B73]">Reference
                                    Document (Optional)</label>
                                <x-custom-select>
                                    <select id="reference_file_id" name="reference_file_id" @change="handleReferenceSelection($event)"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                                        <option value="">— No Reference —</option>
                                        @foreach ($closedFiles as $closedFile)
                                            <option value="{{ $closedFile->id }}"
                                                {{ old('reference_file_id') == $closedFile->id ? 'selected' : '' }}>
                                                {{ $closedFile->file_reference_number }} — {{ $closedFile->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </x-custom-select>
                                <p class="mt-1 text-xs text-gray-400">Select a previously closed document that this new file replies to or references.</p>
                            </div>

                            {{-- Title --}}
                            <div class="pt-4 border-t border-gray-100">
                                <label for="title" class="block text-sm font-semibold text-[#003B73]">File Subject /
                                    Title</label>
                                <input type="text" name="title" id="title" x-model="fileTitle"
                                    class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                                <p class="mt-1 text-xs text-gray-400">The primary descriptive title written on the
                                    physical folder.</p>
                            </div>

                            {{-- File Jacket --}}
                            <div>
                                <label for="file_jacket_id" class="block text-sm font-semibold text-[#003B73]">File
                                    Jacket</label>
                                <div class="flex gap-2 mt-1">
                                    <div class="flex-1 w-full min-w-0">
                                        <x-custom-select>
                                            <select name="file_jacket_id" id="file_jacket_id"
                                                class="block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                                                <option value="">Select Jacket...</option>
                                                @foreach ($jackets as $jacket)
                                                    <option value="{{ $jacket->id }}"
                                                        {{ (old('file_jacket_id') ?? $preselectedJacketId) == $jacket->id ? 'selected' : '' }}>
                                                        {{ $jacket->jacket_code }} — {{ $jacket->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </x-custom-select>
                                    </div>
                                    <button type="button" @click="showJacketModal = true"
                                        class="flex-shrink-0 inline-flex items-center px-3 py-2 bg-white border border-[#003B73] text-[#003B73] text-xs font-semibold rounded-sm hover:bg-[#003B73] hover:text-white transition">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        New
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Select the case folder this document belongs to.
                                </p>
                            </div>

                            {{-- Department & Station (Automated Origin Lock) --}}
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-[#003B73]">Originating
                                        Station</label>
                                    <input type="text" disabled value="{{ Auth::user()->department->station->name ?? 'Unknown' }}"
                                        class="mt-1 block w-full rounded-sm border-gray-300 bg-gray-50 text-gray-500 shadow-sm cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-400">Locked to your assigned region.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-[#003B73]">Originating
                                        Department</label>
                                    <input type="text" disabled value="{{ Auth::user()->department->name ?? 'Unknown' }}"
                                        class="mt-1 block w-full rounded-sm border-gray-300 bg-gray-50 text-gray-500 shadow-sm cursor-not-allowed">
                                    <input type="hidden" name="department_id" value="{{ Auth::user()->department_id }}">
                                    <p class="mt-1 text-xs text-gray-400">Locked to your current department.</p>
                                </div>
                            </div>


                            {{-- Priority + Confidentiality --}}
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label for="priority" class="block text-sm font-semibold text-[#003B73]">Priority
                                        Level</label>
                                    <x-custom-select>
                                        <select id="priority" name="priority_level"
                                            class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] shadow-sm">
                                            <option value="1">1 - Routine</option>
                                            <option value="2">2 - Urgent</option>
                                            <option value="3">3 - Critical</option>
                                        </select>
                                    </x-custom-select>
                                </div>
                                <div>
                                    <label for="confidentiality"
                                        class="block text-sm font-semibold text-[#003B73]">Confidentiality
                                        Clearance</label>
                                    <x-custom-select>
                                        <select id="confidentiality" name="confidentiality_level"
                                            class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] shadow-sm">
                                            <option value="1">Level 1 (General)</option>
                                            <option value="2">Level 2 (Restricted)</option>
                                            <option value="3">Level 3 (Secret)</option>
                                        </select>
                                    </x-custom-select>
                                </div>
                            </div>

                            @if (config('digital_module.enabled'))
                                <div class="pt-6 border-t border-gray-100">
                                    <label for="digital_document"
                                        class="block text-sm font-semibold text-[#003B73]">Optional
                                        Digital Attachment</label>
                                    <input type="file" name="digital_document" id="digital_document"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                        class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#003B73] file:text-white hover:file:bg-blue-800 transition shadow-sm border border-gray-200 rounded-md bg-white">
                                    <p class="mt-1 text-xs text-gray-400">Attach an initial scan or PDF if available.
                                        Max limit
                                        {{ config('digital_module.max_upload_size') / 1024 }}MB.</p>
                                </div>
                            @endif

                            {{-- Initial Dispatch (Optional) --}}
                            <div class="pt-6 border-t border-gray-100">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-[#003B73]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    <h4 class="font-bold text-[#003B73] text-sm uppercase tracking-wider">Initial
                                        Dispatch (Optional)</h4>
                                </div>
                                <p class="text-xs text-gray-500 mb-4">Dispatch this document immediately after creation.
                                    Leave blank to create without dispatching.</p>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="dispatch_station_id"
                                            class="block text-sm font-semibold text-[#003B73]">Destination
                                            Station</label>
                                        <x-custom-select>
                                            <select id="dispatch_station_id" name="dispatch_station_id"
                                                x-model="dispatchStationId" @change="resetDispatchDept()"
                                                class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                                                <option value="">— None —</option>
                                                @foreach ($stations as $station)
                                                    <option value="{{ $station->id }}">{{ $station->name }}</option>
                                                @endforeach
                                            </select>
                                        </x-custom-select>
                                    </div>
                                    <div>
                                        <label for="dispatch_department_id"
                                            class="block text-sm font-semibold text-[#003B73]">Destination
                                            Department</label>
                                        <x-custom-select>
                                            <select id="dispatch_department_id" name="dispatch_department_id"
                                                x-model="dispatchDeptId" @change="loadUsers()" :disabled="!dispatchStationId"
                                                class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition disabled:bg-gray-100 disabled:text-gray-400">
                                                <option value="">— None —</option>
                                                <template x-for="dept in departments.filter(d => d.station_id == dispatchStationId)" :key="dept.id">
                                                    <option :value="dept.id" x-text="dept.name" :selected="dept.id == {{ old('dispatch_department_id', 'null') }}"></option>
                                                </template>
                                            </select>
                                        </x-custom-select>
                                    </div>
                                    <div>
                                        <label for="dispatch_user_id"
                                            class="block text-sm font-semibold text-[#003B73]">Specific Officer
                                            (Optional)</label>
                                        <x-custom-select>
                                            <select id="dispatch_user_id" name="dispatch_user_id"
                                                x-model="dispatchUserId" :disabled="!dispatchDeptId"
                                                class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition disabled:bg-gray-100 disabled:text-gray-400">
                                                <option value="">— Department Inbox —</option>
                                                <template x-for="user in deptUsers" :key="user.id">
                                                    <option :value="user.id"
                                                        x-text="user.name + (user.staff_id ? ' (' + user.staff_id + ')' : '')">
                                                    </option>
                                                </template>
                                            </select>
                                        </x-custom-select>
                                        <p class="mt-1 text-xs text-gray-400">If left empty, the document goes to the
                                            department inbox for any officer to claim.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-6 mt-6 border-t border-gray-100 flex justify-end">
                                <button type="submit"
                                    class="px-6 py-3 bg-[#003B73] border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-[#003B73] focus:ring-offset-2 transition ease-in-out duration-150 flex items-center shadow-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    GENERATE FILE RECORD
                                </button>
                            </div>
                        </form>

                        {{-- Inline Jacket Creation Modal --}}
                        <div x-show="showJacketModal" x-cloak
                            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
                            <div class="relative bg-white rounded-lg shadow-xl max-w-md mx-auto w-full"
                                @click.away="showJacketModal = false">
                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div
                                            class="h-10 w-10 rounded-full bg-[#003B73]/10 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-[#003B73]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
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
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jacket Title
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="jacketTitle"
                                                placeholder="e.g. Budget Review 2026"
                                                class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Description
                                                <span class="text-gray-400 font-normal">(Optional)</span></label>
                                            <textarea x-model="jacketDesc" rows="2" placeholder="Brief description of the case"
                                                class="w-full rounded-sm border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm"></textarea>
                                        </div>
                                        <p class="text-xs text-red-600" x-show="jacketError" x-text="jacketError">
                                        </p>
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

                    <!-- Right Side Context Panel -->
                    <div class="lg:col-span-1 bg-gray-50/50 p-6 rounded-md border border-gray-100 space-y-6">
                        <div>
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 text-[#003B73] mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h4 class="font-bold text-[#003B73] text-sm uppercase tracking-wider">Filing Guidelines
                                </h4>
                            </div>
                            <ul class="text-sm text-gray-600 space-y-2 list-disc pl-5">
                                <li>Ensure the <strong>File Subject</strong> accurately matches the printed label on the
                                    physical folder.</li>
                                <li>Selecting <strong>Urgent</strong> or <strong>Critical</strong> priority
                                    automatically flags the transit ledger for expedited handling.</li>
                                <li>Once generated, a unique <strong>Reference Number</strong> will be assigned and the
                                    file will be traced to your desk.</li>
                            </ul>
                        </div>

                        @if (config('digital_module.enabled'))
                            <div class="pt-5 border-t border-gray-200">
                                <div class="flex items-center mb-3">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <h4 class="font-bold text-green-700 text-sm uppercase tracking-wider">Digital
                                        Attachment</h4>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    The Digital Archive mechanism is active. If you are holding the physical documents
                                    now, you may upload an initial high-quality PDF scan. This will be securely hashed
                                    and attached irrevocably to the Genesis movement ledger.
                                </p>
                            </div>
                        @endif

                        <div class="pt-5 border-t border-gray-200">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                <h4 class="font-bold text-blue-700 text-sm uppercase tracking-wider">Initial Dispatch
                                </h4>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                You can optionally dispatch this document immediately after creation.
                                If you select a <strong>Destination Department</strong>, the file will be sent there
                                upon generation.
                                Leave the officer field empty to send to the department inbox.
                            </p>
                        </div>

                        <div class="pt-5 border-t border-gray-200">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                <h4 class="font-bold text-red-700 text-sm uppercase tracking-wider">Security Notice
                                </h4>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Avoid including extremely sensitive data in the Subject line (e.g. monetary values,
                                precise personal identities) if the Confidentiality Clearance is Restricted or higher,
                                as titles are exposed in broad transit logs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function generateForm() {
            return {
                fileTitle: '{{ old('title', '') }}',
                showJacketModal: false,
                jacketTitle: '',
                jacketDesc: '',
                jacketError: '',
                jacketLoading: false,
                departments: {{ $departments->toJson() }},
                dispatchStationId: '{{ old('dispatch_station_id', '') }}',
                dispatchDeptId: '{{ old('dispatch_department_id', '') }}',
                dispatchUserId: '{{ old('dispatch_user_id', '') }}',
                deptUsers: [],

                handleReferenceSelection(event) {
                    const selectedValue = event.target.value;
                    if (selectedValue) {
                        // If there is no title or it doesn't start with 'RE:', prefix it.
                        if (!this.fileTitle) {
                            this.fileTitle = 'RE: ';
                        } else if (!this.fileTitle.toUpperCase().startsWith('RE:')) {
                            this.fileTitle = 'RE: ' + this.fileTitle;
                        }
                    }
                },

                async loadUsers() {
                    this.dispatchUserId = '';
                    this.deptUsers = [];
                    if (!this.dispatchDeptId) return;

                    try {
                        const res = await fetch(`/api/departments/${this.dispatchDeptId}/users`);
                        this.deptUsers = await res.json();
                    } catch (e) {
                        console.error('Failed to load users:', e);
                    }
                },

                resetDispatchDept() {
                    this.dispatchDeptId = '';
                    this.dispatchUserId = '';
                    this.deptUsers = [];
                },

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
                },

                init() {
                    if (this.dispatchDeptId) {
                        this.loadUsers();
                    }
                }
            };
        }
    </script>
</x-app-layout>
