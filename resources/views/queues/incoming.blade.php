<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
            {{ __('Incoming Files') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 h-full flex-1">
            <div class="bg-white p-6 shadow-sm border border-gray-200">
                <p class="text-sm text-gray-500 mb-4">Files dispatched to you that require your active acknowledgment to
                    accept custody.</p>
                <div class="bg-white p-0 overflow-x-auto">
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
                                    Sender</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                    Sent At</th>
                                <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($files as $file)
                                <tr class="hover:bg-gray-50 transition border-l-4 border-l-orange-400">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                                        {{ $file->file_reference_number }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $file->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                        {{ $file->movements->first()->fromUser->system_identifier ?? 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                        {{ $file->movements->first()->dispatched_at->diffForHumans() ?? 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                        <div class="flex items-center justify-end space-x-3">
                                            <a href="{{ route('files.show', $file->id) }}"
                                                class="text-[#003B73] hover:text-blue-900 underline font-semibold px-2">View</a>
                                            <form method="POST"
                                                action="{{ route('movements.receive', $file->movements->first()->id) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900 border border-green-600 px-3 py-1 rounded text-xs font-bold uppercase tracking-wide bg-white transition cursor-pointer"
                                                    onclick="return confirm('Are you sure you want to accept custody of this physical file?');">Acknowledge</button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('movements.reject', $file->movements->first()->id) }}"
                                                onsubmit="const reason = prompt('Please enter the reason for rejection:'); if(reason) { this.insertAdjacentHTML('beforeend', '<input type=&quot;hidden&quot; name=&quot;rejection_reason&quot; value=&quot;' + reason.replace(/&quot;/g, '&amp;quot;') + '&quot;>'); return true; } return false;">
                                                @csrf
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900 border border-red-600 px-3 py-1 rounded text-xs font-bold uppercase tracking-wide bg-white transition cursor-pointer">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        You have no incoming files at this moment.
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
    </div>
</x-app-layout>
