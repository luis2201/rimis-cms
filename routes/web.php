<?php

use App\Http\Controllers\BulletinController;
use App\Http\Controllers\CallForProposalController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsTaxonomyController;
use App\Http\Controllers\PageBlockController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PublicInstitutionController;
use App\Http\Controllers\ResearcherSubmissionController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\SubmissionReviewController;
use App\Http\Controllers\ResearchPublicationController;
use App\Http\Controllers\PublicResearchPublicationController;
use App\Http\Controllers\Admin\ResearchPublicationController as AdminResearchPublicationController;
use App\Http\Controllers\PublicResearcherController;
use App\Http\Controllers\Admin\ResearcherDirectoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\Event;
use App\Models\News;
use App\Support\SeoService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/health', HealthController::class)->middleware('throttle:health')->name('health');
Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'researcher.verified', 'researcher.profile.complete'])->name('dashboard');

Route::get('/pagina/{slug}', [PageController::class, 'publicShow'])->name('pages.show');
Route::get('/noticias', [NewsController::class, 'publicIndex'])->name('news.index');
Route::get('/noticias/todas', [NewsController::class, 'publicAll'])->name('news.all');
Route::get('/noticias/categoria/{category:slug}', [NewsController::class, 'publicCategory'])->name('news.category');
Route::get('/noticias/{slug}', [NewsController::class, 'publicShow'])->name('news.show');
Route::get('/boletines', [BulletinController::class, 'publicIndex'])->name('bulletins.index');
Route::get('/boletines/{slug}', [BulletinController::class, 'publicShow'])->name('bulletins.show');
Route::get('/boletines/{bulletin}/pdf', [BulletinController::class, 'download'])->name('bulletins.download');
Route::get('/eventos', [EventController::class, 'publicIndex'])->name('events.index');
Route::get('/eventos/{slug}', [EventController::class, 'publicShow'])->name('events.show');
Route::get('/eventos/{event:slug}/adjunto', [EventController::class, 'downloadAttachment'])->name('events.download');
Route::get('/convocatorias', [CallForProposalController::class, 'publicIndex'])->name('calls.index');
Route::get('/convocatorias/{slug}', [CallForProposalController::class, 'publicShow'])->name('calls.show');
Route::get('/convocatorias/{call:slug}/bases', [CallForProposalController::class, 'download'])->name('calls.download');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/investigaciones', [PublicResearchPublicationController::class, 'index'])->middleware('throttle:public-search')->name('research-publications.index');
Route::get('/investigaciones/{slug}', [PublicResearchPublicationController::class, 'show'])->name('research-publications.show');
Route::get('/investigaciones/{slug}/pdf', [PublicResearchPublicationController::class, 'pdf'])->name('research-publications.pdf');
Route::get('/investigadores', [PublicResearcherController::class, 'index'])->middleware('throttle:public-search')->name('researchers.index');
Route::get('/investigadores/{slug}', [PublicResearcherController::class, 'show'])->name('researchers.show');
Route::get('/instituciones', [PublicInstitutionController::class, 'index'])->middleware('throttle:public-search')->name('institutions.index');
Route::get('/instituciones/{slug}', [PublicInstitutionController::class, 'show'])->name('institutions.show');
Route::get('/suscripciones', [SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::get('/suscripciones/enviada', [SubscriptionController::class, 'sent'])->name('subscriptions.sent');
Route::get('/suscripciones/{type}', [SubscriptionController::class, 'create'])->whereIn('type',['professional','institutional'])->name('subscriptions.create');
Route::post('/suscripciones/{type}', [SubscriptionController::class, 'store'])->whereIn('type',['professional','institutional'])->middleware('throttle:registration')->name('subscriptions.store');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'researcher.verified'])->group(function () {
    Route::put('/profile/researcher', [ProfileController::class, 'updateResearcher'])->name('profile.researcher.update');
    Route::get('/profile/{user}/curriculum', [ProfileController::class, 'downloadCv'])->middleware('throttle:download')->name('profile.cv.download');
});

Route::middleware(['auth','verified','role.researcher'])->prefix('membresia')->name('membership.')->group(function(){
    Route::get('/',[MembershipController::class,'show'])->name('show');
    Route::get('/certificacion',[MembershipController::class,'certificate'])->middleware('throttle:download')->name('certificate');
});

