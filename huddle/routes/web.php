<?php

use App\Http\Controllers\DigestUnsubscribeController;
use App\Http\Controllers\UserDataExportController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\ProjectImageController;
use App\Http\Controllers\ProjectReportController;
use App\Http\Controllers\WikiAssetController;
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
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Users\Show as UsersShow;
use App\Livewire\Wiki\Edit as WikiEdit;
use App\Livewire\Wiki\Show as WikiShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::view('privacy', 'pages.privacy')->name('privacy.show');

Route::get('digest/unsubscribe/{user}', DigestUnsubscribeController::class)
    ->middleware('signed')
    ->name('digest.unsubscribe');

Route::middleware(['auth'])->group(function () {
    Route::get('settings/data-export', UserDataExportController::class)->name('user-data.export');
});

Route::middleware(['auth', 'verified', 'privacy.accepted'])->group(function () {
Route::livewire('dashboard', Dashboard::class)->name('dashboard');

Route::livewire('members', MembersIndex::class)->name('members.index');

Route::livewire('forms', FormsIndex::class)->name('forms.index');

Route::middleware(['mentor'])->prefix('forms/manage')->name('forms.manage.')->group(function () {
    Route::livewire('/', FormsManageIndex::class)->name('index');
    Route::livewire('/create', FormsManageEdit::class)->name('create');
    Route::livewire('/{form}/edit', FormsManageEdit::class)->name('edit');
    Route::livewire('/{form}/submissions', FormsManageSubmissions::class)->name('submissions');
});

Route::livewire('forms/{form}', FormsTake::class)->name('forms.take');

Route::livewire('events', EventsIndex::class)->name('events.index');

Route::livewire('events/{event}', EventsShow::class)->name('events.show');

Route::livewire('projects', ProjectsIndex::class)->name('projects.index');

Route::livewire('projects/{project}', ProjectsShow::class)->name('projects.show');

Route::livewire('reports', ReportsIndex::class)->name('reports.index');

Route::livewire('admin', AdminIndex::class)->middleware(['admin'])->name('admin.index');

Route::livewire('mentors', MentorsIndex::class)->middleware(['mentor'])->name('mentors.index');

Route::livewire('users/{user}', UsersShow::class)->name('users.show');

Route::middleware(['mentor'])->group(function () {
    Route::livewire('wiki/edit/{path?}', WikiEdit::class)
        ->where('path', '.*')
        ->name('wiki.edit');
    Route::post('wiki/upload-image', WikiImageUploadController::class)->name('wiki.upload-image');
});

Route::get('wiki-file/{path}', WikiAssetController::class)
    ->where('path', '.*')
    ->name('wiki.asset');

Route::livewire('wiki/{path?}', WikiShow::class)
    ->where('path', '.*')
    ->name('wiki.show');

Route::get('projects/{project}/images/{projectImage}', ProjectImageController::class)
    ->name('projects.image');

Route::get('reports/projects-status', [ProjectReportController::class, 'projectsStatus'])
    ->name('reports.projects-status');
Route::get('reports/projects-status/pdf', [ProjectReportController::class, 'projectsStatusPdf'])
    ->name('reports.projects-status.pdf');
Route::post('reports/projects-status/email', [ProjectReportController::class, 'emailProjectsStatus'])
    ->name('reports.projects-status.email');

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
