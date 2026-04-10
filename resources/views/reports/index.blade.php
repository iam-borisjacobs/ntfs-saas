<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-6">
            <h2 class="font-semibold text-xl text-brand-dark leading-tight">
                {{ __('Advanced Reporting & Search') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Quick Search Form -->
            <div class="bg-white p-6 shadow-sm border border-gray-200" x-data="{ advancedOpen: false }">
                <form action="{{ route('reports.index') }}" method="GET">
                    <div class="flex space-x-4">
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="Subject / Title Partial Match or Ref No"
                            class="flex-1 border-gray-300 focus:border-brand-dark focus:ring-brand-dark rounded-sm shadow-sm p-3">
                        <button type="submit"
                            class="px-6 py-3 bg-brand-dark text-white font-bold rounded-sm hover:bg-blue-800 transition">SEARCH</button>
                    </div>

                    <div class="mt-4 flex justify-between items-center text-sm border-t border-gray-100 pt-4">
                        <button type="button" @click="advancedOpen = !advancedOpen"
                            class="text-brand-dark font-semibold hover:underline flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                </path>
                            </svg>
                            Toggle Advanced Filters
                        </button>
                        @if (count(request()->except('page')) > 0)
                            <a href="{{ route('reports.index') }}"
                                class="text-gray-500 hover:text-red-600 underline">Clear Filters</a>
                        @endif
                    </div>

                    <!-- Advanced Filters Panel -->
                    <div x-show="advancedOpen" style="display: none;"
                        class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded border border-gray-200">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Status</label>
                            <x-custom-select>
                                <select name="status_id"
                                    class="w-full border-gray-300 rounded focus:border-brand-dark focus:ring focus:ring-brand-dark focus:ring-opacity-50 text-sm">
                                    <option value="">All Statuses</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}"
                                            {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-custom-select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Dept Match</label>
                            <x-custom-select>
                                <select name="department_match_type"
                                    class="w-full border-gray-300 rounded focus:border-brand-dark focus:ring-brand-dark focus:border-opacity-50 text-sm">
                                    <option value="current" {{ request('department_match_type') === 'current' ? 'selected' : '' }}>Presently Here</option>
                                    <option value="historical" {{ request('department_match_type') === 'historical' ? 'selected' : '' }}>Ever Passed Through</option>
                                </select>
                            </x-custom-select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">@term('department', 'Department')</label>
                            <x-custom-select>
                                <select name="department_id"
                                    class="w-full border-gray-300 rounded focus:border-brand-dark focus:ring-brand-dark focus:border-opacity-50 text-sm">
                                    <option value="">All @term('departments', 'Departments')</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </x-custom-select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Priority</label>
                            <x-custom-select>
                                <select name="priority_level"
                                    class="w-full border-gray-300 rounded focus:border-brand-dark text-sm">
                                    <option value="">Any</option>
                                    <option value="0" {{ request('priority_level') === '0' ? 'selected' : '' }}>
                                        Routine</option>
                                    <option value="1" {{ request('priority_level') === '1' ? 'selected' : '' }}>Urgent
                                    </option>
                                    <option value="2" {{ request('priority_level') === '2' ? 'selected' : '' }}>
                                        Critical</option>
                                </select>
                            </x-custom-select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Has Pending
                                Actions</label>
                            <x-custom-select>
                                <select name="has_pending"
                                    class="w-full border-gray-300 rounded focus:border-brand-dark text-sm">
                                    <option value="">All</option>
                                    <option value="1" {{ request('has_pending') == '1' ? 'selected' : '' }}>Yes
                                    </option>
                                </select>
                             </x-custom-select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 rounded focus:border-brand-dark focus:ring focus:ring-brand-dark focus:ring-opacity-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border-gray-300 rounded focus:border-brand-dark focus:ring focus:ring-brand-dark focus:ring-opacity-50 text-sm">
                        </div>
                        <div class="flex justify-end items-end h-full">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 w-full text-white font-bold tracking-wider uppercase rounded hover:bg-black transition text-sm">Apply
                                Filters</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Header & Export -->
            <div class="flex justify-between items-end mb-4">
                <h3 class="text-lg font-bold text-gray-700">Search Results</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
                        class="px-4 py-2 border border-primary text-primary hover:bg-primary hover:text-white transition rounded-sm text-sm font-semibold tracking-wide">
                        EXPORT CSV
                    </a>
                    <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                        class="px-4 py-2 bg-primary text-white hover:bg-blue-800 transition rounded-sm text-sm font-semibold tracking-wide shadow-sm">
                        EXPORT PDF
                    </a>
                </div>
            </div>

            <!-- Results Table -->
            <div class="bg-white shadow-sm border border-gray-200 overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    File Ref</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    Subject / Title</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    Location</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    Timestamp</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($files as $file)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                                        {{ $file->file_reference_number }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 font-medium line-clamp-2">{{ $file->title }}</div>
                                        @if ($file->priority_level > 0)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $file->priority_level == 2 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $file->priority_level == 2 ? 'Critical' : 'Urgent' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        {{ $file->currentDepartment->name ?? 'Unknown' }}<br>
                                        <span
                                            class="text-xs text-gray-400 font-mono">{{ $file->currentOwner->system_identifier ?? 'Unassigned' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColor = match ($file->status->name ?? '') {
                                                'RECEIVED' => 'bg-green-100 text-green-800',
                                                'IN_TRANSIT' => 'bg-yellow-100 text-yellow-800',
                                                'REJECTED' => 'bg-red-100 text-red-800',
                                                'CLOSED', 'ARCHIVED' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-blue-100 text-blue-800',
                                            };
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                            {{ $file->status->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs text-right">
                                        {{ $file->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                        <a href="{{ route('files.show', $file->uuid) }}"
                                            class="text-brand-dark hover:text-blue-900 underline">Timeline</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No records found</h3>
                                        <p class="mt-1 text-sm text-gray-500">There are no files matching your
                                            clearance or
                                            filter criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $files->links() }}
                    </div>
                </div>

            </div>
        </div>
</x-app-layout>
