<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('My Outgoing Documents') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 h-full flex-1">
            <div class="bg-white p-6 shadow-sm border border-gray-200">
                <p class="text-sm text-gray-500 mb-4">Documents you created that are still active in the workflow.
                    Monitor current location and send reminders when idle for more than 72 hours.</p>

                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="bg-white p-0 overflow-hidden rounded-xl shadow-sm border border-gray-100">
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm border-t border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        File Reference</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Title</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Current Dept.</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Current Holder</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Elapsed</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Status</th>
                                    <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($files as $file)
                                    @php
                                        $movement = $latestMovements[$file->id] ?? null;
                                        $referenceTime = $movement
                                            ? $movement->received_at ?? $movement->dispatched_at
                                            : $file->created_at;
                                        $elapsedHours = \Carbon\Carbon::parse($referenceTime)->diffInHours(now());
                                        $elapsedDisplay = \Carbon\Carbon::parse($referenceTime)->diffForHumans(
                                            now(),
                                            true,
                                        );

                                        // Status resolution
                                        if ($movement && $movement->movement_closed) {
                                            $status = 'Closed';
                                            $statusClass = 'bg-gray-100 text-gray-600';
                                        } elseif ($movement && $movement->received_at) {
                                            if ($elapsedHours >= 72) {
                                                $status = 'Overdue';
                                                $statusClass =
                                                    $elapsedHours >= 120
                                                        ? 'bg-red-100 text-red-800'
                                                        : 'bg-yellow-100 text-yellow-800';
                                            } else {
                                                $status = 'Awaiting Action';
                                                $statusClass = 'bg-blue-100 text-blue-800';
                                            }
                                        } else {
                                            $status = 'Awaiting Receipt';
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                        }

                                        // Current holder resolution
                                        if ($movement && $movement->to_user_id) {
                                            $holderName = $movement->toUser->name ?? 'Unknown';
                                        } elseif ($movement && $movement->to_department_id) {
                                            $holderName = 'Dept. Inbox (Unclaimed)';
                                        } else {
                                            $holderName = $file->currentOwner->name ?? '—';
                                        }

                                        $currentDeptName = $movement
                                            ? $movement->toDepartment->name ?? '—'
                                            : $file->currentDepartment->name ?? '—';

                                        // Alert eligibility
                                        $canAlert = $elapsedHours >= 72 && $movement && !$movement->movement_closed;
                                        $lastAlert = $lastAlerts[$file->id] ?? null;
                                        $alertCooldown = $lastAlert && $lastAlert->alerted_at->gt(now()->subHours(24));

                                        // Row border color
                                        $borderColor = match (true) {
                                            $elapsedHours >= 120 && $status !== 'Closed' => 'border-l-red-500',
                                            $elapsedHours >= 72 && $status !== 'Closed' => 'border-l-amber-500',
                                            default => 'border-l-blue-400',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition border-l-4 {{ $borderColor }}">
                                        <td class="px-4 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                                            <a href="{{ route('files.show', $file->uuid) }}"
                                                class="text-primary hover:underline">
                                                {{ $file->file_reference_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="text-gray-900 font-medium line-clamp-2">{{ $file->title }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-500 text-sm">
                                            {{ $currentDeptName }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            @if ($holderName === 'Dept. Inbox (Unclaimed)')
                                                <span
                                                    class="text-amber-600 font-medium italic">{{ $holderName }}</span>
                                            @else
                                                <span class="text-gray-900">{{ $holderName }}</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-4 py-4 whitespace-nowrap text-sm {{ $elapsedHours >= 120 ? 'text-red-600 font-bold' : ($elapsedHours >= 72 ? 'text-amber-600 font-semibold' : 'text-gray-500') }}">
                                            {{ $elapsedDisplay }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">{{ $status }}</span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-2" x-data="{ showAlertModal: false }">
                                                <a href="{{ route('files.show', $file->uuid) }}"
                                                    class="text-primary hover:text-blue-900 underline font-semibold text-xs px-2">View</a>

                                                @if ($canAlert)
                                                    @if ($alertCooldown)
                                                        <span class="text-xs text-gray-400 italic">Alert sent</span>
                                                    @else
                                                        <form method="POST"
                                                            action="{{ route('documents.alert', $file->uuid) }}"
                                                            class="inline" x-ref="alertForm">
                                                            @csrf
                                                            <button type="button"
                                                                class="inline-flex items-center md:whitespace-nowrap flex-shrink-0 px-3 py-1.5 bg-red-600 border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition"
                                                                @click="showAlertModal = true"
                                                                :class="{'animate-pulse': !showAlertModal}">
                                                                <svg class="w-3.5 h-3.5 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                                                    </path>
                                                                </svg>
                                                                Alert
                                                            </button>
                                                        </form>

                                                        {{-- Inline Alert Confirmation Modal --}}
                                                        <div x-show="showAlertModal" x-cloak
                                                            class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 text-left"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 transform scale-95"
                                                            x-transition:enter-end="opacity-100 transform scale-100"
                                                            x-transition:leave="transition ease-in duration-200"
                                                            x-transition:leave-start="opacity-100 transform scale-100"
                                                            x-transition:leave-end="opacity-0 transform scale-95">
                                                            <div class="relative bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center"
                                                                @click.away="showAlertModal = false">
                                                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                                                    </svg>
                                                                </div>
                                                                <h3 class="text-xl font-bold text-gray-900 mb-2">Send Reminder Alert</h3>
                                                                <p class="text-sm text-gray-500 mb-6">Are you sure you want to send a reminder alert to the current holder of <strong class="font-mono">{{ $file->file_reference_number }}</strong>?</p>
                                                                
                                                                <div class="flex justify-center gap-3">
                                                                    <button type="button" @click="showAlertModal = false"
                                                                        class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded hover:bg-gray-200 transition">Cancel</button>
                                                                    <button type="button" @click="$refs.alertForm.submit()"
                                                                        class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded hover:bg-red-700 shadow-md transition">
                                                                        Send Alert
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No active outgoing
                                                documents</h3>
                                            <p class="mt-1 text-sm text-gray-500">Documents you create will appear here
                                                until their movement chain is closed.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($files->hasPages())
                        <div class="p-4 border-t border-gray-200 bg-gray-50">
                            {{ $files->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
