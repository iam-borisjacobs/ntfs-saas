<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-6">
            <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
                {{ __('Department Management') }}
            </h2>
            <a href="{{ route('admin.departments.create') }}"
                class="px-4 py-2 bg-[#003B73] text-white rounded-sm text-sm font-semibold tracking-wide hover:bg-blue-800 transition">
                + ADD DEPARTMENT
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
                                Department Name
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-semibold text-[#003B73] uppercase tracking-wider">
                                Active Users</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($departments as $dept)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600 font-bold">
                                    {{ $dept->code ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $dept->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ $dept->users()->count() }} users
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                    <a href="{{ route('admin.departments.edit', $dept->id) }}"
                                        class="text-[#003B73] hover:text-blue-900 underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
