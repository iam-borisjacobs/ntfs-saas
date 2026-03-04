<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-brand-dark leading-tight">
                {{ __('Your Notifications') }}
            </h2>
            <form action="{{ route('notifications.read-all') }}" method="POST" x-data="{ submitting: false }"
                @submit="submitting = true">
                @csrf
                <button type="submit" x-bind:disabled="submitting"
                    class="px-4 py-2 border border-brand-dark text-brand-dark hover:bg-brand-dark hover:text-white transition rounded-sm text-sm font-semibold tracking-wide disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span x-show="!submitting">MARK ALL AS READ</span>
                    <span x-show="submitting">PROCESSING...</span>
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <ul class="divide-y divide-gray-200">
                    @forelse($notifications as $notif)
                        <li
                            class="p-6 hover:bg-gray-50 transition relative group {{ !$notif->is_read ? 'bg-blue-50/20' : '' }}">
                            <div class="flex items-start">
                                <!-- Severity Indicator -->
                                <div class="flex-shrink-0 mt-1">
                                    @if ($notif->severity === 'CRITICAL')
                                        <span
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-red-100 ring-4 ring-white">
                                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </span>
                                    @elseif($notif->severity === 'HIGH')
                                        <span
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 ring-4 ring-white">
                                            <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    @elseif($notif->severity === 'MEDIUM')
                                        <span
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-yellow-100 ring-4 ring-white">
                                            <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 ring-4 ring-white">
                                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>

                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p
                                            class="text-sm font-medium {{ !$notif->is_read ? 'text-[#003B73]' : 'text-gray-900' }}">
                                            {{ str_replace('_', ' ', $notif->type) }}
                                        </p>
                                        <div class="text-sm text-gray-500">
                                            <time
                                                datetime="{{ $notif->created_at }}">{{ $notif->created_at->diffForHumans() }}</time>
                                            @if (!$notif->is_read)
                                                <span
                                                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-[#003B73]">New</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-700">
                                        <p>{{ $notif->message }}</p>
                                    </div>

                                    @if (!$notif->is_read)
                                        <div class="mt-2 text-sm">
                                            <form action="{{ route('notifications.read', $notif->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-indigo-600 hover:text-indigo-900 font-medium">Mark as
                                                    read</button>
                                            </form>
                                            @if ($notif->entity_id && str_contains($notif->entity_type, 'FileRecord'))
                                                <span class="mx-2 text-gray-300">|</span>
                                                <a href="#"
                                                    class="text-gray-600 hover:text-gray-900 font-medium">View File
                                                    Reference</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="p-8 text-center text-gray-500">
                            You have no notifications at this time.
                        </li>
                    @endforelse
                </ul>
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $notifications->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
