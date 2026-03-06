<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
            {{ __('Pending Files (My Desk)') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 h-full flex-1">
            <div class="bg-white p-6 shadow-sm border border-gray-200">
                <p class="text-sm text-gray-500 mb-4">Files securely within your custody. These files are awaiting active
                    processing, filing, dispatch, or closure.</p>
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
                                        Current Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                        Last Updated</th>
                                    <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($files as $file)
                                    <tr class="hover:bg-gray-50 transition border-l-4 border-l-[#003B73]">
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                                            {{ $file->file_reference_number }}</td>
                                        <td class="px-6 py-4">
                                            <div class="text-gray-900 font-medium line-clamp-2">{{ $file->title }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $file->status->name ?? 'UNKNOWN' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                            {{ $file->updated_at->format('M d, Y H:i') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-medium flex justify-end space-x-3 items-center">
                                            <a href="{{ route('files.show', $file->uuid) }}"
                                                class="text-[#003B73] hover:text-blue-900 underline font-semibold px-2">View</a>
                                            <a href="{{ route('files.dispatch.create', $file->uuid) }}"
                                                class="inline-flex items-center px-4 py-2 bg-[#003B73] border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Dispatch
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            Your desk is clear. No pending files in your custody.
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
