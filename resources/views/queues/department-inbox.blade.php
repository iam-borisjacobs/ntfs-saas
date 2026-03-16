<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
            {{ __('Department Inbox') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 h-full flex-1">
            <div class="bg-white p-6 shadow-sm border border-gray-200">
                <p class="text-sm text-gray-500 mb-4">Documents dispatched to your department without a specific
                    recipient. Any officer in the department may acknowledge receipt to claim custody.</p>
                <div class="bg-white p-0 overflow-hidden rounded-xl shadow-sm border border-gray-100">
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm border-t border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        File Reference</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Subject / Title</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Origin</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Sender</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Dispatched</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Priority</th>
                                    <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($files as $movement)
                                    <tr class="hover:bg-gray-50 transition border-l-4 border-l-amber-500">
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                                            {{ $movement->file->file_reference_number }}</td>
                                        <td class="px-6 py-4 max-w-[200px]">
                                            <div class="text-gray-900 font-medium truncate"
                                                title="{{ $movement->file->title }}">
                                                {{ $movement->file->title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-sm">
                                            {{ $movement->fromDepartment->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-sm">
                                            {{ $movement->fromUser->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                            {{ $movement->dispatched_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($movement->file->priority_level == 2)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Critical</span>
                                            @elseif ($movement->file->priority_level == 1)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Urgent</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Normal</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-medium flex justify-end space-x-2 items-center w-full min-w-max flex-shrink-0">
                                            <a href="{{ route('files.show', $movement->file->uuid) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-[#003B73] border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap flex-shrink-0">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View
                                            </a>
                                            <a href="{{ route('movements.receive.form', $movement->id) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap flex-shrink-0">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Receive
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">Department inbox empty
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">No documents pending acknowledgment
                                                for your department.</p>
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
