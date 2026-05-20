<?php

use App\Http\Controllers\DigestUnsubscribeController;
use App\Http\Controllers\ProjectDocumentController;
use App\Livewire\Admin\Index as AdminIndex;
use App\Livewire\Dashboard;
use App\Livewire\Events\Index as EventsIndex;
use App\Livewire\Events\Show as EventsShow;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('digest/unsubscribe/{user}', DigestUnsubscribeController::class)
    ->middleware('signed')
    ->name('digest.unsubscribe');

Route::livewire('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('events', EventsIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('events.index');

Route::livewire('events/{event}', EventsShow::class)
    ->middleware(['auth', 'verified'])
    ->name('events.show');

Route::livewire('projects', ProjectsIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('projects.index');

Route::livewire('projects/{project}', ProjectsShow::class)
    ->middleware(['auth', 'verified'])
    ->name('projects.show');

Route::livewire('admin', AdminIndex::class)
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('projects/{project}/quote', [ProjectDocumentController::class, 'quote'])
        ->name('projects.quote');
    Route::get('projects/{project}/quote/pdf', [ProjectDocumentController::class, 'quotePdf'])
        ->name('projects.quote.pdf');
    Route::post('projects/{project}/quote/email', [ProjectDocumentController::class, 'emailQuote'])
        ->name('projects.quote.email');

    Route::get('projects/{project}/invoice', [ProjectDocumentController::class, 'invoice'])
        ->name('projects.invoice');
    Route::get('projects/{project}/invoice/pdf', [ProjectDocumentController::class, 'invoicePdf'])
        ->name('projects.invoice.pdf');
    Route::post('projects/{project}/invoice/email', [ProjectDocumentController::class, 'emailInvoice'])
        ->name('projects.invoice.email');
});

require __DIR__.'/settings.php';
