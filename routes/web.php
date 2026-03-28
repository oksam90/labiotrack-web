<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\StockageController;
use App\Http\Controllers\CollecteController;
use App\Http\Controllers\DestructionController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\QrCodeController;

// ─── AUTH ──────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect('/login'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ─── ROUTES AUTHENTIFIÉES ──────────────────────────────────────────────────
Route::middleware(['auth', 'tenant'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── DÉCLARATIONS ────────────────────────────────────────────────────────
    Route::prefix('declarations')->name('declarations.')->group(function () {
        Route::get('/',            [DeclarationController::class, 'index'])->name('index');
        Route::get('/create',      [DeclarationController::class, 'create'])->name('create');
        Route::post('/',           [DeclarationController::class, 'store'])->name('store');
        Route::get('/{id}',        [DeclarationController::class, 'show'])->name('show');
        Route::get('/{id}/edit',   [DeclarationController::class, 'edit'])->name('edit');
        Route::put('/{id}',        [DeclarationController::class, 'update'])->name('update');
        Route::delete('/{id}',     [DeclarationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/qrcode',[DeclarationController::class, 'generateQr'])->name('qrcode');
    });

    // ── STOCKAGE ────────────────────────────────────────────────────────────
    Route::prefix('stockage')->name('stockage.')->group(function () {
        Route::get('/',              [StockageController::class, 'index'])->name('index');
        Route::get('/create',        [StockageController::class, 'create'])->name('create');
        Route::post('/',             [StockageController::class, 'store'])->name('store');
        Route::get('/{id}',          [StockageController::class, 'show'])->name('show');
        Route::post('/{id}/valider', [StockageController::class, 'valider'])->name('valider');
    });

    // ── COLLECTES ───────────────────────────────────────────────────────────
    Route::prefix('collectes')->name('collectes.')->group(function () {
        Route::get('/',               [CollecteController::class, 'index'])->name('index');
        Route::get('/create',         [CollecteController::class, 'create'])->name('create');
        Route::post('/',              [CollecteController::class, 'store'])->name('store');
        Route::get('/scan/{qr}',      [QrCodeController::class, 'scan'])->name('scan');
        Route::get('/{id}',           [CollecteController::class, 'show'])->name('show');
        Route::post('/{id}/valider',  [CollecteController::class, 'valider'])->name('valider');
        Route::post('/{id}/bordereau',[CollecteController::class, 'bordereau'])->name('bordereau');
    });

    // ── DESTRUCTIONS ────────────────────────────────────────────────────────
    Route::prefix('destructions')->name('destructions.')->group(function () {
        Route::get('/',                    [DestructionController::class, 'index'])->name('index');
        Route::get('/create/{collecte_id}',[DestructionController::class, 'create'])->name('create');
        Route::post('/',                   [DestructionController::class, 'store'])->name('store');
        Route::get('/{id}',                [DestructionController::class, 'show'])->name('show');
        Route::get('/{id}/certificat',     [DestructionController::class, 'certificat'])->name('certificat');
        Route::get('/{id}/certificat/pdf', [DestructionController::class, 'certificatPdf'])->name('certificat.pdf');
    });

    // ── CHECKLISTS ──────────────────────────────────────────────────────────
    Route::prefix('checklists')->name('checklists.')->group(function () {
        Route::get('/',           [ChecklistController::class, 'index'])->name('index');
        Route::get('/create',     [ChecklistController::class, 'create'])->name('create');
        Route::post('/',          [ChecklistController::class, 'store'])->name('store');
        Route::get('/historique', [ChecklistController::class, 'historique'])->name('historique');
        Route::get('/{id}',       [ChecklistController::class, 'show'])->name('show');
    });

    // ── ALERTES ─────────────────────────────────────────────────────────────
    Route::prefix('alertes')->name('alertes.')->group(function () {
        Route::get('/',           [AlerteController::class, 'index'])->name('index');
        Route::post('/tout-lire', [AlerteController::class, 'toutLire'])->name('tout-lire');
        Route::post('/{id}/lire', [AlerteController::class, 'marquerLu'])->name('lire');
    });

    // ── RAPPORTS ────────────────────────────────────────────────────────────
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/',                   [RapportController::class, 'index'])->name('index');
        Route::post('/generer',           [RapportController::class, 'generer'])->name('generer');
        Route::get('/analyse-financiere', [RapportController::class, 'analyseFinanciere'])->name('financier');
        Route::get('/{id}/pdf',           [RapportController::class, 'pdf'])->name('pdf');
    });

    // ── ADMINISTRATION STRUCTURE (admin local) ───────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin,superadmin')->group(function () {
        
        Route::get('/activites',       [AdminController::class, 'activites'])->name('activites');
        Route::get('/activites/data',  [AdminController::class, 'activitesData'])->name('activites.data');

        Route::get('/',                [AdminController::class, 'index'])->name('index');
        Route::get('/create',          [AdminController::class, 'create'])->name('create');
        Route::post('/',               [AdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit',       [AdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',            [AdminController::class, 'update'])->name('update');
        Route::delete('/{id}',         [AdminController::class, 'destroy'])->name('destroy');

        Route::prefix('utilisateurs')->name('utilisateurs.')->group(function () {
            Route::get('/',           [AdminController::class, 'utilisateurs'])->name('index');
            Route::get('/create',     [AdminController::class, 'createUser'])->name('create');
            Route::post('/',          [AdminController::class, 'storeUser'])->name('store');
            Route::get('/{id}/edit',  [AdminController::class, 'editUser'])->name('edit');
            Route::put('/{id}',       [AdminController::class, 'updateUser'])->name('update');
            Route::delete('/{id}',    [AdminController::class, 'destroyUser'])->name('destroy');
            Route::post('/{id}/toggle',[AdminController::class, 'toggleUser'])->name('toggle');
        });

        Route::get('/services',         [AdminController::class, 'services'])->name('services');
        Route::post('/services',        [AdminController::class, 'storeService'])->name('services.store');
        Route::put('/services/{id}',    [AdminController::class, 'updateService'])->name('services.update');
        Route::delete('/services/{id}', [AdminController::class, 'destroyService'])->name('services.destroy');

        Route::get('/contenants',       [AdminController::class, 'contenants'])->name('contenants');
        Route::post('/contenants',      [AdminController::class, 'storeContenant'])->name('contenants.store');
        Route::put('/contenants/{id}',  [AdminController::class, 'updateContenant'])->name('contenants.update');
    });

    // ── SUPERADMIN — Vue réseau global ──────────────────────────────────────
    Route::prefix('superadmin')->name('superadmin.')->middleware('role:superadmin,admin,collecteur,prestataire')->group(function () {
        Route::get('/',                       [SuperAdminController::class, 'index'])->name('index');
        Route::get('/etablissements',         [SuperAdminController::class, 'etablissements'])->name('etablissements');
        Route::get('/etablissements/{id}',    [SuperAdminController::class, 'etablissement'])->name('etablissement');
        Route::post('/switch-tenant/{id}',    [SuperAdminController::class, 'switchTenant'])->name('switch-tenant');
        Route::post('/reset-tenant',          [SuperAdminController::class, 'resetTenant'])->name('reset-tenant');
        Route::get('/stats-reseau',           [SuperAdminController::class, 'statsReseau'])->name('stats-reseau');
        Route::get('/comparatif',             [SuperAdminController::class, 'comparatif'])->name('comparatif');
    });

    // ── QR CODE ─────────────────────────────────────────────────────────────
    Route::get('/qrcode/local/{etablissement_id}', [QrCodeController::class, 'generateLocal'])->name('qrcode.local');
});
