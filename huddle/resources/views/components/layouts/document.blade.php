@props([
    'title' => config('app.name'),
    'project' => null,
    'forPdf' => false,
    'pdfUrl' => null,
    'emailAction' => null,
    'backUrl' => null,
    'backLabel' => __('Back'),
    'recipientEmail' => '',
    'emailFields' => [],
    'paperOrientation' => 'portrait',
    'paperMargin' => '16mm',
    'forceLightMode' => false,
    'showHeader' => true,
    'showFooter' => true,
])

@php
    $bannerSrc = \App\Support\Branding::bannerSrc($forPdf);
    $hasToolbar = ! $forPdf && ($pdfUrl || $backUrl || $emailAction);
    $bodyMaxWidth = $paperOrientation === 'landscape' ? 'none' : '52rem';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @if ($forceLightMode)
            <meta name="color-scheme" content="light">
        @endif
        <title>{{ $title }}</title>
        <style>
            @page {
                size: A4 {{ $paperOrientation }};
                margin: {{ $paperMargin }};
            }
            html {
                @if ($forceLightMode)
                    color-scheme: light;
                    background: #ffffff;
                @endif
            }
            * { box-sizing: border-box; }
            body {
                font-family: ui-sans-serif, system-ui, sans-serif;
                color: #18181b;
                line-height: 1.5;
                margin: 0;
                padding: 2rem;
                max-width: {{ $bodyMaxWidth }};
                margin-inline: auto;
                @if ($forceLightMode)
                    background: #ffffff;
                @endif
            }
            .doc-header {
                margin-bottom: 2rem;
                padding-bottom: 1.5rem;
                border-bottom: 2px solid #287878;
            }
            .doc-header img.banner { display: block; max-width: 100%; width: 20rem; height: auto; }
            h1 {
                font-size: 1.5rem;
                margin: 0 0 0.25rem;
                color: #287878;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .meta { color: #52525b; font-size: 0.875rem; margin-bottom: 2rem; }
            table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
            th, td { text-align: left; padding: 0.625rem 0; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
            th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a; }
            td.amount { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
            .total-row td { font-weight: 600; border-top: 2px solid #287878; border-bottom: none; padding-top: 0.75rem; color: #287878; }
            .notes { white-space: pre-wrap; font-size: 0.875rem; color: #3f3f46; margin-top: 1.5rem; padding: 1rem; background: #f4f4f5; border-radius: 0.375rem; }
            .footer { margin-top: 3rem; font-size: 0.75rem; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 1rem; }
            .toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                align-items: center;
                margin-bottom: 1.5rem;
                padding: 1rem;
                background: #fafafa;
                border: 1px solid #e4e4e7;
                border-radius: 0.5rem;
            }
            .toolbar a, .toolbar button {
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
                padding: 0.5rem 0.875rem;
                font-size: 0.875rem;
                border-radius: 0.375rem;
                border: 1px solid #d4d4d4;
                background: white;
                color: #287878;
                text-decoration: none;
                cursor: pointer;
            }
            .toolbar a:hover, .toolbar button:hover { background: #f0fdfa; }
            .toolbar form { display: inline-flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; }
            .toolbar input[type="email"] {
                padding: 0.5rem 0.75rem;
                border: 1px solid #d4d4d4;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                min-width: 14rem;
            }
            .toolbar .primary {
                background: #287878;
                color: white;
                border-color: #287878;
            }
            @if ($forceLightMode)
                .toolbar input[type="email"],
                .toolbar a,
                .toolbar button {
                    color-scheme: light;
                }
            @endif
            .status-msg {
                margin-bottom: 1rem;
                padding: 0.75rem 1rem;
                background: #ecfdf5;
                border: 1px solid #7edb66;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                color: #166534;
            }
            @media print {
                body { padding: 0; }
                .no-print { display: none !important; }
            }
        </style>
    </head>
    <body>
        @unless ($forPdf)
            @if (session('status'))
                <p class="status-msg no-print">{{ session('status') }}</p>
            @endif

            @if ($hasToolbar)
                <div class="toolbar no-print">
                    <button type="button" onclick="window.print()">{{ __('Print') }}</button>

                    @if ($pdfUrl)
                        <a href="{{ $pdfUrl }}">{{ __('Download PDF') }}</a>
                    @endif

                    @if ($backUrl)
                        <a href="{{ $backUrl }}">{{ $backLabel }}</a>
                    @endif

                    @if ($emailAction)
                        <form method="POST" action="{{ $emailAction }}">
                            @csrf
                            @foreach ($emailFields as $name => $value)
                                @if (is_array($value))
                                    @foreach ($value as $entry)
                                        <input type="hidden" name="{{ $name }}[]" value="{{ $entry }}">
                                    @endforeach
                                @elseif ($value !== null && $value !== '' && $value !== false)
                                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <input type="email" name="email" value="{{ old('email', $recipientEmail) }}" required placeholder="{{ __('Recipient email') }}">
                            <button type="submit" class="primary">{{ __('Email PDF') }}</button>
                        </form>
                    @endif
                </div>
            @endif
        @endunless

        @if ($showHeader)
            <header class="doc-header">
                <img src="{{ $bannerSrc }}" alt="{{ config('app.name') }}" class="banner">
            </header>
        @endif

        {{ $slot }}

        @if ($showFooter)
            <p class="footer">{{ config('app.name') }} · {{ now()->format('j F Y') }}</p>
        @endif
    </body>
</html>
