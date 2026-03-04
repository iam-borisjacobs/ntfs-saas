<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('System Logo') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Update the global system logo. This will be reflected on the login screen and across the application dashboard.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('admin.settings.logo') }}" class="mt-6 space-y-6"
                            enctype="multipart/form-data">
                            @csrf

                            <!-- Current Logo Warning / Preview -->
                            <div class="flex flex-col sm:flex-row items-start gap-8">
                                <div
                                    class="shrink-0 bg-gray-50 border border-gray-200 rounded-lg p-6 flex flex-col items-center justify-center w-full sm:w-64 h-48 relative shadow-sm">
                                    <span
                                        class="absolute top-2 left-2 text-xs font-medium text-gray-400 uppercase tracking-widest">Preview</span>
                                    @if ($setting && $setting->value)
                                        <img src="{{ Storage::url($setting->value) }}" alt="Current System Logo"
                                            class="max-h-full max-w-full object-contain drop-shadow-sm" />
                                    @else
                                        <span class="text-gray-400 font-bold tracking-widest text-xl opacity-50">NAMA
                                            NG</span>
                                    @endif
                                </div>

                                <div class="flex-1 w-full">
                                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Upload a new Image</h3>
                                    <p class="text-xs text-gray-500 mb-4">Select a clear, high-resolution PNG or SVG
                                        format. Max 2MB.</p>

                                    <input id="logo" name="logo" type="file"
                                        class="block w-full text-sm text-gray-500 border border-gray-200 rounded-md
                                        file:mr-4 file:py-2.5 file:px-4
                                        file:border-0 file:rounded-l-md
                                        file:text-sm file:font-semibold
                                        file:bg-[#003B73] file:text-white
                                        hover:file:bg-blue-800 transition shadow-sm bg-white"
                                        required accept="image/png, image/jpeg, image/svg+xml" />
                                    <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save Logo') }}</x-primary-button>

                                @if (session('success'))
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