Route::middleware(['auth', 'verified', 'role.researcher', 'can:submissions.view-own'])
    ->prefix('investigador/aportes')->name('researcher.submissions.')->group(function () {
        Route::get('/', [ResearcherSubmissionController::class, 'index'])->name('index');
        foreach (['event', 'bulletin', 'call'] as $type) {
            $plural = ['event'=>'eventos','bulletin'=>'boletines','call'=>'convocatorias'][$type];
            $parameter = ['event'=>'event','bulletin'=>'bulletin','call'=>'call'][$type];
            $permission = ['event'=>'events.submit','bulletin'=>'bulletins.submit','call'=>'calls.submit'][$type];
            Route::get("$plural/crear", [ResearcherSubmissionController::class, $type.'Create'])->middleware("can:$permission")->name("$type.create");
            Route::post($plural, [ResearcherSubmissionController::class, $type.'Store'])->middleware("can:$permission")->name("$type.store");
            Route::get("$plural/{{$parameter}}/editar", [ResearcherSubmissionController::class, $type.'Edit'])->name("$type.edit");
            Route::put("$plural/{{$parameter}}", [ResearcherSubmissionController::class, $type.'Update'])->name("$type.update");
            Route::delete("$plural/{{$parameter}}", [ResearcherSubmissionController::class, $type.'Destroy'])->name("$type.destroy");
            Route::post("$plural/{{$parameter}}/enviar", [ResearcherSubmissionController::class, $type.'Submit'])->middleware(["can:$permission", 'throttle:submission'])->name("$type.submit");
            Route::get("$plural/{{$parameter}}/archivo", [ResearcherSubmissionController::class, $type.'Download'])->middleware('throttle:download')->name("$type.download");
        }
    });

