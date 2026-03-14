<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-6">
            <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
                {{ __('User Management') }}
            </h2>
            <a href="{{ route('admin.users.create') }}"
                class="px-4 py-2 bg-[#003B73] text-white rounded-sm text-sm font-semibold tracking-wide hover:bg-blue-800 transition">
                + ONBOARD USER
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                Name & Email
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                Department</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                Role & Clearance</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                Status</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600 font-bold">
                                    {{ $user->system_identifier }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ $user->department->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 uppercase">
                                        {{ $user->roles->first()->name ?? 'No Role' }}
                                    </span>
                                    <br>
                                    <span class="text-xs text-gray-400 mt-1 block">Level
                                        {{ $user->clearance_level }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->is_active)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                        class="text-[#003B73] hover:text-blue-900 underline">Manage</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
