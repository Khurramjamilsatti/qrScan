<?php

namespace Database\Seeders;

use App\Models\LandingFeature;
use App\Models\LandingSetting;
use App\Models\PricingPlan;
use App\Models\SitePage;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@qrscan.digital'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'plan' => 'business',
                'scans_reset_at' => now()->addMonth(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'demo@qrscan.digital'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'plan' => 'starter',
                'scans_reset_at' => now()->addMonth(),
            ]
        );

        LandingSetting::setValue('site', [
            'name' => 'QRScan',
            'tagline' => 'Scan. Link. Connect.',
            'logo_text' => 'QRScan',
        ]);

        LandingSetting::setValue('hero', [
            'badge' => 'Launch offer — 50% off all plans',
            'title' => 'Every QR tool your brand needs — in one platform',
            'subtitle' => 'QR codes, short links, digital cards, pages, menus, badges, tickets & scan-to-win campaigns. Create, share, and track everything from one dashboard.',
            'cta_primary' => 'Start free',
            'cta_secondary' => 'See pricing',
        ]);

        LandingSetting::setValue('footer', [
            'tagline' => 'Scan. Link. Connect.',
            'support_email' => 'support@qrscan.digital',
        ]);

        LandingSetting::setValue('stats', [
            ['label' => 'QR codes created', 'value' => '2M+'],
            ['label' => 'Links shortened', 'value' => '10M+'],
            ['label' => 'Countries reached', 'value' => '150+'],
            ['label' => 'Uptime', 'value' => '99.9%'],
        ]);

        LandingSetting::setValue('cta', [
            'title' => 'Ready to grow your brand?',
            'subtitle' => 'Join thousands of marketers, founders, and teams using QRScan to connect offline to online.',
            'button_text' => 'Get started — it\'s free',
        ]);

        foreach ($this->features() as $feature) {
            LandingFeature::updateOrCreate(
                ['locale' => 'en', 'sort_order' => $feature['sort_order']],
                $feature
            );
        }

        foreach ($this->sitePages() as $page) {
            SitePage::updateOrCreate(
                ['slug' => $page['slug'], 'locale' => 'en'],
                $page
            );
        }

        foreach ($this->pricingPlans() as $plan) {
            PricingPlan::updateOrCreate(
                ['slug' => $plan['slug'], 'locale' => 'en'],
                $plan
            );
        }

        foreach ($this->testimonials() as $testimonial) {
            Testimonial::updateOrCreate(
                ['locale' => 'en', 'sort_order' => $testimonial['sort_order']],
                $testimonial
            );
        }

        $this->call(LocalizedContentSeeder::class);
    }

    /** @return list<array<string, mixed>> */
    private function features(): array
    {
        return [
            [
                'title' => 'QR Codes',
                'subtitle' => 'Dynamic & trackable',
                'description' => 'Create branded QR codes that you can edit anytime — even after printing.',
                'items' => ['Dynamic editing after print', 'Real-time scan analytics', 'Logo & color customization', 'SVG & PNG downloads'],
                'icon' => 'qr',
                'color' => '#10b981',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Short Links',
                'subtitle' => 'Branded & measurable',
                'description' => 'Shorten URLs with custom slugs, UTM tracking, and geographic click data.',
                'items' => ['Custom slugs & aliases', 'Click tracking with geo data', 'Redirect management', 'UTM parameter support'],
                'icon' => 'link',
                'color' => '#f59e0b',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Digital Business Cards',
                'subtitle' => 'Shareable profiles',
                'description' => 'Replace paper cards with beautiful digital profiles and auto-generated QR codes.',
                'items' => ['Custom profile URLs', 'vCard & contact export', 'Social links & photo', 'Auto-generated QR code'],
                'icon' => 'card',
                'color' => '#8b5cf6',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Digital Pages',
                'subtitle' => 'Landing & events',
                'description' => 'Template-based landing pages, portfolios, and event pages with galleries, calendars, and social links.',
                'items' => ['4 templates', 'Gallery & calendar', 'Contact blocks', 'Custom QR styles'],
                'icon' => 'page',
                'color' => '#e8b84a',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Digital Menus',
                'subtitle' => 'QR restaurant menus',
                'description' => 'Beautiful mobile menus with sections, photos, dietary tags, and instant QR sharing.',
                'items' => ['Multi-section menus', 'Photo dishes', 'Dietary tags', 'Multi-currency'],
                'icon' => 'menu',
                'color' => '#e8655a',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Digital Badges',
                'subtitle' => 'Credentials & awards',
                'description' => 'Issue verifiable digital badges and certificates with skills, dates, and QR verification.',
                'items' => ['3 badge templates', 'Skills & expiry', 'Verify URL', 'QR credentials'],
                'icon' => 'badge',
                'color' => '#6b4fa0',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'title' => 'Digital Tickets',
                'subtitle' => 'Event admission',
                'description' => 'Create scannable event tickets with seating, barcodes, and concert or conference layouts.',
                'items' => ['4 ticket styles', 'Seat & barcode', 'Valid date range', 'Check-in QR'],
                'icon' => 'ticket',
                'color' => '#e8655a',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'title' => 'Scan to Win',
                'subtitle' => 'Prize campaigns',
                'description' => 'Run gamified QR campaigns with prize pools, play limits, and instant or wheel reveals.',
                'items' => ['Prize pool editor', 'Daily play limits', 'Win/lose messages', 'Campaign analytics'],
                'icon' => 'win',
                'color' => '#e8b84a',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'title' => 'Forms',
                'subtitle' => 'Surveys & questionnaires',
                'description' => 'Build Google Forms-style surveys with 15+ field types, response analytics, and CSV export.',
                'items' => ['15+ field types', 'Response summaries', 'CSV export', 'QR-shareable forms'],
                'icon' => 'form',
                'color' => '#673ab7',
                'sort_order' => 9,
                'is_active' => true,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function sitePages(): array
    {
        return [
            [
                'slug' => 'support',
                'title' => 'Support',
                'intro' => 'We\'re here to help you get the most out of QRScan.',
                'content' => "Need help with QR codes, digital pages, tickets, or your account?\n\nBrowse our resources or reach out to the team — we typically respond within one business day.",
                'contact_info' => ['email' => 'support@qrscan.digital', 'hours' => 'Mon–Fri, 9am–6pm UTC'],
                'is_active' => true,
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Us',
                'intro' => 'Questions, partnerships, or enterprise plans — we\'d love to hear from you.',
                'content' => 'Send us a message and our team will get back to you shortly.',
                'contact_info' => [
                    'email' => 'hello@qrscan.digital',
                    'phone' => '+1 (555) 012-3456',
                    'address' => 'QRScan Inc., 100 Market Street, San Francisco, CA 94105',
                    'hours' => 'Mon–Fri, 9am–6pm UTC',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'intro' => 'Last updated: June 2026',
                'content' => "**Information we collect**\nWe collect account information (name, email), usage data (scans, clicks, page views), and analytics metadata when you use QRScan services.",
                'contact_info' => null,
                'is_active' => true,
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'intro' => 'Last updated: June 2026',
                'content' => "**Acceptance**\nBy using QRScan you agree to these terms.\n\n**Your content**\nYou retain ownership of QR codes, links, pages, menus, badges, tickets, and campaigns you create.",
                'contact_info' => null,
                'is_active' => true,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function pricingPlans(): array
    {
        return [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'billing_period' => 'month',
                'features' => ['1 QR code', '3 short links', '1 business card', '1 form', '100 scans/month', 'No analytics'],
                'limits' => ['qr_codes' => 1, 'short_links' => 3, 'business_cards' => 1, 'forms' => 1, 'scans' => 100],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price' => 6,
                'billing_period' => 'month',
                'features' => ['10 QR codes', 'Unlimited links', '5 business cards', '5 forms', '5K scans/month', 'Basic analytics'],
                'limits' => ['qr_codes' => 10, 'short_links' => -1, 'business_cards' => 5, 'forms' => 5, 'scans' => 5000],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 20,
                'billing_period' => 'month',
                'features' => ['Unlimited QR codes', 'Custom domain', 'Unlimited cards & forms', '50K scans/month', 'Full analytics'],
                'limits' => ['qr_codes' => -1, 'short_links' => -1, 'business_cards' => -1, 'forms' => -1, 'scans' => 50000],
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price' => 50,
                'billing_period' => 'month',
                'features' => ['White-labeling', 'Team seats', 'API access', 'Unlimited scans', 'Priority support'],
                'limits' => ['qr_codes' => -1, 'short_links' => -1, 'business_cards' => -1, 'forms' => -1, 'scans' => -1],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function testimonials(): array
    {
        return [
            [
                'name' => 'Sarah Chen',
                'role' => 'Marketing Director',
                'company' => 'Bloom Studio',
                'content' => 'QRScan replaced three tools for us. The dynamic QR codes alone saved our print budget.',
                'avatar' => null,
                'rating' => 5,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Marcus Webb',
                'role' => 'Founder',
                'company' => 'LaunchPad',
                'content' => 'Our event short links with UTM tracking give us clarity we never had before.',
                'avatar' => null,
                'rating' => 5,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Elena Rodriguez',
                'role' => 'Sales Lead',
                'company' => 'Nexus Corp',
                'content' => 'Digital business cards with QR codes — our team loves them at conferences.',
                'avatar' => null,
                'rating' => 5,
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];
    }
}
