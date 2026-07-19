<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\FormController as AdminFormController;
use App\Http\Controllers\Api\Admin\LandingContentController;
use App\Http\Controllers\Api\Admin\SitePageController as AdminSitePageController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AiImageController;
use App\Http\Controllers\Api\CustomDomainController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\BusinessCardController;
use App\Http\Controllers\Api\DigitalBadgeController;
use App\Http\Controllers\Api\DigitalCertificateController;
use App\Http\Controllers\Api\DigitalEventController;
use App\Http\Controllers\Api\DigitalMenuController;
use App\Http\Controllers\Api\DigitalPageController;
use App\Http\Controllers\Api\DigitalTicketController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LandingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QrAssistantController;
use App\Http\Controllers\Api\QrCodeController;
use App\Http\Controllers\Api\QrFunnelController;
use App\Http\Controllers\Api\RedirectController;
use App\Http\Controllers\Api\ScanToWinCampaignController;
use App\Http\Controllers\Api\ShortLinkController;
use App\Http\Controllers\Api\SitePageController;
use Illuminate\Support\Facades\Route;

Route::get('/landing', [LandingController::class, 'index']);
Route::get('/pages/{slug}', [SitePageController::class, 'show']);

Route::get('/r/{slug}', [RedirectController::class, 'shortLink']);
Route::get('/qr/{code}', [RedirectController::class, 'qrCode']);
Route::get('/card/{slug}', [RedirectController::class, 'businessCard']);
Route::get('/page/{slug}', [RedirectController::class, 'digitalPage']);
Route::get('/menu/{slug}', [RedirectController::class, 'digitalMenu']);
Route::get('/invite/{slug}', [RedirectController::class, 'digitalEvent']);
Route::get('/badge/{slug}', [RedirectController::class, 'digitalBadge']);
Route::get('/certificate/{slug}', [RedirectController::class, 'digitalCertificate']);
Route::get('/verify/{certificateId}', [RedirectController::class, 'verifyCertificate']);
Route::get('/verify/{certificateId}/pdf', [RedirectController::class, 'verifyCertificatePdf']);
Route::get('/ticket/{slug}', [RedirectController::class, 'digitalTicket']);
Route::get('/win/{slug}', [RedirectController::class, 'scanToWin']);
Route::post('/win/{slug}/play', [ScanToWinCampaignController::class, 'play']);
Route::get('/form/{slug}', [FormController::class, 'publicShow']);
Route::post('/form/{slug}/submit', [FormController::class, 'submit']);
Route::get('/card/{slug}/vcard', [BusinessCardController::class, 'vcard']);

