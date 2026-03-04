<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#003B73] leading-tight">
            {{ isset($department) ? 'Edit Department: ' . $department->name : 'Create New Department' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-8 shadow-sm border border-gray-200">
                <form
                    action="{{ isset($department) ? route('admin.departments.update', $department->id) : route('admin.departments.store') }}"
                    method="POST" class="space-y-6">
                    @csrf
                    @if (isset($department))
                        @method('PUT')
                    @endif

                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#003B73]">Department Name</label>
                        <input type="text" name="name" id="name"
                            value="{{ old('name', $department->name ?? '') }}" required
                            class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] focus:ring focus:ring-[#003B73] focus:ring-opacity-50 shadow-sm transition">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-semibold text-[#003B73]">Department Code</label>
                        <input type="text" name="code" id="code"
                            value="{{ old('code', $department->code ?? '') }}" required placeholder="e.g. CR-001"
                            class="mt-1 block w-full rounded-sm border-gray-300 focus:border-[#003B73] font-mono uppercase shadow-sm transition">
                        @error('code')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-6 mt-6 border-t border-gray-100 flex justify-end space-x-3">
                        <a href="{{ route('admin.departments.index') }}"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900 font-semibold text-sm">Cancel</a>
                        <button type="submit"
                            class="px-6 py-2 bg-[#003B73] text-white rounded-sm font-bold text-sm uppercase tracking-wide hover:bg-blue-800 transition shadow-md">
                            {{ isset($department) ? 'Update Department' : 'Save Department' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
