<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('Pending Files (My Desk)') }}
        </h2>
    </x-slot>

    <div class="py-8 h-full flex flex-col" x-data="{
        showCloseModal: false,
        closeFileUuid: '',
        closeFileRef: '',
        closureReason: '',
        submittingClose: false
    }">
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
                                        class="px-6 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        File Reference</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Subject / Title</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Current Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-primary uppercase tracking-wider">
                                        Last Updated</th>
                                    <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($files as $file)
                                    <tr class="hover:bg-gray-50 transition border-l-4 border-l-primary">
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
                                                class="px-6 py-4 whitespace-nowrap text-right font-medium flex justify-end space-x-2 items-center">
                                                <a href="{{ route('files.show', $file->uuid) }}"
                                                    class="text-primary hover:text-blue-900 underline font-semibold px-2">View</a>
                                                <a href="{{ route('files.dispatch.create', $file->uuid) }}"
                                                    class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                                    Dispatch
                                                </a>
                                                <button type="button" 
                                                    @click="showCloseModal = true; closeFileUuid = '{{ $file->uuid }}'; closeFileRef = '{{ $file->file_reference_number }}'; closureReason = ''"
                                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                                    Close
                                                </button>
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

        <!-- Global Close Document Modal -->
        <div x-cloak x-show="showCloseModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCloseModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCloseModal = false"
                    aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showCloseModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" :action="`/files/${closeFileUuid}/close`">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Close Document
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to close file <strong x-text="closeFileRef" class="text-gray-800"></strong>? This
                                            action secures the document and prevents further routing unless explicitly reopened.
                                        </p>
                                        <div class="mt-4">
                                            <label for="closure_reason"
                                                class="block text-sm font-medium text-gray-700">Closure Reason / Final Minutes</label>
                                            <textarea id="closure_reason" name="closure_reason" rows="3" required x-model="closureReason"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-gray-500 focus:border-gray-500 sm:text-sm"
                                                placeholder="Write your final remarks..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" @click="submittingClose = true"
                                :class="{ 'opacity-50 cursor-not-allowed': submittingClose }"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Confirm Closure
                            </button>
                            <button type="button" @click="showCloseModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
