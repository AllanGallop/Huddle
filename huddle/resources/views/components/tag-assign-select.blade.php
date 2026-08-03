@props([
    'flags',
    'selectedIds' => [],
    'label' => __('Tags'),
    'placeholder' => __('Select tags…'),
    'searchPlaceholder' => __('Search tags…'),
])

<x-assign-select
    :options="$flags"
    :selected-ids="$selectedIds"
    wire-model="assignedFlagIds"
    :label="$label"
    :placeholder="$placeholder"
    :search-placeholder="$searchPlaceholder"
    :empty-message="__('No matching tags.')"
    error-name="assignedFlagIds"
    {{ $attributes }}
/>
