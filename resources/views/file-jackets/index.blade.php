<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="font-semibold text-xl text-[#003B73] leading-tight">{{ __('File Jackets') }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ Auth::user()->department->name ?? '' }} — Document Registry
                </p>
            </div>
            <a href="{{ route('file-jackets.create') }}"
                class="flex-shrink-0 inline-flex items-center px-5 py-2.5 bg-[#003B73] text-white text-sm font-semibold rounded shadow-md hover:bg-[#00294d] hover:shadow-lg transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                + Create File Jacket
            </a>
        </div>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 h-full flex-1">

            @if (session('success'))
                <div
                    class="p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Stats Bar --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Jackets</p>
                    <div class="flex items-end justify-between mt-2">
                        <p class="text-3xl font-bold text-amber-600">{{ $stats['total'] }}</p>
                        <svg class="w-6 h-6 text-amber-300 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                        </svg>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active</p>
                    <div class="flex items-end justify-between mt-2">
                        <p class="text-3xl font-bold text-amber-600">{{ $stats['active'] }}</p>
                        <svg class="w-6 h-6 text-amber-300 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Closed</p>
                    <div class="flex items-end justify-between mt-2">
                        <p class="text-3xl font-bold text-amber-600">{{ $stats['closed'] }}</p>
                        <svg class="w-6 h-6 text-amber-300 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived</p>
                    <div class="flex items-end justify-between mt-2">
                        <p class="text-3xl font-bold text-amber-600">{{ $stats['archived'] }}</p>
                        <svg class="w-6 h-6 text-amber-300 mb-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Search / Filter --}}
            <div class="bg-white p-4 shadow-sm border border-gray-200 rounded-xl">
                <form method="GET" action="{{ route('file-jackets.index') }}" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-[#003B73] uppercase mb-1">Search</label>
                        <div class="relative">
                            <svg class="w-4 h-4 text-[#003B73] absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search by code or title..."
                                class="w-full pl-10 rounded-lg border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">
                        </div>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs font-bold text-[#003B73] uppercase mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed
                            </option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived
                            </option>
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Year</label>
                        <input type="number" name="year" value="{{ request('year') }}"
                            placeholder="{{ date('Y') }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm">
                    </div>
                    <button type="submit"
                        class="flex-shrink-0 px-5 py-2.5 bg-[#003B73] text-white text-sm font-semibold rounded-lg hover:bg-[#00294d] transition shadow-sm">Filter</button>
                    @if (request()->hasAny(['search', 'status', 'year']))
                        <a href="{{ route('file-jackets.index') }}"
                            class="text-sm text-gray-500 hover:text-gray-700 underline">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Jackets Table --}}
            @if ($jackets->count())
                <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Jacket</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Title</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Documents</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Created</th>
                                <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($jackets as $jacket)
                                <tr class="hover:bg-blue-50/40 transition group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0
                                                {{ $jacket->status === 'active' ? 'bg-[#003B73]/10' : '' }}
                                                {{ $jacket->status === 'closed' ? 'bg-gray-100' : '' }}
                                                {{ $jacket->status === 'archived' ? 'bg-amber-50' : '' }}">
                                                <svg class="w-5 h-5
                                                    {{ $jacket->status === 'active' ? 'text-[#003B73]' : '' }}
                                                    {{ $jacket->status === 'closed' ? 'text-gray-400' : '' }}
                                                    {{ $jacket->status === 'archived' ? 'text-amber-500' : '' }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                </svg>
                                            </div>
                                            <span
                                                class="font-mono text-xs font-bold text-[#003B73]">{{ $jacket->jacket_code }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 font-medium line-clamp-1 max-w-xs">
                                            {{ $jacket->title }}</div>
                                        <p class="text-xs text-gray-400 mt-0.5">by
                                            {{ $jacket->creator->name ?? 'Unknown' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 font-semibold
                                            {{ $jacket->current_files_count > 0 ? 'text-[#003B73]' : 'text-gray-400' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ $jacket->current_files_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            {{ $jacket->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $jacket->status === 'closed' ? 'bg-gray-100 text-gray-600' : '' }}
                                            {{ $jacket->status === 'archived' ? 'bg-amber-100 text-amber-700' : '' }}">
                                            @if ($jacket->status === 'active')
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                            @endif
                                            {{ ucfirst($jacket->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $jacket->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('file-jackets.show', $jacket->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-[#003B73] bg-blue-50 rounded-lg hover:bg-[#003B73] hover:text-white transition-all">
                                            Open
                                            <svg class="w-3.5 h-3.5 ml-1 opacity-0 group-hover:opacity-100 transition"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($jackets->hasPages())
                    <div class="mt-4">{{ $jackets->appends(request()->query())->links() }}</div>
                @endif
            @else
                <div class="bg-white p-16 shadow-sm border border-gray-200 rounded-xl text-center">
                    <div class="h-16 w-16 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-[#003B73]" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">No file jackets found</h3>
                    <p class="mt-1.5 text-sm text-gray-500 max-w-md mx-auto">
                        @if (request()->hasAny(['search', 'status', 'year']))
                            No jackets match your filters. Try adjusting your search criteria.
                        @else
                            Create your first file jacket to start organizing documents in your department.
                        @endif
                    </p>
                    @if (!request()->hasAny(['search', 'status', 'year']))
                        <a href="{{ route('file-jackets.create') }}"
                            class="mt-5 inline-flex items-center px-5 py-2.5 bg-[#003B73] text-white text-sm font-semibold rounded-lg shadow hover:bg-[#00294d] transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create File Jacket
                        </a>
                    @else
                        <a href="{{ route('file-jackets.index') }}"
                            class="mt-5 inline-flex items-center text-sm text-[#003B73] font-semibold hover:underline">
                            Clear Filters →
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
