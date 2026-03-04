<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans antialiased text-gray-800 bg-gray-50 flex h-screen overflow-hidden">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen w-full relative">

        <!-- Sidebar Mobile Overlay -->
        <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden transition-opacity"
            x-transition:enter="ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-[#003B73] text-white flex flex-col h-full transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:flex-shrink-0 shadow-lg">
            <div class="p-6 flex flex-col items-center justify-center border-b border-white/10 pb-8">
                @php
                    $systemLogo = \App\Models\SystemSetting::where('key', 'system_logo_path')->first();
                @endphp

                @if ($systemLogo && $systemLogo->value)
                    <img src="{{ Storage::url($systemLogo->value) }}" alt="System Logo"
                        class="max-h-16 w-auto object-contain drop-shadow-md bg-white p-2 rounded-lg mb-2">
                @else
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 opacity-90" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                        </svg>
                        <span class="text-xl font-bold tracking-widest">NAMA NG</span>
                    </div>
                @endif
            </div>

            <nav class="flex-1 px-4 space-y-2 overflow-y-auto mt-4">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 bg-white/10 rounded font-medium transition duration-200">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                    Dashboard
                </a>

                <div class="pt-4 pb-2">
                    <a href="{{ route('files.create') }}"
                        class="block text-center w-full bg-white text-[#003B73] font-semibold py-2 rounded shadow-sm hover:bg-gray-100 transition duration-200">
                        Generate or Create
                    </a>
                </div>

                <div class="space-y-1 mt-4">
                    @php
                        $userId = \Illuminate\Support\Facades\Auth::id();
                        $departmentId = \Illuminate\Support\Facades\Auth::user()->department_id;

                        // Outgoing Count: Files in IN_TRANSIT where current logged in user is the owner
                        $outgoingCount = \App\Models\FileRecord::where('current_owner_id', $userId)
                            ->whereHas('status', function ($q) {
                                $q->where('name', 'IN_TRANSIT');
                            })
                            ->count();

                        // Incoming Count: Files where a movement exists with to_user_id = Auth AND acknowledgment_status = PENDING
                        $incomingCount = \App\Models\FileMovement::where('to_user_id', $userId)
                            ->where('acknowledgment_status', 'PENDING')
                            ->count();

                        // Pending Count: Files currently owned by user but NOT in transit
                        $pendingCount = \App\Models\FileRecord::where('current_owner_id', $userId)
                            ->whereHas('status', function ($q) {
                                $q->where('name', '!=', 'IN_TRANSIT');
                            })
                            ->count();
                    @endphp

                    <a href="{{ route('queues.outgoing') }}"
                        class="flex items-center justify-between px-4 py-2.5 rounded text-gray-300 hover:bg-white/5 hover:text-white transition group">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Outgoing
                        </span>
                        @if ($outgoingCount > 0)
                            <span
                                class="bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $outgoingCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('queues.incoming') }}"
                        class="flex items-center justify-between px-4 py-2.5 rounded text-gray-300 hover:bg-white/5 hover:text-white transition group">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Incoming
                        </span>
                        @if ($incomingCount > 0)
                            <span
                                class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $incomingCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('queues.pending') }}"
                        class="flex items-center justify-between px-4 py-2.5 rounded text-gray-300 hover:bg-white/5 hover:text-white transition group">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pending
                        </span>
                        @if ($pendingCount > 0)
                            <span
                                class="bg-gray-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('notifications.index') }}"
                        class="flex items-center justify-between px-4 py-2.5 rounded text-gray-300 hover:bg-white/5 hover:text-white transition group">
                        @php
                            $unreadCountSidebar = \App\Models\Notification::where(
                                'user_id',
                                \Illuminate\Support\Facades\Auth::id(),
                            )
                                ->where('is_read', false)
                                ->count();
                        @endphp
                        <span class="flex items-center gap-3">
                            <span class="w-5 text-center relative flex justify-center items-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                @if ($unreadCountSidebar > 0)
                                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                                @endif
                            </span>
                            Notification
                        </span>
                    </a>
                </div>

                @role('Sys Admin')
                    <div class="px-4 mt-8 mb-2">
                        <p class="text-xs font-semibold text-blue-200 uppercase tracking-wider">Administration</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 px-4 py-2.5 rounded text-gray-300 hover:bg-white/5 hover:text-white transition group">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Users & Roles
                        </a>
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center gap-3 px-4 py-2.5 rounded text-gray-300 hover:bg-white/5 hover:text-white transition group">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                            Departments
                        </a>
                        <a href="/admin/settings"
                            class="flex items-center gap-3 px-4 py-2.5 rounded text-gray-300 hover:bg-white/5 hover:text-white transition group">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71-.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            System Settings
                        </a>
                    </div>
                @endrole
            </nav>

            <div class="px-4 py-6 border-t border-white/10 space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:text-white w-full text-left transition group">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-full overflow-hidden w-full relative">

            <!-- Top Header -->
            <header
                class="h-16 relative z-20 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-8 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true"
                        class="p-1 flex items-center justify-center text-gray-500 hover:text-gray-700 bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 lg:hidden">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    @isset($header)
                        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                            {{ $header }}
                        </h2>
                    @endisset
                </div>

                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-2 sm:gap-4 hover:bg-gray-50 px-2 sm:px-3 py-2 rounded-md transition cursor-pointer">
                    <div class="text-sm font-medium text-gray-600 text-right hidden sm:block">
                        {{ \Illuminate\Support\Facades\Auth::user()->name }}
                        <span
                            class="text-xs text-gray-400 block">{{ \Illuminate\Support\Facades\Auth::user()->system_identifier }}</span>
                    </div>
                    <div
                        class="h-10 w-10 rounded-full bg-brand-dark border-2 border-brand-accent flex items-center justify-center overflow-hidden text-white shadow-sm">
                        <img src="{{ \Illuminate\Support\Facades\Auth::user()->profile_photo_url }}"
                            alt="Profile Photo" class="h-full w-full object-cover">
                    </div>
                </a>
            </header>

            <!-- Scrollable Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F4F7F9] p-6 lg:p-8">
                {{ $slot }}
            </main>

        </div>
    </div> <!-- Close the Alpine root div -->
</body>

</html>
