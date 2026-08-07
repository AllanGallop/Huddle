@props([
    'users',
    'selectedId' => null,
    'wireModel' => null,
    'name' => null,
    'label' => __('Member'),
    'placeholder' => __('Select a member…'),
    'searchPlaceholder' => __('Search members…'),
    'allowClear' => false,
    'clearLabel' => __('None'),
    'emptyValue' => null,
    'required' => false,
    'showEmail' => true,
])

<x-search-select
    :options="$users"
    :selected-id="$selectedId"
    :wire-model="$wireModel"
    :name="$name"
    :label="$label"
    :placeholder="$placeholder"
    :search-placeholder="$searchPlaceholder"
    :empty-message="__('No matching members.')"
    :allow-clear="$allowClear"
    :clear-label="$clearLabel"
    :empty-value="$emptyValue"
    option-label="name"
    :option-sublabel="$showEmail ? 'email' : null"
    :error-name="$wireModel ?? $name"
    :required="$required"
    {{ $attributes }}
/>
