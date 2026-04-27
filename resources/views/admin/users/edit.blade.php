<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ isset($user) ? 'Manage User: ' . $user->name : 'Onboard New User' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6" x-data="userForm()">
            <div class="bg-white p-8 shadow-sm border border-gray-200">
                <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
                    method="POST" class="space-y-6">
                    @csrf
                    @if (isset($user))
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Left Column: Personal Detals -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Identity</h3>

                            <div>
                                <label for="system_identifier" class="block text-sm font-semibold text-primary">System
                                    Identifier (e.g. NAMA/CR/001)</label>
                                <input type="text" name="system_identifier" id="system_identifier"
                                    value="{{ old('system_identifier', $user->system_identifier ?? '') }}" required
                                    placeholder="Unique ID..."
                                    class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary focus:ring focus:ring-primary font-mono shadow-sm transition">
                                @error('system_identifier')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-semibold text-primary">Full
                                    Name</label>
                                <input type="text" name="name" id="name"
                                    value="{{ old('name', $user->name ?? '') }}" required
                                    class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary focus:ring focus:ring-primary shadow-sm">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-primary">Email
                                    Address</label>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email', $user->email ?? '') }}" required
                                    class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary focus:ring focus:ring-primary shadow-sm">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-data="{ showPhone: false }">
                                <label for="phone_number" class="block text-sm font-semibold text-primary">WhatsApp/Phone Number</label>
                                <div class="relative mt-1">
                                    <input :type="showPhone ? 'text' : 'password'" name="phone_number" id="phone_number"
                                        value="{{ old('phone_number', $user->phone_number ?? '') }}" placeholder="+234..."
                                        class="block w-full rounded-sm border-gray-300 focus:border-primary focus:ring focus:ring-primary shadow-sm font-mono tracking-wider pr-10">
                                    <button type="button" @click="showPhone = !showPhone" class="absolute inset-y-0 right-0 px-3 flex items-center text-sm leading-5 text-gray-500 hover:text-gray-700">
                                        <span x-show="!showPhone">
                                            <!-- Heroicon name: string/eye -->
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </span>
                                        <span x-show="showPhone" x-cloak>
                                            <!-- Heroicon name: string/eye-off -->
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Used by the SMS Escalation Engine. Format: +[CountryCode][Number]</p>
                                @error('phone_number')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password block -->
                            <div>
                                <label for="password"
                                    class="block text-sm font-semibold text-primary">{{ isset($user) ? 'New Password (Leave blank to keep current)' : 'Initial Password' }}</label>
                                <input type="password" name="password" id="password"
                                    {{ isset($user) ? '' : 'required' }}
                                    class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary focus:ring focus:ring-primary shadow-sm">
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation"
                                    class="block text-sm font-semibold text-primary">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    {{ isset($user) ? '' : 'required' }}
                                    class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary focus:ring focus:ring-primary shadow-sm">
                            </div>
                        </div>

                        <!-- Right Column: Security & Routing -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Clearance & Routing</h3>

                            <div>
                                <label for="station_id"
                                    class="block text-sm font-semibold text-primary">@term('station', 'Geographic Station')</label>
                                <x-custom-select>
                                    <select id="station_id" name="station_id" x-model="stationId" @change="departmentId = ''"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary shadow-sm transition">
                                        <option value="">Select Station...</option>
                                        @foreach ($stations as $station)
                                            <option value="{{ $station->id }}"
                                                {{ (old('station_id', isset($user) ? ($user->department->station_id ?? '') : '')) == $station->id ? 'selected' : '' }}>
                                                {{ $station->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </x-custom-select>
                            </div>

                            <div>
                                <label for="department_id"
                                    class="block text-sm font-semibold text-primary">@term('department', 'Department')</label>
                                <x-custom-select>
                                    <select id="department_id" name="department_id" x-model="departmentId" :disabled="!stationId"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary shadow-sm transition disabled:bg-gray-100 disabled:text-gray-400"
                                        required>
                                        <option value="">Select @term('department', 'Department')...</option>
                                        <template x-for="dept in @term('departments', 'departments').filter(d => d.station_id == stationId)" :key="dept.id">
                                            <option :value="dept.id" x-text="dept.name" :selected="dept.id == initialDepartmentId"></option>
                                        </template>
                                    </select>
                                </x-custom-select>
                                @error('department_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-semibold text-primary">System Role
                                    (RBAC)</label>
                                <x-custom-select>
                                    <select id="role" name="role"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary shadow-sm transition"
                                        required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ old('role', isset($user) ? $user->roles->first()->name ?? '' : '') == $role->name ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', Str::title($role->name)) }}</option>
                                        @endforeach
                                    </select>
                                </x-custom-select>
                                @error('role')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="clearance_level" class="block text-sm font-semibold text-primary">Data
                                    Clearance Level</label>
                                <x-custom-select>
                                    <select id="clearance_level" name="clearance_level"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary shadow-sm transition"
                                        required>
                                        <option value="1"
                                            {{ old('clearance_level', $user->clearance_level ?? 1) == 1 ? 'selected' : '' }}>
                                            Level 1 (General)</option>
                                        <option value="2"
                                            {{ old('clearance_level', $user->clearance_level ?? 1) == 2 ? 'selected' : '' }}>
                                            Level 2 (Restricted)</option>
                                        <option value="3"
                                            {{ old('clearance_level', $user->clearance_level ?? 1) == 3 ? 'selected' : '' }}>
                                            Level 3 (Secret)</option>
                                        <option value="4"
                                            {{ old('clearance_level', $user->clearance_level ?? 1) == 4 ? 'selected' : '' }}>
                                            Level 4 (Top Secret / Director)</option>
                                    </select>
                                </x-custom-select>
                                <p class="mt-1 text-xs text-gray-500">Dictates which files this user can legally access
                                    or hold.</p>
                                @error('clearance_level')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="is_active" class="block text-sm font-semibold text-primary">Account
                                    Status</label>
                                <x-custom-select>
                                    <select id="is_active" name="is_active"
                                        class="mt-1 block w-full rounded-sm border-gray-300 focus:border-primary shadow-sm transition">
                                        <option value="1"
                                            {{ old('is_active', $user->is_active ?? 1) == 1 ? 'selected' : '' }}>Active -
                                            Enabled</option>
                                        <option value="0"
                                            {{ old('is_active', $user->is_active ?? 1) == 0 ? 'selected' : '' }}>
                                            Suspended - Blocked</option>
                                    </select>
                                </x-custom-select>
                                @error('is_active')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t border-gray-100 flex justify-end space-x-3">
                        <a href="{{ route('admin.users.index') }}"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900 font-semibold text-sm mt-1">Cancel</a>
                        <button type="submit"
                            class="px-6 py-2 bg-primary text-white rounded-sm font-bold text-sm uppercase tracking-wide hover:bg-blue-800 transition shadow-md">
                            {{ isset($user) ? 'Update User' : 'Onboard User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userForm', () => ({
                @term('departments', 'departments'): @json($departments),
                stationId: '{{ old('station_id', isset($user) ? ($user->department->station_id ?? '') : '') }}',
                departmentId: '{{ old('department_id', $user->department_id ?? '') }}',
                initialDepartmentId: '{{ old('department_id', $user->department_id ?? '') }}',
                
                init() {
                    if (this.departmentId && !this.stationId) {
                        const dept = this.@term('departments', 'departments').find(d => d.id == this.departmentId);
                        if (dept) this.stationId = dept.station_id;
                    }
                }
            }));
        });
    </script>
</x-app-layout>
