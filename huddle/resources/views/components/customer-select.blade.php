@props([
    'customers',
    'selectedId' => null,
    'wireModel' => 'customer_id',
    'label' => null,
    'placeholder' => null,
    'allowClear' => true,
    'clearLabel' => null,
])

@php
    $options = collect($customers)->map(function ($customer) {
        $parts = array_filter([
            $customer->typeLabel(),
            $customer->email,
            $customer->telephone,
        ]);

        return (object) [
            'id' => $customer->id,
            'name' => $customer->name,
            'description' => implode(' · ', $parts),
        ];
    });
@endphp

<x-search-select
    :options="$options"
    :selected-id="$selectedId"
    :wire-model="$wireModel"
    :label="$label ?? __('Customer')"
    :placeholder="$placeholder ?? __('Search customers…')"
    :search-placeholder="__('Search by name, email, or type…')"
    :empty-message="__('No matching customers.')"
    :allow-clear="$allowClear"
    :clear-label="$clearLabel ?? __('No customer')"
    :empty-value="null"
    option-label="name"
    option-sublabel="description"
    :error-name="$wireModel"
    {{ $attributes }}
/>
