<div x-data="customSelectWrapper()" x-init="initSelect($refs.selectContainer.querySelector('select'))" class="relative w-full" :class="{'z-[9999]': open}">
    {{-- The hidden native select passed from the parent view --}}
    <div x-ref="selectContainer" class="hidden">
        {{ $slot }}
    </div>

    {{-- Custom Styled Dropdown Button --}}
    <button type="button" @click="toggle()" x-ref="triggerBtn"
        class="relative w-full bg-white border border-gray-300 rounded-sm shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm transition ease-in-out duration-150"
        aria-haspopup="listbox" :aria-expanded="open" aria-labelledby="listbox-label">
        
        <span class="block truncate" x-text="selectedText || '— Select an Option —'" :class="{'text-gray-400': !selectedText, 'text-gray-900': selectedText}">
            — Select an Option —
        </span>
        
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    {{-- Custom Dropdown List (fixed position to escape overflow parents) --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            x-ref="dropdownPanel"
            x-effect="if (open) { $nextTick(() => { positionDropdown() }) }"
            @scroll.window.debounce.50ms="if (open) positionDropdown()"
            @resize.window.debounce.100ms="if (open) positionDropdown()"
            @click.away="close()"
            class="fixed z-[9999] max-w-xl bg-white shadow-lg rounded-sm py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
            style="max-height: 440px; overflow-y: auto;">
            
            {{-- Optional Search Bar --}}
            <div class="px-2 pb-2 pt-1 border-b border-gray-100 sticky top-0 bg-white z-10" x-show="options.length > 2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-model="search" @click.stop placeholder="Search..."
                        class="block w-full pl-9 pr-3 py-1.5 border border-gray-200 rounded-sm leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm">
                </div>
            </div>

            <ul tabindex="-1" role="listbox">
                {{-- Empty State --}}
                <li x-show="filteredOptions.length === 0" class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">
                    No results found.
                </li>

                {{-- Render Options --}}
                <template x-for="(opt, index) in filteredOptions" :key="index">
                    <li @click="selectOption(opt.value)"
                        class="text-gray-900 cursor-pointer select-none relative py-2.5 pl-3 pr-9 hover:bg-primary hover:text-white group border-b border-gray-50 last:border-0"
                        :class="{'bg-blue-50 text-primary font-semibold': isSelected(opt.value)}">
                        
                        <span class="block truncate" x-text="opt.text" :class="{'font-semibold': isSelected(opt.value), 'font-normal': !isSelected(opt.value)}"></span>
                        
                        {{-- Checkmark for selected item --}}
                        <span x-show="isSelected(opt.value)"
                            class="text-primary group-hover:text-white absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                </template>
            </ul>
        </div>
    </template>
</div>
