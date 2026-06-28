<?php

use App\Models\LandingFeature;
use App\Models\LandingSetting;
use App\Models\SitePage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('intro')->nullable();
            $table->longText('content');
            $table->json('contact_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $pages = [
            [
                'slug' => 'support',
                'title' => 'Support',
                'intro' => 'We\'re here to help you get the most out of QRScan.',
                'content' => "Need help with QR codes, digital pages, tickets, or your account?\n\nBrowse our resources or reach out to the team — we typically respond within one business day.\n\n**Common topics**\n- Setting up your first QR code\n- Connecting a custom domain\n- Publishing digital pages, menus, badges & tickets\n- Scan to Win campaigns\n- Billing and plan upgrades",
                'contact_info' => ['email' => 'support@qrscan.digital', 'hours' => 'Mon–Fri, 9am–6pm UTC'],
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Us',
                'intro' => 'Questions, partnerships, or enterprise plans — we\'d love to hear from you.',
                'content' => "Send us a message and our team will get back to you shortly.\n\nFor urgent account issues, email support directly. For sales and partnerships, use the contact details below.",
                'contact_info' => [
                    'email' => 'hello@qrscan.digital',
                    'phone' => '+1 (555) 012-3456',
                    'address' => 'QRScan Inc., 100 Market Street, San Francisco, CA 94105',
                    'hours' => 'Mon–Fri, 9am–6pm UTC',
                ],
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'intro' => 'Last updated: June 2026',
                'content' => "**Information we collect**\nWe collect account information (name, email), usage data (scans, clicks, page views), and analytics metadata (country, referrer) when you use QRScan services.\n\n**How we use data**\nTo provide and improve our platform, process analytics, enforce plan limits, and communicate about your account.\n\n**Data sharing**\nWe do not sell personal data. We use trusted infrastructure providers to host the service.\n\n**Your rights**\nYou may request access, correction, or deletion of your data by contacting us.\n\n**Cookies**\nWe use essential cookies for authentication and preferences (including theme).",
                'contact_info' => null,
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'intro' => 'Last updated: June 2026',
                'content' => "**Acceptance**\nBy using QRScan you agree to these terms.\n\n**Your content**\nYou retain ownership of QR codes, links, pages, menus, badges, tickets, and campaigns you create. You are responsible for ensuring content complies with applicable laws.\n\n**Acceptable use**\nDo not use QRScan for spam, malware, illegal content, or to violate third-party rights.\n\n**Plans & billing**\nPaid plans renew monthly unless cancelled. Usage limits apply per plan tier.\n\n**Limitation of liability**\nQRScan is provided \"as is\" without warranties. We are not liable for indirect damages arising from use of the service.\n\n**Changes**\nWe may update these terms; continued use constitutes acceptance.",
                'contact_info' => null,
            ],
        ];

        foreach ($pages as $page) {
            SitePage::create($page);
        }

        LandingSetting::setValue('footer', [
            'tagline' => 'Scan. Link. Connect.',
            'support_email' => 'support@qrscan.digital',
        ]);

        LandingSetting::setValue('hero', [
            'badge' => 'Launch offer — 50% off all plans',
            'title' => 'Every QR tool your brand needs — in one platform',
            'subtitle' => 'QR codes, short links, digital cards, pages, menus, badges, tickets & scan-to-win campaigns. Create, share, and track everything from one dashboard.',
            'cta_primary' => 'Start free',
            'cta_secondary' => 'See pricing',
        ]);

        $newFeatures = [
            [
                'title' => 'Digital Pages',
                'subtitle' => 'Landing & events',
                'description' => 'Template-based landing pages, portfolios, and event pages with galleries, calendars, and social links.',
                'items' => ['4 templates', 'Gallery & calendar', 'Contact blocks', 'Custom QR styles'],
                'icon' => 'page',
                'color' => '#e8b84a',
                'sort_order' => 4,
            ],
            [
                'title' => 'Digital Menus',
                'subtitle' => 'QR restaurant menus',
                'description' => 'Beautiful mobile menus with sections, photos, dietary tags, and instant QR sharing.',
                'items' => ['Multi-section menus', 'Photo dishes', 'Dietary tags', 'Multi-currency'],
                'icon' => 'menu',
                'color' => '#e8655a',
                'sort_order' => 5,
            ],
            [
                'title' => 'Digital Badges',
                'subtitle' => 'Credentials & awards',
                'description' => 'Issue verifiable digital badges and certificates with skills, dates, and QR verification.',
                'items' => ['3 badge templates', 'Skills & expiry', 'Verify URL', 'QR credentials'],
                'icon' => 'badge',
                'color' => '#6b4fa0',
                'sort_order' => 6,
            ],
            [
                'title' => 'Digital Tickets',
                'subtitle' => 'Event admission',
                'description' => 'Create scannable event tickets with seating, barcodes, and concert or conference layouts.',
                'items' => ['4 ticket styles', 'Seat & barcode', 'Valid date range', 'Check-in QR'],
                'icon' => 'ticket',
                'color' => '#e8655a',
                'sort_order' => 7,
            ],
            [
                'title' => 'Scan to Win',
                'subtitle' => 'Prize campaigns',
                'description' => 'Run gamified QR campaigns with prize pools, play limits, and instant or wheel reveals.',
                'items' => ['Prize pool editor', 'Daily play limits', 'Win/lose messages', 'Campaign analytics'],
                'icon' => 'win',
                'color' => '#e8b84a',
                'sort_order' => 8,
            ],
        ];

        foreach ($newFeatures as $feature) {
            LandingFeature::updateOrCreate(
                ['title' => $feature['title']],
                array_merge($feature, [
                    'items' => json_encode($feature['items']),
                    'is_active' => true,
                ])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');
        LandingFeature::whereIn('title', [
            'Digital Pages', 'Digital Menus', 'Digital Badges', 'Digital Tickets', 'Scan to Win',
        ])->delete();
    }
};