Route::middleware(['auth','verified','role.researcher'])->prefix('miembro/publicaciones')->name('researcher.publications.')->group(function(){
 Route::get('/',[ResearchPublicationController::class,'index'])->middleware('can:research-publications.view-own')->name('index');
 Route::get('/crear',[ResearchPublicationController::class,'create'])->middleware('can:research-publications.create')->name('create'); Route::post('/',[ResearchPublicationController::class,'store'])->middleware('can:research-publications.create')->name('store');
 Route::get('/{publication}',[ResearchPublicationController::class,'show'])->middleware('can:research-publications.view-own')->name('show'); Route::get('/{publication}/editar',[ResearchPublicationController::class,'edit'])->middleware('can:research-publications.edit-own')->name('edit'); Route::put('/{publication}',[ResearchPublicationController::class,'update'])->middleware('can:research-publications.edit-own')->name('update'); Route::delete('/{publication}',[ResearchPublicationController::class,'destroy'])->middleware('can:research-publications.delete-own')->name('destroy'); Route::post('/{publication}/enviar',[ResearchPublicationController::class,'submit'])->middleware(['can:research-publications.submit','throttle:submission'])->name('submit'); Route::get('/{publication}/pdf',[ResearchPublicationController::class,'pdf'])->middleware(['can:research-publications.download-own','throttle:download'])->name('pdf');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('researchers',[ResearcherDirectoryController::class,'index'])->middleware('can:researchers.view')->name('researchers.index');
    Route::get('researchers/{profile}/edit',[ResearcherDirectoryController::class,'edit'])->middleware('can:researchers.edit')->name('researchers.edit');
    Route::put('researchers/{profile}',[ResearcherDirectoryController::class,'update'])->middleware('can:researchers.edit')->name('researchers.update');
    Route::patch('researchers/{profile}/visibility',[ResearcherDirectoryController::class,'visibility'])->middleware('can:researchers.manage-visibility')->name('researchers.visibility');
    Route::get('research-publications/{publication}/edit',[AdminResearchPublicationController::class,'edit'])->middleware('can:research-publications.edit')->name('research-publications.edit');
    Route::put('research-publications/{publication}',[AdminResearchPublicationController::class,'update'])->middleware('can:research-publications.edit')->name('research-publications.update');
    Route::get('submissions', [SubmissionReviewController::class, 'index'])->middleware('can:submissions.view')->name('submissions.index');
    Route::get('submissions/{type}/{id}', [SubmissionReviewController::class, 'show'])->middleware('can:submissions.view')->name('submissions.show');
    Route::get('submissions/{type}/{id}/download', [SubmissionReviewController::class, 'download'])->middleware(['can:submissions.view', 'throttle:download'])->name('submissions.download');
    Route::patch('submissions/{type}/{id}/start-review', [SubmissionReviewController::class, 'startReview'])->middleware('can:submissions.review')->name('submissions.start-review');
    Route::patch('submissions/{type}/{id}/observe', [SubmissionReviewController::class, 'observe'])->middleware('can:submissions.observe')->name('submissions.observe');
    Route::patch('submissions/{type}/{id}/approve', [SubmissionReviewController::class, 'approve'])->middleware('can:submissions.approve')->name('submissions.approve');
    Route::patch('submissions/{type}/{id}/reject', [SubmissionReviewController::class, 'reject'])->middleware('can:submissions.reject')->name('submissions.reject');
    Route::patch('submissions/{type}/{id}/publish', [SubmissionReviewController::class, 'publish'])->middleware('can:submissions.publish')->name('submissions.publish');
    Route::patch('submissions/{type}/{id}/unpublish', [SubmissionReviewController::class, 'unpublish'])->middleware('can:submissions.publish')->name('submissions.unpublish');
    Route::get('subscriptions',[AdminSubscriptionController::class,'index'])->middleware('can:subscriptions.view')->name('subscriptions.index');
    Route::get('subscriptions/{subscription}/edit',[AdminSubscriptionController::class,'edit'])->middleware('can:subscriptions.edit')->name('subscriptions.edit');
    Route::put('subscriptions/{subscription}',[AdminSubscriptionController::class,'update'])->middleware('can:subscriptions.edit')->name('subscriptions.update');
    Route::get('subscriptions/{subscription}',[AdminSubscriptionController::class,'show'])->middleware('can:subscriptions.view')->name('subscriptions.show');
    Route::patch('subscriptions/{subscription}/review',[AdminSubscriptionController::class,'startReview'])->middleware('can:subscriptions.review')->name('subscriptions.review');
    Route::patch('subscriptions/{subscription}/approve',[AdminSubscriptionController::class,'approve'])->middleware('can:subscriptions.approve')->name('subscriptions.approve');
    Route::patch('subscriptions/{subscription}/reject',[AdminSubscriptionController::class,'reject'])->middleware('can:subscriptions.reject')->name('subscriptions.reject');
    Route::get('settings/mail', [SiteSettingController::class, 'edit'])->middleware('can:settings.view')->name('settings.mail.edit');
    Route::put('settings/mail', [SiteSettingController::class, 'update'])->middleware('can:settings.edit')->name('settings.mail.update');
    Route::post('settings/mail/test', [SiteSettingController::class, 'sendTest'])->middleware('can:settings.edit')->name('settings.mail.test');

    Route::get('calls', [CallForProposalController::class, 'index'])->middleware('can:calls.view')->name('calls.index');
    Route::get('calls/create', [CallForProposalController::class, 'create'])->middleware('can:calls.create')->name('calls.create');
    Route::post('calls', [CallForProposalController::class, 'store'])->middleware('can:calls.create')->name('calls.store');
    Route::get('calls/{call}/edit', [CallForProposalController::class, 'edit'])->middleware('can:calls.edit')->name('calls.edit');
    Route::put('calls/{call}', [CallForProposalController::class, 'update'])->middleware('can:calls.edit')->name('calls.update');
    Route::delete('calls/{call}', [CallForProposalController::class, 'destroy'])->middleware('can:calls.delete')->name('calls.destroy');
    Route::patch('calls/{call}/publish', [CallForProposalController::class, 'publish'])->middleware('can:calls.publish')->name('calls.publish');
    Route::patch('calls/{call}/unpublish', [CallForProposalController::class, 'unpublish'])->middleware('can:calls.publish')->name('calls.unpublish');

    Route::get('events', [EventController::class, 'index'])->middleware('can:events.view')->name('events.index');
    Route::get('events/create', [EventController::class, 'create'])->middleware('can:events.create')->name('events.create');
    Route::post('events', [EventController::class, 'store'])->middleware('can:events.create')->name('events.store');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->middleware('can:events.edit')->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->middleware('can:events.edit')->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->middleware('can:events.delete')->name('events.destroy');
    Route::patch('events/{event}/publish', [EventController::class, 'publish'])->middleware('can:events.publish')->name('events.publish');
    Route::patch('events/{event}/unpublish', [EventController::class, 'unpublish'])->middleware('can:events.publish')->name('events.unpublish');

    Route::get('bulletins', [BulletinController::class, 'index'])->middleware('can:bulletins.view')->name('bulletins.index');
    Route::get('bulletins/create', [BulletinController::class, 'create'])->middleware('can:bulletins.create')->name('bulletins.create');
    Route::post('bulletins', [BulletinController::class, 'store'])->middleware('can:bulletins.create')->name('bulletins.store');
    Route::get('bulletins/{bulletin}/edit', [BulletinController::class, 'edit'])->middleware('can:bulletins.edit')->name('bulletins.edit');
    Route::put('bulletins/{bulletin}', [BulletinController::class, 'update'])->middleware('can:bulletins.edit')->name('bulletins.update');
    Route::delete('bulletins/{bulletin}', [BulletinController::class, 'destroy'])->middleware('can:bulletins.delete')->name('bulletins.destroy');
    Route::patch('bulletins/{bulletin}/publish', [BulletinController::class, 'publish'])->middleware('can:bulletins.publish')->name('bulletins.publish');
    Route::patch('bulletins/{bulletin}/unpublish', [BulletinController::class, 'unpublish'])->middleware('can:bulletins.publish')->name('bulletins.unpublish');

    Route::get('news', [NewsController::class, 'index'])->middleware('can:posts.view')->name('news.index');
    Route::get('news/create', [NewsController::class, 'create'])->middleware('can:posts.create')->name('news.create');
    Route::post('news', [NewsController::class, 'store'])->middleware('can:posts.create')->name('news.store');
    Route::get('news/taxonomies', [NewsTaxonomyController::class, 'index'])->middleware('can:posts.view')->name('news.taxonomies');
    Route::post('news/categories', [NewsTaxonomyController::class, 'storeCategory'])->middleware('can:posts.create')->name('news.categories.store');
    Route::put('news/categories/{category}', [NewsTaxonomyController::class, 'updateCategory'])->middleware('can:posts.edit')->name('news.categories.update');
    Route::delete('news/categories/{category}', [NewsTaxonomyController::class, 'destroyCategory'])->middleware('can:posts.delete')->name('news.categories.destroy');
    Route::post('news/tags', [NewsTaxonomyController::class, 'storeTag'])->middleware('can:posts.create')->name('news.tags.store');
    Route::delete('news/tags/{tag}', [NewsTaxonomyController::class, 'destroyTag'])->middleware('can:posts.delete')->name('news.tags.destroy');
    Route::get('news/{news}/edit', [NewsController::class, 'edit'])->middleware('can:posts.edit')->name('news.edit');
    Route::put('news/{news}', [NewsController::class, 'update'])->middleware('can:posts.edit')->name('news.update');
    Route::delete('news/{news}', [NewsController::class, 'destroy'])->middleware('can:posts.delete')->name('news.destroy');
    Route::patch('news/{news}/publish', [NewsController::class, 'publish'])->middleware('can:posts.publish')->name('news.publish');
    Route::patch('news/{news}/unpublish', [NewsController::class, 'unpublish'])->middleware('can:posts.publish')->name('news.unpublish');
    Route::patch('news/{news}/feature', [NewsController::class, 'feature'])->middleware('can:posts.publish')->name('news.feature');

    Route::get('pages', [PageController::class, 'index'])->middleware('can:pages.view')->name('pages.index');
    Route::get('pages/create', [PageController::class, 'create'])->middleware('can:pages.create')->name('pages.create');
    Route::post('pages', [PageController::class, 'store'])->middleware('can:pages.create')->name('pages.store');
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->middleware('can:pages.edit')->name('pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->middleware('can:pages.edit')->name('pages.update');
    Route::delete('pages/{page}', [PageController::class, 'destroy'])->middleware('can:pages.delete')->name('pages.destroy');
    Route::patch('pages/{page}/publish', [PageController::class, 'publish'])->middleware('can:pages.publish')->name('pages.publish');
    Route::patch('pages/{page}/unpublish', [PageController::class, 'unpublish'])->middleware('can:pages.publish')->name('pages.unpublish');
    Route::post('pages/{page}/blocks', [PageBlockController::class, 'store'])->middleware('can:pages.edit')->name('pages.blocks.store');
    Route::put('pages/{page}/blocks/{pageBlock}', [PageBlockController::class, 'update'])->middleware('can:pages.edit')->name('pages.blocks.update');
    Route::delete('pages/{page}/blocks/{pageBlock}', [PageBlockController::class, 'destroy'])->middleware('can:pages.edit')->name('pages.blocks.destroy');
    Route::patch('pages/{page}/blocks/{pageBlock}/move/{direction}', [PageBlockController::class, 'move'])->middleware('can:pages.edit')->name('pages.blocks.move');
    Route::get('seo', [SeoController::class, 'edit'])->middleware('can:seo.view')->name('seo.edit');
    Route::put('seo', [SeoController::class, 'update'])->middleware('can:seo.edit')->name('seo.update');

    Route::get('menus', [MenuController::class, 'index'])->middleware('can:menus.view')->name('menus.index');
    Route::get('menus/create', [MenuController::class, 'create'])->middleware('can:menus.create')->name('menus.create');
    Route::post('menus', [MenuController::class, 'store'])->middleware('can:menus.create')->name('menus.store');
    Route::get('menus/{menu}', [MenuController::class, 'show'])->middleware('can:menus.view')->name('menus.show');
    Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->middleware('can:menus.edit')->name('menus.edit');
    Route::put('menus/{menu}', [MenuController::class, 'update'])->middleware('can:menus.edit')->name('menus.update');
    Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->middleware('can:menus.delete')->name('menus.destroy');
    Route::post('menus/{menu}/items', [MenuItemController::class, 'store'])->middleware('can:menus.create')->name('menus.items.store');
    Route::put('menus/{menu}/items/{menuItem}', [MenuItemController::class, 'update'])->middleware('can:menus.edit')->name('menus.items.update');
    Route::delete('menus/{menu}/items/{menuItem}', [MenuItemController::class, 'destroy'])->middleware('can:menus.delete')->name('menus.items.destroy');
    Route::patch('menus/{menu}/items/{menuItem}/move/{direction}', [MenuItemController::class, 'move'])->middleware('can:menus.edit')->name('menus.items.move');

    Route::get('users', [UserController::class, 'index'])
        ->middleware('can:users.view')
        ->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('can:users.create')
        ->name('users.create');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('can:users.create')
        ->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('can:users.edit')
        ->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])
        ->middleware('can:users.edit')
        ->name('users.update');
    Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])
        ->middleware('can:users.delete')
        ->name('users.deactivate');
    Route::patch('users/{user}/activate', [UserController::class, 'activate'])
        ->middleware('can:users.edit')
        ->name('users.activate');

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('can:roles.view')
        ->name('roles.index');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('can:roles.edit')
        ->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('can:roles.edit')
        ->name('roles.update');

    Route::get('media-files', [MediaFileController::class, 'index'])
        ->middleware('can:media.view')
        ->name('media-files.index');
    Route::get('media-files/editor/images', [MediaFileController::class, 'editorImages'])
        ->middleware('can:media.view')
        ->name('media-files.editor.images');
    Route::post('media-files/editor/upload', [MediaFileController::class, 'editorUpload'])
        ->middleware('can:media.create')
        ->name('media-files.editor.upload');
    Route::get('media-files/create', [MediaFileController::class, 'create'])
        ->middleware('can:media.create')
        ->name('media-files.create');
    Route::post('media-files', [MediaFileController::class, 'store'])
        ->middleware('can:media.create')
        ->name('media-files.store');
    Route::delete('media-files/bulk-destroy', [MediaFileController::class, 'bulkDestroy'])
        ->middleware('can:media.delete')
        ->name('media-files.bulk-destroy');
    Route::get('media-files/{media_file}', [MediaFileController::class, 'show'])
        ->middleware('can:media.view')
        ->name('media-files.show');
    Route::get('media-files/{media_file}/edit', [MediaFileController::class, 'edit'])
        ->middleware('can:media.edit')
        ->name('media-files.edit');
    Route::match(['put', 'patch'], 'media-files/{media_file}', [MediaFileController::class, 'update'])
        ->middleware('can:media.edit')
        ->name('media-files.update');
    Route::delete('media-files/{media_file}', [MediaFileController::class, 'destroy'])
        ->middleware('can:media.delete')
        ->name('media-files.destroy');
});

Route::get('/test-settings', function () {
    return settings()->site_name;
});

require __DIR__.'/auth.php';
