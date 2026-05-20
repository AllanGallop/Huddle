<div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:heading size="lg" class="inline-flex items-center gap-2">
        <x-material-icon name="payments" class="text-[1.375rem] text-huddle-primary" />
        {{ __('Finance') }}
    </flux:heading>
    <flux:text class="mt-1">{{ __('Quotes, invoices, and payment tracking.') }}</flux:text>

    <form wire:submit="saveFinancials" class="mt-5 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="due_date" type="date" :label="__('Due date')" />
            <flux:select wire:model="financial_status" :label="__('Financial status')">
                <flux:select.option value="">{{ __('Not set') }}</flux:select.option>
                @foreach (\App\Models\Project::FINANCIAL_STATUSES as $status)
                    <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="quote_amount" type="number" step="0.01" min="0" :label="__('Quote amount (£)')" />
            <flux:input wire:model="invoice_amount" type="number" step="0.01" min="0" :label="__('Invoice amount (£)')" />
            <flux:input wire:model="deposit_amount" type="number" step="0.01" min="0" :label="__('Deposit received (£)')" />
            <flux:input wire:model="payment_amount" type="number" step="0.01" min="0" :label="__('Payment received (£)')" />
        </div>

        <div class="rounded-lg border border-huddle-primary/20 bg-huddle-primary/5 px-4 py-3 text-sm">
            <span class="text-zinc-500">{{ __('Balance due') }}:</span>
            <span class="ms-2 font-semibold text-zinc-900 dark:text-white">{{ $project->formatMoney($project->balanceDue()) }}</span>
        </div>

        <flux:textarea wire:model="quote_notes" :label="__('Quote notes')" rows="3" :placeholder="__('Line items or terms for the quote...')" />
        <flux:textarea wire:model="invoice_notes" :label="__('Invoice notes')" rows="3" :placeholder="__('Payment terms or invoice details...')" />

        <flux:button type="submit" variant="primary">
            <span class="inline-flex items-center gap-2">
                <x-material-icon name="save" class="text-[1.25rem]" />
                {{ __('Save financials') }}
            </span>
        </flux:button>
    </form>

    @if ($project->financial_status || $project->quoted_at)
        <dl class="mt-5 grid gap-2 border-t border-zinc-200 pt-4 text-xs text-zinc-500 dark:border-zinc-700 sm:grid-cols-2">
            @if ($project->quoted_at)
                <div>{{ __('Quoted') }}: {{ $project->quoted_at->format('j M Y') }}</div>
            @endif
            @if ($project->invoiced_at)
                <div>{{ __('Invoiced') }}: {{ $project->invoiced_at->format('j M Y') }}</div>
            @endif
            @if ($project->deposit_paid_at)
                <div>{{ __('Deposit paid') }}: {{ $project->deposit_paid_at->format('j M Y') }}</div>
            @endif
            @if ($project->paid_at)
                <div>{{ __('Paid in full') }}: {{ $project->paid_at->format('j M Y') }}</div>
            @endif
        </dl>
    @endif

    <div class="mt-6 space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
        <flux:heading size="sm">{{ __('Documents') }}</flux:heading>
        <flux:text class="text-sm">{{ __('Open branded quote or invoice pages to print, download a PDF, or email as an attachment.') }}</flux:text>

        <flux:input wire:model="documentEmail" type="email" :label="__('Send PDF to')" class="max-w-md" />

        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Quote --}}
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-600">
                <p class="mb-3 text-sm font-medium text-zinc-900 dark:text-white">{{ __('Quote') }}</p>
                <div class="flex flex-wrap gap-2">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        :href="route('projects.quote', $project)"
                        target="_blank"
                        :disabled="! $project->quote_amount"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <x-material-icon name="open_in_new" class="text-[1.125rem]" />
                            {{ __('View & print') }}
                        </span>
                    </flux:button>
                    <flux:button
                        variant="ghost"
                        size="sm"
                        :href="route('projects.quote.pdf', $project)"
                        :disabled="! $project->quote_amount"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <x-material-icon name="download" class="text-[1.125rem]" />
                            {{ __('PDF') }}
                        </span>
                    </flux:button>
                    <flux:button
                        variant="primary"
                        size="sm"
                        wire:click="emailQuote"
                        wire:loading.attr="disabled"
                        wire:target="emailQuote"
                        :disabled="! $project->quote_amount"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <x-material-icon name="mail" class="text-[1.125rem]" />
                            {{ __('Email PDF') }}
                        </span>
                    </flux:button>
                </div>
            </div>

            {{-- Invoice --}}
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-600">
                <p class="mb-3 text-sm font-medium text-zinc-900 dark:text-white">{{ __('Invoice') }}</p>
                <div class="flex flex-wrap gap-2">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        :href="route('projects.invoice', $project)"
                        target="_blank"
                        :disabled="! $project->invoice_amount"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <x-material-icon name="open_in_new" class="text-[1.125rem]" />
                            {{ __('View & print') }}
                        </span>
                    </flux:button>
                    <flux:button
                        variant="ghost"
                        size="sm"
                        :href="route('projects.invoice.pdf', $project)"
                        :disabled="! $project->invoice_amount"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <x-material-icon name="download" class="text-[1.125rem]" />
                            {{ __('PDF') }}
                        </span>
                    </flux:button>
                    <flux:button
                        variant="primary"
                        size="sm"
                        wire:click="emailInvoice"
                        wire:loading.attr="disabled"
                        wire:target="emailInvoice"
                        :disabled="! $project->invoice_amount"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <x-material-icon name="mail" class="text-[1.125rem]" />
                            {{ __('Email PDF') }}
                        </span>
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