Route::post('/stripe/webhook', [BillingController::class, 'webhook']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('app.user')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/billing/config', [BillingController::class, 'config']);
        Route::post('/billing/checkout', [BillingController::class, 'checkout']);
        Route::post('/billing/confirm', [BillingController::class, 'confirm']);
        Route::post('/billing/portal', [BillingController::class, 'portal']);

    Route::get('analytics/{type}/{id}', [AnalyticsController::class, 'show']);
    Route::get('analytics/{type}/{id}/insights', [AnalyticsController::class, 'insights']);

    Route::get('qr-assistant/status', [QrAssistantController::class, 'status']);
    Route::post('qr-assistant/advise', [QrAssistantController::class, 'advise']);
    Route::get('qr-funnels/templates', [QrFunnelController::class, 'templates']);
    Route::apiResource('qr-funnels', QrFunnelController::class);
    Route::patch('qr-funnels/{qrFunnel}/publish', [QrFunnelController::class, 'togglePublish']);

    Route::apiResource('qr-codes', QrCodeController::class);
    Route::patch('qr-codes/{qrCode}/active', [QrCodeController::class, 'toggleActive']);
    Route::get('qr-codes/{qrCode}/download/{format}', [QrCodeController::class, 'download']);

    Route::apiResource('short-links', ShortLinkController::class);
    Route::patch('short-links/{shortLink}/active', [ShortLinkController::class, 'toggleActive']);
    Route::apiResource('business-cards', BusinessCardController::class);
    Route::patch('business-cards/{businessCard}/publish', [BusinessCardController::class, 'togglePublish']);
    Route::apiResource('digital-pages', DigitalPageController::class);
    Route::patch('digital-pages/{digitalPage}/publish', [DigitalPageController::class, 'togglePublish']);
    Route::apiResource('digital-menus', DigitalMenuController::class);
    Route::patch('digital-menus/{digitalMenu}/publish', [DigitalMenuController::class, 'togglePublish']);
    Route::apiResource('digital-events', DigitalEventController::class);
    Route::patch('digital-events/{digitalEvent}/publish', [DigitalEventController::class, 'togglePublish']);
    Route::apiResource('digital-badges', DigitalBadgeController::class);
    Route::patch('digital-badges/{digitalBadge}/publish', [DigitalBadgeController::class, 'togglePublish']);
    Route::post('digital-certificates/bulk-import', [DigitalCertificateController::class, 'bulkImport']);
    Route::apiResource('digital-certificates', DigitalCertificateController::class);
    Route::patch('digital-certificates/{digitalCertificate}/publish', [DigitalCertificateController::class, 'togglePublish']);
    Route::patch('digital-certificates/{digitalCertificate}/revoke', [DigitalCertificateController::class, 'revoke']);
    Route::get('digital-certificates/{digitalCertificate}/download-pdf', [DigitalCertificateController::class, 'downloadPdf']);
    Route::apiResource('digital-tickets', DigitalTicketController::class);
    Route::patch('digital-tickets/{digitalTicket}/publish', [DigitalTicketController::class, 'togglePublish']);
    Route::apiResource('scan-to-win', ScanToWinCampaignController::class)->parameters(['scan-to-win' => 'scanToWinCampaign']);
    Route::patch('scan-to-win/{scanToWinCampaign}/publish', [ScanToWinCampaignController::class, 'togglePublish']);
    Route::get('forms/{form}/submissions', [FormController::class, 'submissions']);
    Route::get('forms/{form}/submissions/export', [FormController::class, 'exportSubmissions']);
    Route::delete('forms/{form}/submissions/{submission}', [FormController::class, 'destroySubmission']);
    Route::patch('forms/{form}/publish', [FormController::class, 'togglePublish']);
    Route::apiResource('forms', FormController::class);

    Route::apiResource('custom-domains', CustomDomainController::class)->except(['show', 'update']);
    Route::post('custom-domains/{customDomain}/verify', [CustomDomainController::class, 'verify']);
    Route::get('custom-domains/{customDomain}/verification-status', [CustomDomainController::class, 'verificationStatus']);
    Route::put('custom-domains/{customDomain}/primary', [CustomDomainController::class, 'setPrimary']);
    Route::get('custom-domains/{customDomain}/dns', [CustomDomainController::class, 'dns']);

    Route::get('ai/status', [AiImageController::class, 'status']);
    Route::post('ai/generate', [AiImageController::class, 'generate']);
    Route::post('ai/upload', [AiImageController::class, 'upload']);
    });

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/user', [AdminAuthController::class, 'user']);
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/landing', [LandingContentController::class, 'index']);

        Route::put('/landing/hero', [LandingContentController::class, 'updateHero']);
        Route::put('/landing/stats', [LandingContentController::class, 'updateStats']);
        Route::put('/landing/cta', [LandingContentController::class, 'updateCta']);
        Route::put('/landing/site', [LandingContentController::class, 'updateSite']);
        Route::put('/landing/sections', [LandingContentController::class, 'updateSections']);

        Route::get('/site-pages', [AdminSitePageController::class, 'index']);
        Route::put('/site-pages/{sitePage}', [AdminSitePageController::class, 'update']);
        Route::put('/site-pages-footer', [AdminSitePageController::class, 'updateFooter']);

        Route::post('/features', [LandingContentController::class, 'storeFeature']);
        Route::put('/features/{feature}', [LandingContentController::class, 'updateFeature']);
        Route::delete('/features/{feature}', [LandingContentController::class, 'destroyFeature']);

        Route::post('/pricing-plans', [LandingContentController::class, 'storePlan']);
        Route::put('/pricing-plans/{plan}', [LandingContentController::class, 'updatePlan']);
        Route::delete('/pricing-plans/{plan}', [LandingContentController::class, 'destroyPlan']);

        Route::post('/testimonials', [LandingContentController::class, 'storeTestimonial']);
        Route::put('/testimonials/{testimonial}', [LandingContentController::class, 'updateTestimonial']);
        Route::delete('/testimonials/{testimonial}', [LandingContentController::class, 'destroyTestimonial']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}/plan', [AdminUserController::class, 'updatePlan']);

        Route::get('/forms', [AdminFormController::class, 'index']);
        Route::get('/forms/{form}', [AdminFormController::class, 'show']);
        Route::delete('/forms/{form}/submissions/{submission}', [AdminFormController::class, 'destroySubmission']);
    });
});
