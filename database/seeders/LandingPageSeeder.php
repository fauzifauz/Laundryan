<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'site' => [
                'name' => 'LAUNDRYAN',
                'logo_url' => '', // Default empty, text will show
            ],
            'hero' => [
                'title_line1' => 'Laundry Day,',
                'title_accent' => 'Made Easy',
                'subtitle' => 'Experience the gold standard of garment care. We pick up, clean with eco-conscious precision, and deliver fresh luxury straight to your door.',
                'cta_text' => 'Schedule Your Pickup',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDW5dFL9VUF5NzcFNGok0f791tj4uDKWGo6e5q4sFGB5GkxBaghYV9vYuGZVGILnD09iR2fQPco1504I8vyOy0vOZqBdeL0aRf40Ve7EcAvB7PxcAqyBOlviXlxeRzq3eTScE-ioZznkt4PuATBp0KXLb1lfI8xCupekM7I-hxneHwvcK19fTuuBfhztfU7MEaxb5h57CFIShZ99v-M6R5IEz4Td4HAcxvaVRsbVQBFx2zM0wA20caKOSrSQxzpFFovuec9tdVfGdI',
            ],
            'services' => [
                'heading' => 'Curated Care Services',
                'subtitle' => 'Our Expertise',
                'items' => [
                    ['icon' => 'local_laundry_service', 'title' => 'Wash & Fold', 'desc' => 'Everyday essentials cleaned with professional-grade detergents and expertly folded for your convenience.'],
                    ['icon' => 'checkroom', 'title' => 'Dry Cleaning', 'desc' => 'Specialized cleaning for your delicate fabrics and formal wear, ensuring longevity and a crisp finish.'],
                    ['icon' => 'iron', 'title' => 'Steam Ironing', 'desc' => 'State-of-the-art steam technology to remove every wrinkle and refresh your garments to like-new condition.'],
                ]
            ],
            'process' => [
                'heading' => 'The Cycle of Freshness',
                'subtitle' => 'Four simple steps to reclaim your weekend.',
                'steps' => [
                    ['icon' => 'calendar_today', 'title' => 'Schedule', 'desc' => 'Pick a time that suits your lifestyle through our app.'],
                    ['icon' => 'local_shipping', 'title' => 'We Pick Up', 'desc' => 'Our valet collects your items from your doorstep.'],
                    ['icon' => 'water_drop', 'title' => 'We Clean', 'desc' => 'Expert treatment tailored to each specific garment.'],
                    ['icon' => 'task_alt', 'title' => 'Delivered', 'desc' => 'Pristine laundry delivered back to you within 24h.'],
                ]
            ],
            'benefits' => [
                'heading' => 'The Laundryan Advantage',
                'subtitle' => 'Why Choose Us',
                'items' => [
                    ['key' => 'eco', 'icon' => 'eco', 'title' => 'Eco-friendly Detergents', 'desc' => 'We use dermatologically tested, plant-based solutions that are tough on stains but gentle on the planet and your skin.'],
                    ['key' => 'bolt', 'icon' => 'bolt', 'title' => 'Fast 24h Turnaround', 'desc' => 'Time is luxury. That\'s why we guarantee your clothes back in your closet within one day.'],
                    ['key' => 'payments', 'icon' => 'payments', 'title' => 'Affordable Pricing', 'desc' => 'Premium service doesn\'t have to mean premium prices. Transparent, competitive rates for everyone.'],
                    ['key' => 'fact_check', 'icon' => 'fact_check', 'title' => 'Quality Checked', 'desc' => 'Every single garment passes through a 7-point quality inspection before it leaves our facility.'],
                ]
            ],
            'pricing' => [
                'heading' => 'Transparent Pricing',
                'subtitle' => 'Investment in Quality',
                'desc' => 'Choose the perfect care plan for your wardrobe.',
                'plans' => [
                    [
                        'name' => 'Essential',
                        'subtitle' => 'Basic wash and fold service',
                        'price' => 'Rp 10.000',
                        'features' => ['Premium Detergent Wash', 'Machine Tumble Dry', 'Neat Professional Folding', '48h Standard Turnaround']
                    ],
                    [
                        'name' => 'Premium',
                        'subtitle' => 'Wash, dry, and expert iron',
                        'price' => 'Rp 15.000',
                        'features' => ['All Essential Benefits', 'Eco-Friendly Softener', 'Professional Steam Ironing', 'Guaranteed 24h Delivery'],
                        'popular' => true
                    ],
                    [
                        'name' => 'Executive',
                        'subtitle' => 'Complete care for delicate fabrics',
                        'price' => 'Rp 25.000',
                        'features' => ['All Premium Benefits', 'Specialized Silk & Wool Care', 'Custom Stain Treatment', 'VIP Express Pick-up']
                    ]
                ]
            ],
            'reviews' => [
                'heading' => 'Trusted by Thousands',
                'subtitle' => 'See why LAUNDRYAN is the highest-rated garment care service in the city.',
                'items' => [
                    ['name' => 'Sarah Jenkins', 'role' => 'Marketing Executive', 'text' => 'Absolutely life-changing. My clothes have never looked better, and the convenience of home pickup is worth every penny.', 'avatar' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA6IKGBN4cUYpNJzXusRFdQ_WlST2Z2h-HBKmIVWY78r3k9mdIctjQvj4XHXJ0O0_M1xJJQ-lABFL8eH_lOKkVaYUFcyrWV_Jgx9FR7yeVMkIIXYl6HywCzwLAUajrCF3z6YhRzQrDUj60ukirQsbrkbWZMG9ah--C5ch6OusS4XgDyajcCW39c-evDNSmJW0M2IDNkR__FflLHzflWRuQoITY9-9tR7_OaON4jPni27jKs5UKUBmwu1B4D1ONjHpMPHKZorkqnPlM'],
                    ['name' => 'James Wilson', 'role' => 'Banker', 'text' => 'The steam ironing service is impeccable. My dress shirts come back looking like they just came off the shelf. 5 stars!', 'avatar' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDuYh6yiXy98sN-DkNuvBr4CCbi3ex_TXnLVhxUSROGTbEBiqFT2lmPL3ocIxpNVhhJdxTTAzo-bE5XbVRk9shMNY_c6rlSBrHgqJlWri6k3cI7ptQSjojQD2nvXa5rKo2kf7i1U5iR5I2xmtfJ3J2oO2HI1oKMg7q-RhxLUl5DyqXQMLcibeOL6tJlzxnH7WTt2Sd2e-vBsy1qBRFBAMWWQojJdpQJEwJdgw1u60uLUq0dCkhGxqHeT9R6qxTpqdPq4_1reRfIskQ'],
                    ['name' => 'Elena Rodriguez', 'role' => 'Interior Designer', 'text' => 'The best laundry app I\'ve used. Simple interface, prompt delivery, and the \'Freshness\' scent is wonderful but subtle.', 'avatar' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBvEuoirAt1EasW_LQAdFettlCKVhZn76ufNK3NwRcuN0Kk82WNT_ukSJMa65VNiHRczWKa90QKvozMDeL4XiEee0uQYGVrhp-twBi7CQJJRt3J-60s-XcGf3wwsFc14NzLitojVUqrSIj5M6zgyfomAagZg2TM-iYrUD7GVTRhOWat3Ism2FmxhyoYVXufY535gd7bVU0Ms4P4dr1k4B_SRE_4D7ow9eCUu-bcUvz9UNODthoshLuDlroh1nPqjOBmjB3-VtBPODM'],
                ]
            ],
            'location' => [
                'heading' => 'Visit Our Location',
                'subtitle' => 'Our Outlet',
                'map_iframe' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126748.77506686!2d106.4774561!3d-6.1632146!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69fb5f1f282bf1%3A0xa3ca0c14b3e07736!2sKabupaten%20Tangerang%2C%20Banten!5e0!3m2!1sid!2sid!4v1700000000002!5m2!1sid!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
            ],
            'faqs' => [
                'heading' => 'Everything You Need To Know',
                'subtitle' => 'Frequently Asked Questions',
                'items' => [
                    [
                        'question' => 'How do I schedule a pickup?',
                        'answer' => 'You can schedule a pickup by clicking the "Order Now" button, choosing your service, and selecting a time slot that works for you.'
                    ],
                    [
                        'question' => 'What is your turnaround time?',
                        'answer' => 'Our standard turnaround time is 24-48 hours depending on the plan you choose. Our Premium and Executive plans guarantee 24h delivery.'
                    ],
                    [
                        'question' => 'Do you use eco-friendly detergents?',
                        'answer' => 'Yes, we use dermatologically tested, plant-based solutions that are gentle on your skin and the environment.'
                    ],
                    [
                        'question' => 'How do I pay for the service?',
                        'answer' => 'We accept various payment methods including credit/debit cards, bank transfers, and digital wallets through our secure payment gateway.'
                    ],
                    [
                        'question' => 'What if I am not home during pickup/delivery?',
                        'answer' => 'You can leave instructions for our valet to collect or drop off your laundry at a safe location, like with a concierge or at your doorstep.'
                    ],
                ]
            ],
            'footer' => [
                'cta_title' => 'Ready for a spotless week?',
                'cta_subtitle' => 'Join 10,000+ happy customers and schedule your first pickup today. Get 20% off your first order.',
                'cta_button' => 'Schedule Your Pickup',
                'mission' => 'Providing premium garment care since 2024. We combine eco-friendly technology with artisan precision to give your clothes the love they deserve.',
                'address' => '123 Fresh Lane, Spotless District, Jakarta 12345',
                'phone' => '+62 21 555 0123',
                'email' => 'hello@laundryan.com',
                'facebook_url' => '#',
                'instagram_url' => '#',
                'x_url' => '#',
                'copyright' => '© 2024 LAUNDRYAN. All rights reserved.'
            ],
            // ── Authentication Pages Content ──────────────────────────────
            'login' => [
                'left_title'      => 'Welcome Back to Laundryan.',
                'left_subtitle'   => 'Reclaim your time while we handle your garments with the gold standard of cleaning technology.',
                'right_title'     => 'Sign In',
                'right_subtitle'  => 'Access your premium laundry dashboard.',
            ],
            'register' => [
                'left_title'      => 'Join the Revolution of Clean.',
                'left_subtitle'   => 'Create your account today and experience garment care that exceeds expectations, every single time.',
                'right_title'     => 'Create Account',
                'right_subtitle'  => 'Experience premium laundry services with ease.',
            ],
            'forgot_password' => [
                'left_title'      => 'Reset Your Password.',
                'left_subtitle'   => 'No worries — enter your email and we\'ll send a secure link to get you back in.',
                'right_title'     => 'Forgot Password?',
                'right_subtitle'  => 'We\'ll email you a link to reset your password.',
            ],
        ];

        foreach ($settings as $key => $content) {
            \App\Models\LandingPageSetting::updateOrCreate(
                ['key' => $key],
                ['content' => $content]
            );
        }
    }
}
