<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <h2 class="font-bold text-xl text-primary leading-tight tracking-tight">
                    {{ __('Notifications') }}
                </h2>
                @if ($notifications->total() > 0)
                    <span class="bg-primary/10 text-primary text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ $notifications->total() }}
                    </span>
                @endif
            </div>
            <form action="{{ route('notifications.read-all') }}" method="POST" x-data="{ submitting: false }"
                @submit="submitting = true">
                @csrf
                <button type="submit" x-bind:disabled="submitting"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-cloak x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!submitting">Mark All Read</span>
                    <span x-cloak x-show="submitting">Processing...</span>
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full sm:px-6 lg:px-8">

            @if ($notifications->isEmpty())
                {{-- Premium Empty State --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-inner">
                        <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">All caught up!</h3>
                    <p class="text-sm text-gray-500 max-w-sm mx-auto">You have no notifications at this time. When documents require your attention, they'll appear here.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($notifications as $notif)
                        @php
                            // Parse the message to extract structured information
                            $senderName = null;
                            $documentTitle = null;
                            $documentRef = null;
                            $mainMessage = $notif->message;

                            // Pattern 1: "Personal Reminder from {Name}: Document "{Title}" ({Ref}) ..."
                            if (preg_match('/(?:from|by)\s+([^:]+?):\s*Document\s+"([^"]+)"\s*\(([^)]+)\)/i', $notif->message, $m)) {
                                $senderName = trim($m[1]);
                                $documentTitle = $m[2];
                                $documentRef = $m[3];
                                // Extract the main message (everything after the first sentence)
                                $parts = preg_split('/\.\s*\n|\.\s{2,}/', $notif->message, 2);
                                $mainMessage = isset($parts[1]) ? trim($parts[1]) : trim(preg_replace('/^.*?\)\s*/', '', $notif->message));
                            }
                            // Pattern 2: "Document "{Title}" ({Ref}) has been ..."
                            elseif (preg_match('/Document\s+"([^"]+)"\s*\(([^)]+)\)/i', $notif->message, $m)) {
                                $documentTitle = $m[1];
                                $documentRef = $m[2];
                                $senderName = 'System';
                                $parts = preg_split('/\.\s*\n|\.\s{2,}/', $notif->message, 2);
                                $mainMessage = isset($parts[1]) ? trim($parts[1]) : $notif->message;
                            }

                            // Clean up message — remove emoji prefixes and leading whitespace
                            $mainMessage = preg_replace('/^[\x{1F4A9}\x{1F4E9}\x{23F0}\x{1F514}\x{26A0}\x{2757}]\s*/u', '', $mainMessage);
                            $mainMessage = trim($mainMessage);

                            // Determine severity styling
                            $severityConfig = match($notif->severity) {
                                'CRITICAL' => [
                                    'gradient' => 'from-red-500 to-red-600',
                                    'shadow' => 'shadow-red-200',
                                    'badge_bg' => 'bg-red-50 text-red-700 border-red-200',
                                    'label' => 'Critical',
                                    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                                ],
                                'HIGH' => [
                                    'gradient' => 'from-orange-400 to-orange-500',
                                    'shadow' => 'shadow-orange-200',
                                    'badge_bg' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'label' => 'High Priority',
                                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'MEDIUM' => [
                                    'gradient' => 'from-amber-400 to-yellow-500',
                                    'shadow' => 'shadow-yellow-200',
                                    'badge_bg' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'label' => 'Medium',
                                    'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                default => [
                                    'gradient' => 'from-blue-400 to-primary',
                                    'shadow' => 'shadow-blue-200',
                                    'badge_bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'label' => 'Info',
                                    'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                                ],
                            };
                        @endphp

                        <div class="bg-white rounded-2xl shadow-sm border transition-all duration-300 hover:shadow-md overflow-hidden
                            {{ !$notif->is_read ? 'border-l-[5px] border-l-primary border-t-gray-100 border-r-gray-100 border-b-gray-100 bg-gradient-to-r from-blue-50/30 to-white' : 'border-gray-100' }}">

                            {{-- Card Header --}}
                            <div class="px-6 pt-6 pb-4 flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    {{-- Severity Icon --}}
                                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $severityConfig['gradient'] }} flex items-center justify-center shadow-sm {{ $severityConfig['shadow'] }} flex-shrink-0">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $severityConfig['icon'] }}" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2.5 mb-0.5">
                                            <h4 class="text-base font-bold {{ !$notif->is_read ? 'text-primary' : 'text-gray-800' }}">
                                                {{ str_replace('_', ' ', $notif->type) }}
                                            </h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $severityConfig['badge_bg'] }}">
                                                {{ $severityConfig['label'] }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 font-medium">
                                            {{ $notif->created_at->format('M d, Y') }} at {{ $notif->created_at->format('h:i A') }}
                                            <span class="text-gray-300 mx-1">·</span>
                                            {{ $notif->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Unread Indicator --}}
                                @if (!$notif->is_read)
                                    <span class="relative flex h-3 w-3 flex-shrink-0 mt-1">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                                    </span>
                                @endif
                            </div>

                            {{-- Structured Metadata --}}
                            @if ($senderName || $documentTitle)
                                <div class="mx-6 mb-4 bg-gray-50 rounded-xl border border-gray-100 divide-y divide-gray-100">
                                    @if ($senderName)
                                        <div class="flex items-center gap-3 px-4 py-3">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-sm">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">From</p>
                                                <p class="text-sm font-semibold text-gray-800">{{ $senderName }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($documentTitle)
                                        <div class="flex items-center gap-3 px-4 py-3">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-sm">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Document</p>
                                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $documentTitle }}</p>
                                                @if ($documentRef)
                                                    <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $documentRef }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Message Body --}}
                            <div class="px-6 pb-4">
                                <p class="text-sm text-gray-600 leading-relaxed {{ !$notif->is_read ? 'font-medium' : '' }}">
                                    {{ $mainMessage }}
                                </p>
                            </div>

                            {{-- Action Footer --}}
                            <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    @if (!$notif->is_read)
                                        <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Mark as Read
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-400 font-medium">
                                            <svg class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Read · {{ $notif->read_at ? $notif->read_at->diffForHumans() : '' }}
                                        </span>
                                    @endif

                                    @if ($notif->entity_id && str_contains($notif->entity_type ?? '', 'file_records'))
                                        @php
                                            $fileUuid = \App\Models\FileRecord::where('id', $notif->entity_id)->value('uuid');
                                        @endphp
                                        @if ($fileUuid)
                                            <a href="{{ route('files.show', $fileUuid) }}"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                Open Document
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
