<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight">
            {{ __('Outgoing Files') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 h-full flex-1">
            <div class="bg-white p-6 shadow-sm border border-gray-200">
                <p class="text-sm text-gray-500 mb-4">Files you have dispatched that are currently resting in transit,
                    awaiting acknowledgment by the recipient.</p>
                <div class="bg-white p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm border-t border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    File Reference</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    Subject / Title</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-brand-dark uppercase tracking-wider">
                                    Dispatched</th>
                                <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($files as $file)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                                        {{ $file->file_reference_number }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $file->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $file->status->name ?? 'IN_TRANSIT' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                        {{ $file->movements->first()->dispatched_at->diffForHumans() ?? 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                        <a href="{{ route('files.show', $file->id) }}"
                                            class="text-[#003B73] hover:text-blue-900 underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No outgoing files currently in transit.
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
