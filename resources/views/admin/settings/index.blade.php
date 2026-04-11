<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                
                <!-- General System Settings (Left Column) -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg h-full">
                    <div class="max-w-full">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('General System Settings') }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Update the global website title and browser favicon.') }}
                                </p>
                            </header>

                            <form method="post" action="{{ route('admin.settings.basic') }}" class="mt-6 space-y-6"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="system_title" :value="__('Website Title')" />
                                            <x-text-input id="system_title" name="system_title" type="text"
                                                class="mt-1 block w-full" :value="old('system_title', $systemTitle)" required autofocus />
                                            <x-input-error class="mt-2" :messages="$errors->get('system_title')" />
                                        </div>

                                        <div>
                                            <x-input-label for="primary_color_hex" :value="__('Primary Theme Color (Hex)')" />
                                            <div class="flex items-center gap-3 mt-1">
                                                <input type="color" id="color_picker" 
                                                    class="w-10 h-10 border-0 p-0 rounded-l cursor-pointer shadow-sm disabled:opacity-50"
                                                    x-data="{ color: '{{ old('primary_color_hex', $primaryColorHex ?? '#003B73') }}' }"
                                                    x-model="color"
                                                    @input="document.getElementById('primary_color_hex').value = $event.target.value.toUpperCase()">
                                                <x-text-input id="primary_color_hex" name="primary_color_hex" type="text"
                                                    pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                                    class="block w-full font-mono uppercase" :value="old('primary_color_hex', $primaryColorHex ?? '#003B73')" />
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('primary_color_hex')" />
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-gray-100">
                                        <h3 class="text-sm font-semibold text-gray-800 mb-4">Guest Login Portal Configuration</h3>

                                        <div class="space-y-4">
                                            <div>
                                                <x-input-label for="system_author_name" :value="__('Author/Brand Name (e.g. NAMA NG)')" />
                                                <x-text-input id="system_author_name" name="system_author_name"
                                                    type="text" class="mt-1 block w-full" :value="old('system_author_name', $systemAuthorName)" />
                                                <x-input-error class="mt-2" :messages="$errors->get('system_author_name')" />
                                            </div>

                                            <div>
                                                <x-input-label for="system_guest_header" :value="__('Banner Header Text')" />
                                                <x-text-input id="system_guest_header" name="system_guest_header"
                                                    type="text" class="mt-1 block w-full" :value="old('system_guest_header', $systemGuestHeader)" />
                                                <x-input-error class="mt-2" :messages="$errors->get('system_guest_header')" />
                                            </div>

                                            <div>
                                                <x-input-label for="system_guest_description" :value="__('Banner Description Paragraph')" />
                                                <textarea id="system_guest_description" name="system_guest_description" rows="3"
                                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('system_guest_description', $systemGuestDescription) }}</textarea>
                                                <x-input-error class="mt-2" :messages="$errors->get('system_guest_description')" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row items-center gap-6 mt-4 pt-4 border-t border-gray-100">
                                    @if ($systemFavicon && $systemFavicon->value)
                                        <div class="shrink-0">
                                            <img src="{{ Storage::url($systemFavicon->value) }}" alt="Favicon"
                                                class="w-12 h-12 object-contain bg-gray-50 p-2 rounded border border-gray-200" />
                                        </div>
                                    @endif
                                    <div class="flex-1 w-full">
                                        <x-input-label for="favicon" :value="__('Browser Favicon (.ico, .png, .svg)')" class="mb-1" />
                                        <input id="favicon" name="favicon" type="file"
                                            class="block w-full text-sm text-gray-500 border border-gray-200 rounded-md
                                            file:mr-4 file:py-2 file:px-4
                                            file:border-0 file:rounded-l-md
                                            file:text-sm file:font-semibold
                                            file:bg-primary file:text-white
                                            hover:file:bg-blue-800 transition shadow-sm bg-white"
                                            accept="image/png, image/x-icon, image/svg+xml, image/jpeg" />
                                        <x-input-error class="mt-2" :messages="$errors->get('favicon')" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-primary-button>{{ __('Save General Settings') }}</x-primary-button>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- System Logo (Right Column) -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg h-full">
                    <div class="max-w-full">
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
                                <div class="flex flex-col gap-6">
                                    <div class="shrink-0 bg-gray-50 border border-gray-200 rounded-lg p-6 flex flex-col items-center justify-center w-full h-48 lg:h-64 relative shadow-sm">
                                        <span class="absolute top-2 left-2 text-xs font-medium text-gray-400 uppercase tracking-widest">Preview</span>
                                        @if ($setting && $setting->value)
                                            <img src="{{ Storage::url($setting->value) }}" alt="Current System Logo"
                                                class="max-h-full max-w-full object-contain drop-shadow-sm" />
                                        @else
                                            <span class="text-gray-400 font-bold tracking-widest text-xl opacity-50">NAMA NG</span>
                                        @endif
                                    </div>

                                    <div class="w-full">
                                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Upload a new Image</h3>
                                        <p class="text-xs text-gray-500 mb-4">Select a clear, high-resolution PNG or SVG format. Max 2MB.</p>

                                        <input id="logo" name="logo" type="file"
                                            class="block w-full text-sm text-gray-500 border border-gray-200 rounded-md
                                            file:mr-4 file:py-2.5 file:px-4
                                            file:border-0 file:rounded-l-md
                                            file:text-sm file:font-semibold
                                            file:bg-primary file:text-white
                                            hover:file:bg-blue-800 transition shadow-sm bg-white"
                                            required accept="image/png, image/jpeg, image/svg+xml" />
                                        <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 mt-6">
                                    <x-primary-button>{{ __('Save Logo') }}</x-primary-button>

                                    @if (session('success') && !\Illuminate\Support\Str::contains(session('success'), 'Digital'))
                                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                            class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
                                    @endif
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

            </div> <!-- End Top Grid Row -->

            <!-- Enterprise Add-ons Module (Bottom Full Width) -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-full">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Enterprise System Add-ons') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Enable or disable the optional premium capabilities globally. Changes take effect immediately across all active sessions.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('admin.settings.addons') }}" class="mt-6 space-y-6">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Digital Document Add-on -->
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 flex flex-col justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            Digital Document Add-on
                                            @if (isset($digitalModuleEnabled) && $digitalModuleEnabled === 'true')
                                                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full uppercase tracking-wider">Active</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase tracking-wider">Disabled</span>
                                            @endif
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-1">Upload and strictly control digital lifecycle.</p>
                                    </div>
                                    <div class="flex items-center gap-2 mt-auto">
                                        <button type="submit" name="digital_module_enabled" value="true"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (isset($digitalModuleEnabled) && $digitalModuleEnabled === 'true') bg-green-600 text-white border-green-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Enable
                                        </button>
                                        <button type="submit" name="digital_module_enabled" value="false"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (!isset($digitalModuleEnabled) || $digitalModuleEnabled !== 'true') bg-red-600 text-white border-red-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Disable
                                        </button>
                                    </div>
                                </div>

                                <!-- Twilio SMS Escalations -->
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 flex flex-col justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            SMS Escalation Engine
                                            @if (isset($smsEscalationEnabled) && $smsEscalationEnabled === 'true')
                                                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full uppercase tracking-wider">Active</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase tracking-wider">Disabled</span>
                                            @endif
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-1">Ping staff via Twilio SMS for SLA breaches.</p>
                                    </div>
                                    <div class="flex items-center gap-2 mt-auto">
                                        <button type="submit" name="sms_escalation_enabled" value="true"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (isset($smsEscalationEnabled) && $smsEscalationEnabled === 'true') bg-green-600 text-white border-green-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Enable
                                        </button>
                                        <button type="submit" name="sms_escalation_enabled" value="false"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (!isset($smsEscalationEnabled) || $smsEscalationEnabled !== 'true') bg-red-600 text-white border-red-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Disable
                                        </button>
                                    </div>
                                </div>

                                <!-- Dynamic PDF Watermarks -->
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 flex flex-col justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            Dynamic PDF Watermarks
                                            @if (isset($pdfWatermarkEnabled) && $pdfWatermarkEnabled === 'true')
                                                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full uppercase tracking-wider">Active</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase tracking-wider">Disabled</span>
                                            @endif
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-1">Inject users' IP and timestamp into PDF downloads.</p>
                                    </div>
                                    <div class="flex items-center gap-2 mt-auto">
                                        <button type="submit" name="pdf_watermark_enabled" value="true"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (isset($pdfWatermarkEnabled) && $pdfWatermarkEnabled === 'true') bg-green-600 text-white border-green-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Enable
                                        </button>
                                        <button type="submit" name="pdf_watermark_enabled" value="false"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (!isset($pdfWatermarkEnabled) || $pdfWatermarkEnabled !== 'true') bg-red-600 text-white border-red-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Disable
                                        </button>
                                    </div>
                                </div>

                                <!-- Cryptographic Ledger -->
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 flex flex-col justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            Cryptographic Ledger (SHA-256)
                                            @if (isset($cryptoLedgerEnabled) && $cryptoLedgerEnabled === 'true')
                                                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full uppercase tracking-wider">Active</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase tracking-wider">Disabled</span>
                                            @endif
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-1">Mathematically enforce immutable system audit trails.</p>
                                    </div>
                                    <div class="flex items-center gap-2 mt-auto">
                                        <button type="submit" name="crypto_ledger_enabled" value="true"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (isset($cryptoLedgerEnabled) && $cryptoLedgerEnabled === 'true') bg-green-600 text-white border-green-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Enable
                                        </button>
                                        <button type="submit" name="crypto_ledger_enabled" value="false"
                                            class="px-4 py-2 text-sm font-semibold rounded shadow-sm border transition flex-1
                                            @if (!isset($cryptoLedgerEnabled) || $cryptoLedgerEnabled !== 'true') bg-red-600 text-white border-red-700 @else bg-white text-gray-700 border-gray-300 hover:bg-gray-50 @endif">
                                            Disable
                                        </button>
                                    </div>
                                </div>

                            </div>

                            @if (session()->has('success') && \Illuminate\Support\Str::contains(session('success'), 'Add-on'))
                                <div class="flex items-center gap-4 mt-4">
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
                                </div>
                            @endif
                        </form>
                    </section>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
