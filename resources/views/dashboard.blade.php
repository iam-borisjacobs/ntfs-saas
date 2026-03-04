<x-app-layout>
    <x-slot name="header">
        Dashboard Overview
    </x-slot>

    <!-- Top Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <!-- Outgoing Card -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center text-center">
            <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Outgoing</h3>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $metrics['outgoing'] }}</p>
        </div>

        <!-- Incoming Card -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center text-center">
            <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Incoming</h3>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $metrics['incoming'] }}</p>
        </div>

        <!-- Pending Card -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center text-center">
            <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending</h3>
            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $metrics['pending'] }}</p>
        </div>

        <!-- Notifications Card -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center text-center">
            <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                    </path>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Notification</h3>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $metrics['notifications'] }}</p>
        </div>

    </div>

    <!-- Main Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Active Queues and Search -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Quick Search Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ open: false }">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-blue-50/30">
                    <h3 class="text-lg font-bold text-[#003B73]">Search & Reporting Engine</h3>
                    <a href="{{ route('reports.index') }}"
                        class="text-xs font-semibold text-[#003B73] hover:underline bg-white px-3 py-1 border border-blue-200 rounded-full shadow-sm">Advanced
                        Portal &rarr;</a>
                </div>
                <div class="p-6">
                    <form action="{{ route('reports.index') }}" method="GET">
                        <div class="flex space-x-4">
                            <input type="text" name="q" placeholder="Search by File Ref No, Title, or Topic..."
                                class="flex-1 border-gray-300 focus:border-[#003B73] focus:ring-[#003B73] rounded-md shadow-sm p-3 text-sm">
                            <button type="submit"
                                class="px-6 py-3 bg-[#003B73] text-white font-bold rounded-md hover:bg-blue-800 transition text-sm flex items-center shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                SEARCH
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Action Required Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Action Required Queues</h3>
                    <span
                        class="bg-gray-100 text-gray-600 py-1 px-3 rounded-full text-xs font-semibold">{{ $activeFiles->total() }}</span>
                </div>

                <div class="p-0 overflow-x-auto">
                    @if ($activeFiles->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            You have no active files. System operating within SLA.
                        </div>
                    @else
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-blue-50/50 text-gray-500 text-sm uppercase tracking-wide">
                                    <th class="px-6 py-3 font-medium">Subject</th>
                                    <th class="px-6 py-3 font-medium">Ref ID</th>
                                    <th class="px-6 py-3 font-medium">Status</th>
                                    <th class="px-6 py-3 font-medium">Priority</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($activeFiles as $file)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-gray-800 font-medium">
                                            {{ Str::limit($file->title, 40) }}</td>
                                        <td class="px-6 py-4 text-gray-500 font-mono text-sm">
                                            {{ $file->file_reference_number }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $file->status->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($file->priority_level >= 3)
                                                <span class="text-red-600 font-bold text-sm">High</span>
                                            @else
                                                <span class="text-gray-500 text-sm">Normal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination Logic Snippet -->
                        <div class="p-4 border-t border-gray-100 flex justify-center">
                            {{ $activeFiles->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Generic Pending Queue (Secondary) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">General Pending</h3>
                </div>
                <!-- Simplified display for visual balance against mockup -->
                <div class="px-6 py-4 text-sm text-gray-500">
                    See Action Required queue for primary routing.
                </div>
                <div class="p-4 border-t border-gray-100 text-center">
                    <button
                        class="bg-[#003B73] hover:bg-blue-800 text-white px-6 py-2 rounded text-sm font-medium transition">More</button>
                </div>
            </div>

        </div>

        <!-- Right Column: Notifications & Calendar -->
        <div class="space-y-8">

            <!-- Reminder Calendar Mock -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Reminder Calendar</h3>
                </div>

                <!-- Mockup Calendar Grid -->
                <div class="text-sm">
                    <div class="flex justify-between items-center mb-4">
                        <button class="text-gray-400 hover:text-gray-800">&lt;</button>
                        <span class="font-medium">{{ \Carbon\Carbon::now()->format('F Y') }}</span>
                        <button class="text-gray-400 hover:text-gray-800">&gt;</button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center mb-2 font-medium text-gray-500 text-xs">
                        <div>Su</div>
                        <div>Mo</div>
                        <div>Tu</div>
                        <div>We</div>
                        <div>Th</div>
                        <div>Fr</div>
                        <div>Sa</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-gray-700">
                        @for ($i = 1; $i <= 31; $i++)
                            @if ($i == \Carbon\Carbon::now()->day)
                                <div
                                    class="w-8 h-8 flex items-center justify-center bg-[#003B73] text-white rounded-full mx-auto">
                                    {{ $i }}</div>
                            @else
                                <div
                                    class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded-full mx-auto cursor-pointer">
                                    {{ $i }}</div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

            <!-- General Notifications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">General Notification</h3>
                </div>
                <div class="p-4 space-y-3 max-h-96 overflow-y-auto">

                    @if ($escalatedMovements->isEmpty())
                        <div class="p-3 text-sm text-gray-500">No active escalations.</div>
                    @else
                        @foreach ($escalatedMovements as $escalation)
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <h4 class="text-red-800 font-bold">System Alert - Escalation!</h4>
                                        <p class="text-red-700 mt-1">File Ref:
                                            {{ $escalation->file->file_reference_number }} has breached movement SLA.
                                            Please review.</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <!-- Dummy Info Alert -->
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 text-sm">
                                <h4 class="text-gray-800 font-bold">System Update</h4>
                                <p class="text-gray-500 mt-1">NAMA workflow engine v1.0 is currently online.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="mt-8 text-center text-xs text-gray-400">
        All actions are logged and subject to review. ({{ \Carbon\Carbon::now()->format('Y') }})
    </div>
</x-app-layout>
