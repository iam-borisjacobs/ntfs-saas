<x-app-layout>
    <x-slot name="header">
        Dashboard Overview
    </x-slot>

    <!-- Top Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

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

        <!-- @term('department', 'Department') Inbox Card -->
        <a href="{{ route('queues.department-inbox') }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center text-center hover:shadow-md transition">
            <div class="h-12 w-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                    </path>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Dept. Inbox</h3>
            <p
                class="text-3xl font-bold {{ ($metrics['deptInbox'] ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">
                {{ $metrics['deptInbox'] ?? 0 }}
            </p>
        </a>

    </div>

    <!-- Main Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Active Queues and Search -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Quick Search Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ open: false }">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-blue-50/30">
                    <h3 class="text-lg font-bold text-primary">Search & Reporting Engine</h3>
                    <a href="{{ route('reports.index') }}"
                        class="text-xs font-semibold text-primary hover:underline bg-white px-3 py-1 border border-blue-200 rounded-full shadow-sm">Advanced
                        Portal &rarr;</a>
                </div>
                <div class="p-6">
                    <form action="{{ route('reports.index') }}" method="GET">
                        <div class="flex space-x-4">
                            <input type="text" name="q" placeholder="Search by File Ref No, Title, or Topic..."
                                class="flex-1 border-gray-300 focus:border-primary focus:ring-primary rounded-md shadow-sm p-3 text-sm">
                            <button type="submit"
                                class="px-6 py-3 bg-primary text-white font-bold rounded-md hover:bg-blue-800 transition text-sm flex items-center shadow-sm">
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

            <!-- General Pending Queue (Live) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">General Pending</h3>
                    @if ($metrics['pending'] > 0)
                        <span class="bg-amber-100 text-amber-700 py-1 px-3 rounded-full text-xs font-bold">{{ $metrics['pending'] }} file{{ $metrics['pending'] > 1 ? 's' : '' }}</span>
                    @endif
                </div>

                @if ($pendingFiles->isEmpty())
                    <div class="px-6 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-gray-500 font-medium">Your desk is clear.</p>
                        <p class="text-xs text-gray-400 mt-1">No files pending action in your custody.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($pendingFiles as $pf)
                            <a href="{{ route('files.show', $pf->uuid) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition group">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate group-hover:text-primary transition">{{ Str::limit($pf->title, 35) }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $pf->file_reference_number }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $pf->status->name ?? 'N/A' }}</span>
                                    <p class="text-xs text-gray-400 mt-1">{{ $pf->updated_at->diffForHumans() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if ($metrics['pending'] > 5)
                        <div class="p-4 border-t border-gray-100 text-center">
                            <a href="{{ route('queues.pending') }}" class="bg-primary hover:bg-blue-800 text-white px-6 py-2 rounded text-sm font-medium transition inline-block">View All {{ $metrics['pending'] }} Files</a>
                        </div>
                    @endif
                @endif
            </div>

        </div>

        <!-- Right Column: Notifications & Calendar -->
        <div class="space-y-8">

            <!-- Reminder Calendar Interactive -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="calendarApp()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Reminder Calendar</h3>
                </div>

                <!-- Calendar Grid -->
                <div class="text-sm select-none relative">
                    <div class="flex justify-between items-center mb-4">
                        <button type="button" @click="prevMonth" class="text-gray-400 hover:text-gray-800 p-1">&lt;</button>
                        <span class="font-bold flex-1 text-center" x-text="monthName + ' ' + year"></span>
                        <button type="button" @click="nextMonth" class="text-gray-400 hover:text-gray-800 p-1">&gt;</button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center mb-2 font-medium text-gray-400 text-xs">
                        <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-gray-700">
                        <template x-for="blank in blankDays">
                            <div class="w-8 h-8 mx-auto"></div>
                        </template>
                        <template x-for="day in daysInMonth">
                            <div class="relative w-8 h-8 flex items-center justify-center rounded-full mx-auto transition-colors"
                                 :class="{
                                     'bg-primary text-white font-bold shadow-md cursor-pointer': isToday(day) && isValidDate(day),
                                     'hover:bg-gray-100 cursor-pointer text-gray-700': !isToday(day) && isValidDate(day),
                                     'opacity-30 cursor-not-allowed text-gray-400': !isValidDate(day)
                                 }"
                                 @click="isValidDate(day) ? openModal(day) : null">
                                <span x-text="day"></span>
                                <!-- Blinking Dot Indicator if reminder exists -->
                                <template x-if="hasReminder(day)">
                                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-600 rounded-full animate-pulse border border-white"></span>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Loader overlay -->
                    <div x-show="loading" class="absolute inset-0 bg-white/50 flex items-center justify-center z-10">
                        <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>

                <!-- Reminder Modal -->
                <div x-cloak x-show="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="isModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                            
                            <div class="bg-gradient-to-r from-primary to-blue-800 px-4 py-3 border-b border-blue-900">
                                <h3 class="text-lg font-bold text-white flex justify-between items-center" id="modal-title">
                                    <span x-text="'Reminders: ' + getFormattedSelectedDate()"></span>
                                    <button @click="closeModal" class="text-white hover:text-gray-200 focus:outline-none">&times;</button>
                                </h3>
                            </div>

                            <div class="px-4 py-3 bg-gray-50 max-h-48 overflow-y-auto w-full border-b border-gray-200">
                                <template x-if="selectedDayReminders.length === 0">
                                    <p class="text-sm text-gray-500 italic text-center py-2">No reminders for this date.</p>
                                </template>
                                <ul class="space-y-2">
                                    <template x-for="rem in selectedDayReminders" :key="rem.id">
                                        <li class="bg-white p-3 rounded shadow-sm border border-gray-100 flex justify-between items-start group">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input type="checkbox" :checked="rem.is_completed" @change="toggleReminder(rem)" class="text-primary focus:ring-primary rounded border-gray-300">
                                                    <span class="font-bold text-sm text-gray-800" :class="rem.is_completed ? 'line-through text-gray-400' : ''" x-text="rem.title"></span>
                                                </div>
                                                <p x-show="rem.description" class="text-xs text-gray-500 pl-6 line-clamp-2" x-text="rem.description"></p>
                                            </div>
                                            <button type="button" @click="deleteReminder(rem)" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition focus:outline-none px-2 py-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <!-- Add New Reminder -->
                            <div class="bg-white px-4 py-4 sm:p-6">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Add New Reminder</h4>
                                <div class="space-y-3">
                                    <div>
                                        <input type="text" x-model="newReminder.title" placeholder="Reminder title..." class="focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400 font-medium">
                                    </div>
                                    <div>
                                        <textarea x-model="newReminder.description" rows="2" placeholder="Notes (optional)..." class="focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400 text-gray-600"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                                <button type="button" @click="saveReminder()" :disabled="!newReminder.title || saving" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition-colors">
                                    <span x-show="!saving">Save Reminder</span>
                                    <span x-show="saving">Saving...</span>
                                </button>
                                <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Notifications (Live Data) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">General Notification</h3>
                    @php
                        $totalAlerts = $overdueMovements->count() + $recentNotifications->count();
                    @endphp
                    @if ($totalAlerts > 0)
                        <span class="bg-red-100 text-red-700 py-1 px-3 rounded-full text-xs font-bold animate-pulse">{{ $totalAlerts }} alert{{ $totalAlerts > 1 ? 's' : '' }}</span>
                    @endif
                </div>
                <div class="p-4 space-y-3 max-h-96 overflow-y-auto">

                    {{-- Overdue Movements (48h+ without acknowledgment) --}}
                    @forelse ($overdueMovements as $overdue)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500 animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-sm">
                                    <h4 class="text-red-800 font-bold">Overdue Incoming Document</h4>
                                    <p class="text-red-700 mt-1">
                                        <span class="font-mono text-xs">{{ $overdue->file->file_reference_number ?? 'N/A' }}</span>
                                        sent by <strong>{{ $overdue->fromUser->name ?? 'Unknown' }}</strong>
                                        has been pending for <strong>{{ $overdue->dispatched_at->diffForHumans() }}</strong>.
                                    </p>
                                    <a href="{{ route('queues.incoming') }}" class="text-red-800 underline text-xs font-semibold mt-1 inline-block">Review Incoming &rarr;</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-sm text-gray-500">No overdue documents.</div>
                    @endforelse

                    {{-- System-level Escalations --}}
                    @foreach ($escalatedMovements as $escalation)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-sm">
                                    <h4 class="text-red-800 font-bold">System Alert - Escalation!</h4>
                                    <p class="text-red-700 mt-1">File Ref: {{ $escalation->file->file_reference_number }} has breached movement SLA. Please review.</p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Recent Unread Notifications --}}
                    @forelse ($recentNotifications as $notif)
                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-sm flex-1">
                                    <h4 class="text-gray-800 font-bold">{{ $notif->title ?? 'Notification' }}</h4>
                                    <p class="text-gray-500 mt-1 line-clamp-2">{{ $notif->message ?? '' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Only show if there are also no overdue movements --}}
                        @if ($overdueMovements->isEmpty() && $escalatedMovements->isEmpty())
                        @endif
                    @endforelse

                    <!-- System Health -->
                    <div class="p-4 border-t border-gray-100">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 text-sm">
                                <h4 class="text-gray-800 font-bold">System Online</h4>
                                <p class="text-gray-500 mt-1">NAMA workflow engine v1.0 is currently online.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Alpine.js Calendar Component Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('calendarApp', () => ({
                month: new Date().getMonth() + 1,
                year: new Date().getFullYear(),
                daysInMonth: [],
                blankDays: [],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                reminders: [],
                loading: false,

                // Modal state
                isModalOpen: false,
                selectedDay: null,
                selectedDayReminders: [],
                newReminder: { title: '', description: '' },
                saving: false,

                init() {
                    this.calculateCalendar();
                    this.fetchReminders();
                },

                get monthName() {
                    return this.monthNames[this.month - 1];
                },

                calculateCalendar() {
                    // Get number of days in the month
                    let daysInMonth = new Date(this.year, this.month, 0).getDate();
                    
                    // Day of the week for the 1st of the month (0 = Sun, 6 = Sat)
                    let dayOfWeek = new Date(this.year, this.month - 1, 1).getDay();
                    
                    this.blankDays = Array.from({length: dayOfWeek}, (_, i) => i);
                    this.daysInMonth = Array.from({length: daysInMonth}, (_, i) => i + 1);
                },

                isToday(day) {
                    const today = new Date();
                    return this.year === today.getFullYear() && 
                           this.month === today.getMonth() + 1 && 
                           day === today.getDate();
                },

                isValidDate(day) {
                    // Current date stripped of time
                    const now = new Date();
                    now.setHours(0, 0, 0, 0);

                    // Cell date
                    const cellDate = new Date(this.year, this.month - 1, day);

                    // 12 months from now limit
                    const maxDate = new Date();
                    maxDate.setMonth(maxDate.getMonth() + 12);
                    maxDate.setHours(23, 59, 59, 999);

                    return cellDate >= now && cellDate <= maxDate;
                },

                prevMonth() {
                    if (this.month === 1) {
                        this.month = 12;
                        this.year--;
                    } else {
                        this.month--;
                    }
                    this.calculateCalendar();
                    this.fetchReminders();
                },

                nextMonth() {
                    if (this.month === 12) {
                        this.month = 1;
                        this.year++;
                    } else {
                        this.month++;
                    }
                    this.calculateCalendar();
                    this.fetchReminders();
                },

                async fetchReminders() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/reminders?year=${this.year}&month=${this.month}`);
                        if (response.ok) {
                            this.reminders = await response.json();
                        }
                    } catch (e) {
                        console.error('Failed to load reminders', e);
                    } finally {
                        this.loading = false;
                    }
                },

                hasReminder(day) {
                    const dateStr = this.formatDate(this.year, this.month, day);
                    return this.reminders.some(r => r.reminder_date && r.reminder_date.substring(0, 10) === dateStr && !r.is_completed);
                },

                formatDate(y, m, d) {
                    return `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                },

                getFormattedSelectedDate() {
                    if (!this.selectedDay) return '';
                    const d = new Date(this.year, this.month - 1, this.selectedDay);
                    return d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                },

                openModal(day) {
                    this.selectedDay = day;
                    const dateStr = this.formatDate(this.year, this.month, day);
                    this.selectedDayReminders = this.reminders.filter(r => r.reminder_date && r.reminder_date.substring(0, 10) === dateStr);
                    this.newReminder = { title: '', description: '' };
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                    this.selectedDay = null;
                },

                async saveReminder() {
                    if (!this.newReminder.title.trim()) return;
                    
                    this.saving = true;
                    const payload = {
                        reminder_date: this.formatDate(this.year, this.month, this.selectedDay),
                        title: this.newReminder.title.trim(),
                        description: this.newReminder.description.trim()
                    };

                    try {
                        const response = await fetch('/api/reminders', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        if (response.ok) {
                            const result = await response.json();
                            this.reminders.push(result.reminder);
                            this.selectedDayReminders.push(result.reminder);
                            this.newReminder = { title: '', description: '' }; // Reset
                        } else {
                            const errData = await response.json().catch(() => ({}));
                            console.error('Save failed:', errData);
                            alert('Could not save reminder. ' + (errData.message || 'Please check your inputs.'));
                        }
                    } catch (e) {
                        console.error('Failed to save reminder', e);
                        alert('Network error while saving. Please try again.');
                    } finally {
                        this.saving = false;
                    }
                },

                async toggleReminder(rem) {
                    const originalStatus = rem.is_completed;
                    rem.is_completed = !rem.is_completed;
                    
                    try {
                        const response = await fetch(`/api/reminders/${rem.id}/status`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ is_completed: rem.is_completed })
                        });
                        
                        if (!response.ok) {
                            rem.is_completed = originalStatus; // Revert on failure
                        }
                    } catch (e) {
                        rem.is_completed = originalStatus;
                    }
                },

                async deleteReminder(rem) {
                    if (!confirm('Delete this reminder?')) return;
                    
                    try {
                        const response = await fetch(`/api/reminders/${rem.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            this.reminders = this.reminders.filter(r => r.id !== rem.id);
                            this.selectedDayReminders = this.selectedDayReminders.filter(r => r.id !== rem.id);
                        }
                    } catch (e) {}
                }
            }));
        });
    </script>

</x-app-layout>
