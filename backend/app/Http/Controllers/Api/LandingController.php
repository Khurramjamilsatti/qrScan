<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingFeature;
use App\Models\LandingSetting;
use App\Models\PricingPlan;
use App\Models\SitePage;
use App\Models\Testimonial;
use App\Support\LocalizedContent;
use Illuminate\Http\JsonResponse;

class LandingController extends Controller
{
    public function index(): JsonResponse
    {
        $locale = LocalizedContent::locale();

        $hero = LocalizedContent::resolve(LandingSetting::getValue('hero', $this->defaultHero()));
        $stats = LocalizedContent::resolve(LandingSetting::getValue('stats', $this->defaultStats()));
        $cta = LocalizedContent::resolve(LandingSetting::getValue('cta', $this->defaultCta()));
        $site = LocalizedContent::resolve(LandingSetting::getValue('site', $this->defaultSite()));
        $footer = LocalizedContent::resolve(LandingSetting::getValue('footer', $this->defaultFooter()));
        $sections = LocalizedContent::resolve(LandingSetting::getValue('sections', $this->defaultSections()));

        return response()->json([
            'hero' => $hero,
            'stats' => $stats,
            'features' => $this->localizedFeatures($locale),
            'pricing' => $this->localizedPricing($locale),
            'testimonials' => $this->localizedTestimonials($locale),
            'cta' => $cta,
            'site' => $site,
            'footer' => $footer,
            'site_pages' => $this->localizedSitePageNav($locale),
            'sections' => $sections,
        ]);
    }

    private function localizedFeatures(string $locale)
    {
        $features = LandingFeature::where('is_active', true)
            ->where('locale', $locale)
            ->orderBy('sort_order')
            ->get();

        if ($features->isEmpty() && $locale !== 'en') {
            $features = LandingFeature::where('is_active', true)
                ->where('locale', 'en')
                ->orderBy('sort_order')
                ->get();
        }

        return $features;
    }

    private function localizedPricing(string $locale)
    {
        $plans = PricingPlan::where('is_active', true)
            ->where('locale', $locale)
            ->orderBy('sort_order')
            ->get();

        if ($plans->isEmpty() && $locale !== 'en') {
            $plans = PricingPlan::where('is_active', true)
                ->where('locale', 'en')
                ->orderBy('sort_order')
                ->get();
        }

        return $plans;
    }

    private function localizedTestimonials(string $locale)
    {
        $items = Testimonial::where('is_active', true)
            ->where('locale', $locale)
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty() && $locale !== 'en') {
            $items = Testimonial::where('is_active', true)
                ->where('locale', 'en')
                ->orderBy('sort_order')
                ->get();
        }

        return $items;
    }

    private function localizedSitePageNav(string $locale)
    {
        $pages = SitePage::where('is_active', true)
            ->where('locale', $locale)
            ->orderBy('slug')
            ->get(['slug', 'title']);

        if ($pages->isEmpty() && $locale !== 'en') {
            $pages = SitePage::where('is_active', true)
                ->where('locale', 'en')
                ->orderBy('slug')
                ->get(['slug', 'title']);
        }

        return $pages;
    }

    private function defaultHero(): array
    {
        return [
            'badge' => ['en' => 'Launch offer — 50% off all plans', 'ar' => 'عرض الإطلاق — خصم 50% على جميع الخطط'],
            'title' => ['en' => 'Every QR tool your brand needs — in one platform', 'ar' => 'كل أدوات QR التي تحتاجها علامتك — في منصة واحدة'],
            'subtitle' => [
                'en' => 'QR codes, short links, digital cards, pages, menus, badges, tickets & scan-to-win campaigns. Create, share, and track everything from one dashboard.',
                'ar' => 'رموز QR والروابط المختصرة وبطاقات العمل والصفحات والقوائم والشارات والتذاكر وحملات امسح واربح. أنشئ وشارك وتتبع كل شيء من لوحة تحكم واحدة.',
            ],
            'cta_primary' => ['en' => 'Start free', 'ar' => 'ابدأ مجاناً'],
            'cta_secondary' => ['en' => 'See pricing', 'ar' => 'عرض الأسعار'],
        ];
    }

    private function defaultStats(): array
    {
        return [
            ['label' => ['en' => 'QR codes created', 'ar' => 'رموز QR مُنشأة'], 'value' => '2M+'],
            ['label' => ['en' => 'Links shortened', 'ar' => 'روابط مختصرة'], 'value' => '10M+'],
            ['label' => ['en' => 'Countries reached', 'ar' => 'دولة'], 'value' => '150+'],
            ['label' => ['en' => 'Uptime', 'ar' => 'وقت التشغيل'], 'value' => '99.9%'],
        ];
    }

    private function defaultCta(): array
    {
        return [
            'title' => ['en' => 'Ready to grow your brand?', 'ar' => 'هل أنت مستعد لتنمية علامتك؟'],
            'subtitle' => [
                'en' => 'Join thousands of marketers, founders, and teams using QRScan to connect offline to online.',
                'ar' => 'انضم إلى آلاف المسوقين ورواد الأعمال والفرق الذين يستخدمون QRScan للربط بين العالمين الفعلي والرقمي.',
            ],
            'button_text' => ['en' => "Get started — it's free", 'ar' => 'ابدأ الآن — مجاناً'],
        ];
    }

    private function defaultSite(): array
    {
        return [
            'name' => ['en' => 'QRScan', 'ar' => 'QRScan'],
            'tagline' => ['en' => 'Scan. Link. Connect.', 'ar' => 'امسح. اربط. تواصل.'],
            'logo_text' => ['en' => 'QRScan', 'ar' => 'QRScan'],
        ];
    }

    private function defaultFooter(): array
    {
        return [
            'tagline' => ['en' => 'Scan. Link. Connect.', 'ar' => 'امسح. اربط. تواصل.'],
            'support_email' => 'support@qrscan.digital',
        ];
    }

    private function defaultSections(): array
    {
        return [
            'features_eyebrow' => ['en' => 'Everything you need', 'ar' => 'كل ما تحتاجه'],
            'features_title' => ['en' => "Eight tools.\nOne beautiful platform.", 'ar' => "ثمانية أدوات.\nمنصة واحدة جميلة."],
            'pricing_eyebrow' => ['en' => 'Half-price launch rates', 'ar' => 'أسعار إطلاق بنصف السعر'],
            'pricing_title' => ['en' => 'Simple, honest pricing', 'ar' => 'أسعار بسيطة وشفافة'],
            'pricing_subtitle' => ['en' => 'Start free. Upgrade when you grow.', 'ar' => 'ابدأ مجاناً. ترقّ عندما تنمو.'],
            'testimonials_title' => ['en' => 'Loved by teams worldwide', 'ar' => 'محبوب من فرق حول العالم'],
        ];
    }
}
