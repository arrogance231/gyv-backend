<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignCategoryController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

// Public Endpoints (Accessible by front-end website)
Route::post('/login', [AuthController::class, 'login']);
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);
Route::get('/pages/{slug}/preview', [PageController::class, 'previewPage'])->name('preview.page');

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

// Campaigns & Categories (Public)
Route::get('/campaigns', [CampaignController::class, 'index']);
Route::get('/campaigns/{slug}', [CampaignController::class, 'show']);
Route::get('/campaign-categories', [CampaignCategoryController::class, 'index']);
Route::get('/campaign-categories/{slug}/campaigns', [CampaignCategoryController::class, 'getCategoryCampaigns']);

// Protected Endpoints (Requires valid Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Pages & Sections editing
    Route::get('/pages', [PageController::class, 'index']);
    Route::put('/pages/{id}', [PageController::class, 'update']);
    Route::put('/sections/{id}', [PageController::class, 'updateSection']);
    Route::post('/sections', [PageController::class, 'storeSection']);
    Route::delete('/sections/{id}', [PageController::class, 'destroySection']);

    // Draft/Publish Workflow & Version History
    Route::post('/pages/{id}/publish', [PageController::class, 'publish']);
    Route::get('/pages/{slug}/preview-url', [PageController::class, 'getPreviewUrl']);
    Route::get('/pages/{id}/versions', [PageController::class, 'versions']);
    Route::post('/versions/{id}/restore', [PageController::class, 'restoreVersion']);

    // Campaigns CRUD & Assignment Management
    Route::post('/campaigns', [CampaignController::class, 'store']);
    Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);
    Route::post('/campaigns/{id}/restore', [CampaignController::class, 'restore']);
    Route::post('/campaigns/{id}/categories', [CampaignCategoryController::class, 'assignCategory']);
    Route::delete('/campaigns/{id}/categories/{categoryId}', [CampaignCategoryController::class, 'removeCategory']);

    // Audit logs feed
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    // Articles CRUD
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);

    // Events CRUD
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);

    // Media management
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);

    // Settings management
    Route::put('/settings', [SettingController::class, 'update']);

    // System User management & Permission Overrides
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/permissions', [UserController::class, 'permissions']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
