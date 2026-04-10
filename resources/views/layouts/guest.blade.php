<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $systemTitle =
            \App\Models\SystemSetting::where('key', 'system_title')->value('value') ?: config('app.name', 'Laravel');
        $systemFavicon = \App\Models\SystemSetting::where('key', 'system_favicon_path')->value('value');

        $systemAuthorName = \App\Models\SystemSetting::where('key', 'system_author_name')->value('value') ?: 'NAMA NG';
        $systemGuestHeader =
            \App\Models\SystemSetting::where('key', 'system_guest_header')->value('value') ?:
            'Secure Document Tracking Portal';
        $systemGuestDescription =
            \App\Models\SystemSetting::where('key', 'system_guest_description')->value('value') ?:
            'Authorized personnel only. Access the central registry to dispatch, track, and acknowledge critical documentation sequences across all inter-departmental desks.';
        $primaryColorHex = \App\Models\SystemSetting::where('key', 'primary_color_hex')->value('value') ?: '#003B73';
    @endphp

    <title>{{ $systemTitle }}</title>

    @if ($systemFavicon)
        <link rel="icon" href="{{ Storage::url($systemFavicon) }}" type="image/x-icon">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: {{ $primaryColorHex }};
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased min-h-screen flex flex-col lg:flex-row bg-white">

    <!-- Left Banner Side (Brand) -->
    <div class="lg:w-1/2 bg-primary p-12 lg:p-24 flex flex-col justify-center text-white relative overflow-hidden">
        <!-- Decorative overlay -->
        <div
            class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-white via-transparent to-transparent">
        </div>

        <div class="relative z-10 max-w-lg">
            <div class="flex items-center gap-4 mb-8">
                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                </svg>
                <span class="text-4xl font-bold tracking-widest uppercase">{{ $systemAuthorName }}</span>
            </div>
            <h1 class="text-4xl font-light mb-4 leading-tight">{!! nl2br(e($systemGuestHeader)) !!}</h1>
            <p class="text-[#8FBCE3] text-lg leading-relaxed mb-12">
                {{ $systemGuestDescription }}
            </p>
        </div>

        <!-- Bottom Legal -->
        <div class="absolute bottom-8 left-12 lg:left-24 text-sm text-[#4A8EC9]">
            &copy; {{ date('Y') }} Nigerian Airspace Management Agency.
        </div>
    </div>

    <!-- Right Form Side -->
    <div class="flex-1 flex items-center justify-center p-8 lg:p-24">
        <div class="w-full max-w-md">

            <!-- Dynamic System Logo -->
            <div class="mb-10 text-center flex flex-col items-center">
                @php
                    $systemLogo = \App\Models\SystemSetting::where('key', 'system_logo_path')->first();
                @endphp

                @if ($systemLogo && $systemLogo->value)
                    <img src="{{ Storage::url($systemLogo->value) }}" alt="System Logo"
                        class="max-h-24 w-auto object-contain mb-4">
                @else
                    <div
                        class="h-16 w-16 bg-primary text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-blue-900/20">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                        </svg>
                    </div>
                @endif
                <h2 class="text-2xl font-bold text-gray-900">Sign in to your account</h2>
                <p class="text-gray-500 mt-2">Enter your designated credentials to proceed.</p>
            </div>

            {{ $slot }}

        </div>
    </div>
</body>

</html>
