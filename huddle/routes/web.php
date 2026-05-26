<?php

use App\Http\Controllers\DigestUnsubscribeController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\WikiImageUploadController;
use App\Livewire\Admin\Index as AdminIndex;
use App\Livewire\Dashboard;
use App\Livewire\Events\Index as EventsIndex;
use App\Livewire\Events\Show as EventsShow;
use App\Livewire\Forms\Index as FormsIndex;
use App\Livewire\Forms\Manage\Edit as FormsManageEdit;
use App\Livewire\Forms\Manage\Index as FormsManageIndex;
use App\Livewire\Forms\Manage\Submissions as FormsManageSubmissions;
use App\Livewire\Forms\Take as FormsTake;
use App\Livewire\Members\Index as MembersIndex;
use App\Livewire\Mentors\Index as MentorsIndex;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Livewire\Users\Show as UsersShow;
use App\Livewire\Wiki\Edit as WikiEdit;
use App\Livewire\Wiki\Show as WikiShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('digest/unsubscribe/{user}', DigestUnsubscribeController::class)
    ->middleware('signed')
    ->name('digest.unsubscribe');

Route::livewire('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('members', MembersIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('members.index');

Route::livewire('forms', FormsIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('forms.index');

Route::middleware(['auth', 'verified', 'mentor'])->prefix('forms/manage')->name('forms.manage.')->group(function () {
    Route::livewire('/', FormsManageIndex::class)->name('index');
    Route::livewire('/create', FormsManageEdit::class)->name('create');
    Route::livewire('/{form}/edit', FormsManageEdit::class)->name('edit');
    Route::livewire('/{form}/submissions', FormsManageSubmissions::class)->name('submissions');
});

Route::livewire('forms/{form}', FormsTake::class)
    ->middleware(['auth', 'verified'])
    ->name('forms.take');

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

Route::livewire('mentors', MentorsIndex::class)
    ->middleware(['auth', 'verified', 'mentor'])
    ->name('mentors.index');

Route::livewire('users/{user}', UsersShow::class)
    ->middleware(['auth', 'verified'])
    ->name('users.show');

Route::middleware(['auth', 'verified', 'mentor'])->group(function () {
    Route::livewire('wiki/edit/{path?}', WikiEdit::class)
        ->where('path', '.*')
        ->name('wiki.edit');
    Route::post('wiki/upload-image', WikiImageUploadController::class)->name('wiki.upload-image');
});

Route::livewire('wiki/{path?}', WikiShow::class)
    ->where('path', '.*')
    ->middleware(['auth', 'verified'])
    ->name('wiki.show');

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
