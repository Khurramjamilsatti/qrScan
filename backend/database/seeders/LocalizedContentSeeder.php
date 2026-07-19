<?php

namespace Database\Seeders;

use App\Models\LandingFeature;
use App\Models\LandingSetting;
use App\Models\PricingPlan;
use App\Models\SitePage;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class LocalizedContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBilingualSettings();
        $this->seedArabicFeatures();
        $this->seedArabicPricing();
        $this->seedArabicTestimonials();
        $this->seedArabicSitePages();
    }

    private function seedBilingualSettings(): void
    {
        LandingSetting::setValue('site', [
            'name' => ['en' => 'QRScan', 'ar' => 'QRScan'],
            'tagline' => ['en' => 'Scan. Link. Connect.', 'ar' => 'امسح. اربط. تواصل.'],
            'logo_text' => ['en' => 'QRScan', 'ar' => 'QRScan'],
        ]);

        LandingSetting::setValue('hero', [
            'badge' => ['en' => 'Launch offer — 50% off all plans', 'ar' => 'عرض الإطلاق — خصم 50% على جميع الخطط'],
            'title' => ['en' => 'Every QR tool your brand needs — in one platform', 'ar' => 'كل أدوات QR التي تحتاجها علامتك — في منصة واحدة'],
            'subtitle' => [
                'en' => 'QR codes, short links, digital cards, pages, menus, badges, tickets & scan-to-win campaigns. Create, share, and track everything from one dashboard.',
                'ar' => 'رموز QR والروابط المختصرة وبطاقات العمل والصفحات والقوائم والشارات والتذاكر وحملات امسح واربح. أنشئ وشارك وتتبع كل شيء من لوحة تحكم واحدة.',
            ],
            'cta_primary' => ['en' => 'Start free', 'ar' => 'ابدأ مجاناً'],
            'cta_secondary' => ['en' => 'See pricing', 'ar' => 'عرض الأسعار'],
        ]);

        LandingSetting::setValue('footer', [
            'tagline' => ['en' => 'Scan. Link. Connect.', 'ar' => 'امسح. اربط. تواصل.'],
            'support_email' => 'support@qrscan.digital',
        ]);

        LandingSetting::setValue('stats', [
            ['label' => ['en' => 'QR codes created', 'ar' => 'رموز QR مُنشأة'], 'value' => '2M+'],
            ['label' => ['en' => 'Links shortened', 'ar' => 'روابط مختصرة'], 'value' => '10M+'],
            ['label' => ['en' => 'Countries reached', 'ar' => 'دولة'], 'value' => '150+'],
            ['label' => ['en' => 'Uptime', 'ar' => 'وقت التشغيل'], 'value' => '99.9%'],
        ]);

        LandingSetting::setValue('cta', [
            'title' => ['en' => 'Ready to grow your brand?', 'ar' => 'هل أنت مستعد لتنمية علامتك؟'],
            'subtitle' => [
                'en' => 'Join thousands of marketers, founders, and teams using QRScan to connect offline to online.',
                'ar' => 'انضم إلى آلاف المسوقين ورواد الأعمال والفرق الذين يستخدمون QRScan للربط بين العالمين الفعلي والرقمي.',
            ],
            'button_text' => ['en' => "Get started — it's free", 'ar' => 'ابدأ الآن — مجاناً'],
        ]);

        LandingSetting::setValue('sections', [
            'features_eyebrow' => ['en' => 'Everything you need', 'ar' => 'كل ما تحتاجه'],
            'features_title' => ['en' => "Eight tools.\nOne beautiful platform.", 'ar' => "ثمانية أدوات.\nمنصة واحدة جميلة."],
            'pricing_eyebrow' => ['en' => 'Half-price launch rates', 'ar' => 'أسعار إطلاق بنصف السعر'],
            'pricing_title' => ['en' => 'Simple, honest pricing', 'ar' => 'أسعار بسيطة وشفافة'],
            'pricing_subtitle' => ['en' => 'Start free. Upgrade when you grow.', 'ar' => 'ابدأ مجاناً. ترقّ عندما تنمو.'],
            'testimonials_title' => ['en' => 'Loved by teams worldwide', 'ar' => 'محبوب من فرق حول العالم'],
        ]);
    }

    private function seedArabicFeatures(): void
    {
        foreach ($this->arabicFeatures() as $feature) {
            LandingFeature::updateOrCreate(
                ['locale' => 'ar', 'sort_order' => $feature['sort_order']],
                $feature
            );
        }
    }

    private function seedArabicPricing(): void
    {
        foreach ($this->arabicPricingPlans() as $plan) {
            PricingPlan::updateOrCreate(
                ['slug' => $plan['slug'], 'locale' => 'ar'],
                $plan
            );
        }
    }

    private function seedArabicTestimonials(): void
    {
        foreach ($this->arabicTestimonials() as $testimonial) {
            Testimonial::updateOrCreate(
                ['locale' => 'ar', 'sort_order' => $testimonial['sort_order']],
                $testimonial
            );
        }
    }

    private function seedArabicSitePages(): void
    {
        foreach ($this->arabicSitePages() as $page) {
            SitePage::updateOrCreate(
                ['slug' => $page['slug'], 'locale' => 'ar'],
                $page
            );
        }
    }

    /** @return list<array<string, mixed>> */
    private function arabicFeatures(): array
    {
        return [
            [
                'locale' => 'ar',
                'title' => 'رموز QR',
                'subtitle' => 'ديناميكية وقابلة للتتبع',
                'description' => 'أنشئ رموز QR مخصصة يمكنك تعديلها في أي وقت — حتى بعد الطباعة.',
                'items' => ['تعديل ديناميكي بعد الطباعة', 'تحليلات مسح فورية', 'تخصيص الشعار والألوان', 'تنزيل SVG و PNG'],
                'icon' => 'qr',
                'color' => '#10b981',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'title' => 'الروابط المختصرة',
                'subtitle' => 'مخصصة وقابلة للقياس',
                'description' => 'اختصر الروابط مع روابط مخصصة وتتبع UTM وبيانات النقرات الجغرافية.',
                'items' => ['روابط وأسماء مخصصة', 'تتبع النقرات مع البيانات الجغرافية', 'إدارة إعادة التوجيه', 'دعم معاملات UTM'],
                'icon' => 'link',
                'color' => '#f59e0b',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'title' => 'بطاقات العمل الرقمية',
                'subtitle' => 'ملفات قابلة للمشاركة',
                'description' => 'استبدل البطاقات الورقية بملفات رقمية جميلة ورموز QR تلقائية.',
                'items' => ['روابط ملف شخصي مخصصة', 'تصدير vCard وجهات الاتصال', 'روابط اجتماعية وصورة', 'رمز QR تلقائي'],
                'icon' => 'card',
                'color' => '#8b5cf6',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'title' => 'الصفحات الرقمية',
                'subtitle' => 'صفحات هبوط وفعاليات',
                'description' => 'صفحات هبوط ومحافظ وفعاليات من قوالب مع معارض وتقويمات وروابط اجتماعية.',
                'items' => ['4 قوالب', 'معرض وتقويم', 'كتل تواصل', 'أنماط QR مخصصة'],
                'icon' => 'page',
                'color' => '#e8b84a',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'title' => 'القوائم الرقمية',
                'subtitle' => 'قوائم مطاعم QR',
                'description' => 'قوائم جوال جميلة مع أقسام وصور ووسوم غذائية ومشاركة QR فورية.',
                'items' => ['قوائم متعددة الأقسام', 'صور الأطباق', 'وسوم غذائية', 'عملات متعددة'],
                'icon' => 'menu',
                'color' => '#e8655a',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'title' => 'الشارات الرقمية',
                'subtitle' => 'شهادات وجوائز',
                'description' => 'أصدر شارات وشهادات رقمية قابلة للتحقق مع مهارات وتواريخ وتحقق QR.',
                'items' => ['3 قوالب شارات', 'مهارات وانتهاء', 'رابط تحقق', 'اعتمادات QR'],
                'icon' => 'badge',
                'color' => '#6b4fa0',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'title' => 'التذاكر الرقمية',
                'subtitle' => 'دخول الفعاليات',
                'description' => 'أنشئ تذاكر فعاليات قابلة للمسح مع مقاعد وباركود وتصاميم حفلات ومؤتمرات.',
                'items' => ['4 أنماط تذاكر', 'مقعد وباركود', 'نطاق تاريخ صالح', 'QR للتسجيل'],
                'icon' => 'ticket',
                'color' => '#e8655a',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'title' => 'امسح واربح',
                'subtitle' => 'حملات جوائز',
                'description' => 'شغّل حملات QR تفاعلية مع مجموعات جوائز وحدود لعب وكشف فوري أو عجلة.',
                'items' => ['محرر مجموعة الجوائز', 'حدود اللعب اليومية', 'رسائل فوز/خسارة', 'تحليلات الحملة'],
                'icon' => 'win',
                'color' => '#e8b84a',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function arabicPricingPlans(): array
    {
        return [
            [
                'locale' => 'ar',
                'name' => 'مجاني',
                'slug' => 'free',
                'price' => 0,
                'billing_period' => 'month',
                'features' => ['رمز QR واحد', '3 روابط مختصرة', 'بطاقة عمل واحدة', '100 مسح/شهر', 'بدون تحليلات'],
                'limits' => ['qr_codes' => 1, 'short_links' => 3, 'business_cards' => 1, 'scans' => 100],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'locale' => 'ar',
                'name' => 'مبتدئ',
                'slug' => 'starter',
                'price' => 6,
                'billing_period' => 'month',
                'features' => ['10 رموز QR', 'روابط غير محدودة', '5 بطاقات عمل', '5000 مسح/شهر', 'تحليلات أساسية'],
                'limits' => ['qr_codes' => 10, 'short_links' => -1, 'business_cards' => 5, 'scans' => 5000],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'locale' => 'ar',
                'name' => 'احترافي',
                'slug' => 'pro',
                'price' => 20,
                'billing_period' => 'month',
                'features' => ['رموز QR غير محدودة', 'نطاق مخصص', 'بطاقات غير محدودة', '50000 مسح/شهر', 'تحليلات كاملة'],
                'limits' => ['qr_codes' => -1, 'short_links' => -1, 'business_cards' => -1, 'scans' => 50000],
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'locale' => 'ar',
                'name' => 'أعمال',
                'slug' => 'business',
                'price' => 50,
                'billing_period' => 'month',
                'features' => ['علامة بيضاء', 'مقاعد فريق', 'وصول API', 'مسح غير محدود', 'دعم أولوية'],
                'limits' => ['qr_codes' => -1, 'short_links' => -1, 'business_cards' => -1, 'scans' => -1],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function arabicTestimonials(): array
    {
        return [
            [
                'locale' => 'ar',
                'name' => 'سارة الشمري',
                'role' => 'مديرة التسويق',
                'company' => 'استوديو بلوم',
                'content' => 'QRScan حل محل ثلاث أدوات لدينا. رموز QR الديناميكية وحدها وفرت ميزانية الطباعة.',
                'avatar' => null,
                'rating' => 5,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'name' => 'مازن العتيبي',
                'role' => 'مؤسس',
                'company' => 'لونش باد',
                'content' => 'روابط الفعاليات المختصرة مع تتبع UTM أعطتنا وضوحاً لم نحصل عليه من قبل.',
                'avatar' => null,
                'rating' => 5,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'name' => 'ليلى الحربي',
                'role' => 'قائدة المبيعات',
                'company' => 'نكسس',
                'content' => 'بطاقات العمل الرقمية مع رموز QR — فريقنا يحبها في المؤتمرات.',
                'avatar' => null,
                'rating' => 5,
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function arabicSitePages(): array
    {
        return [
            [
                'locale' => 'ar',
                'slug' => 'support',
                'title' => 'الدعم',
                'intro' => 'نحن هنا لمساعدتك في الاستفادة القصوى من QRScan.',
                'content' => "هل تحتاج مساعدة في رموز QR أو الصفحات الرقمية أو التذاكر أو حسابك؟\n\nتصفح مواردنا أو تواصل مع الفريق — نرد عادة خلال يوم عمل واحد.\n\n**مواضيع شائعة**\n- إعداد أول رمز QR\n- ربط نطاق مخصص\n- نشر الصفحات والقوائم والشارات والتذاكر\n- حملات امسح واربح\n- الفوترة وترقية الخطة",
                'contact_info' => ['email' => 'support@qrscan.digital', 'hours' => 'الإثنين–الجمعة، 9 ص–6 م UTC'],
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'slug' => 'contact',
                'title' => 'تواصل معنا',
                'intro' => 'أسئلة أو شراكات أو خطط مؤسسات — يسعدنا سماعك.',
                'content' => "أرسل لنا رسالة وسيعود فريقنا إليك قريباً.\n\nللمشاكل العاجلة في الحساب، راسل الدعم مباشرة. للمبيعات والشراكات، استخدم بيانات التواصل أدناه.",
                'contact_info' => [
                    'email' => 'hello@qrscan.digital',
                    'phone' => '+1 (555) 012-3456',
                    'address' => 'QRScan Inc., 100 Market Street, San Francisco, CA 94105',
                    'hours' => 'الإثنين–الجمعة، 9 ص–6 م UTC',
                ],
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'slug' => 'privacy',
                'title' => 'سياسة الخصوصية',
                'intro' => 'آخر تحديث: يونيو 2026',
                'content' => "**المعلومات التي نجمعها**\nنجمع معلومات الحساب (الاسم، البريد) وبيانات الاستخدام (مسح، نقرات، مشاهدات) وبيانات تحليلات (الدولة، المصدر) عند استخدام خدمات QRScan.\n\n**كيف نستخدم البيانات**\nلتقديم المنصة وتحسينها ومعالجة التحليلات وتطبيق حدود الخطة والتواصل بخصوص حسابك.\n\n**مشاركة البيانات**\nلا نبيع البيانات الشخصية. نستخدم مزودي بنية تحتية موثوقين.\n\n**حقوقك**\nيمكنك طلب الوصول أو التصحيح أو الحذف بالتواصل معنا.\n\n**ملفات تعريف الارتباط**\nنستخدم cookies أساسية للمصادقة والتفضيلات (بما في ذلك السمة).",
                'contact_info' => null,
                'is_active' => true,
            ],
            [
                'locale' => 'ar',
                'slug' => 'terms',
                'title' => 'شروط الخدمة',
                'intro' => 'آخر تحديث: يونيو 2026',
                'content' => "**القبول**\nباستخدام QRScan فإنك توافق على هذه الشروط.\n\n**محتواك**\nتحتفظ بملكية الرموز والروابط والصفحات والقوائم والشارات والتذاكر والحملات التي تنشئها. أنت مسؤول عن امتثال المحتوى للقوانين.\n\n**الاستخدام المقبول**\nلا تستخدم QRScan للبريد العشوائي أو البرمجيات الخبيثة أو المحتوى غير القانوني أو انتهاك حقوق الغير.\n\n**الخطط والفوترة**\nالخطط المدفوعة تتجدد شهرياً ما لم تُلغَ. تنطبق حدود الاستخدام حسب الخطة.\n\n**حدود المسؤولية**\nتُقدّم QRScan \"كما هي\" دون ضمانات.\n\n**التغييرات**\nقد نحدّث هذه الشروط؛ الاستمرار في الاستخدام يعني القبول.",
                'contact_info' => null,
                'is_active' => true,
            ],
        ];
    }
}
