@props(['disabled' => false])

<input @disabled($disabled)
    {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#003B73] focus:ring-[#003B73] rounded-md shadow-sm']) }}>
