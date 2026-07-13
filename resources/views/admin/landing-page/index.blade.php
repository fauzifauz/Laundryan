<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-xl text-slate-800 leading-tight">
                    {{ __('Landing Page CMS & Form Settings') }}
                </h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Configure and manage all public landing page sections and general brand identities</p>
            </div>
            <div class="flex items-center gap-2 bg-[#005bc0]/5 border border-[#005bc0]/15 px-3 py-1.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-[#005bc0] shadow-sm">
                <span class="material-symbols-outlined text-[16px] animate-pulse">design_services</span>
                Live Editor Mode
            </div>
        </div>
    </x-slot>

    <!-- Custom CSS for Premium Design & Layout -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 91, 192, 0.15);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 91, 192, 0.3);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .preview-browser-frame {
            border-top: 20px solid #f1f5f9;
            position: relative;
        }
        .preview-browser-frame::before {
            content: '';
            position: absolute;
            top: -14px;
            left: 10px;
            width: 6px;
            height: 6px;
            background: #ef4444;
            border-radius: 50%;
            box-shadow: 10px 0 0 #f59e0b, 20px 0 0 #10b981;
        }
    </style>

    <div class="py-6" x-data="{
        activeCategory: @if(session('updated_key') === 'site' || session('updated_key') === 'contact' || session('updated_key') === 'socials' || session('updated_key') === 'login' || session('updated_key') === 'register' || session('updated_key') === 'forgot_password') 'general' @else localStorage.getItem('cms_active_category') || 'landing' @endif,
        activeTab: @if(session('updated_key')) '{{ session('updated_key') }}' @else localStorage.getItem('cms_active_tab') || 'hero' @endif,
        setActiveCategory(cat) {
            this.activeCategory = cat;
            localStorage.setItem('cms_active_category', cat);
        },
        setActiveTab(tab) {
            this.activeTab = tab;
            localStorage.setItem('cms_active_tab', tab);
        },

        // Auth Settings (Login, Register, Forgot Password)
        loginLeftTitle: {{ json_encode($settings['login']['left_title'] ?? 'Welcome Back to Laundryan.') }},
        loginLeftSubtitle: {{ json_encode($settings['login']['left_subtitle'] ?? 'Reclaim your time while we handle your garments with the gold standard of cleaning technology.') }},
        loginRightTitle: {{ json_encode($settings['login']['right_title'] ?? 'Sign In') }},
        loginRightSubtitle: {{ json_encode($settings['login']['right_subtitle'] ?? 'Access your premium laundry dashboard.') }},
        registerLeftTitle: {{ json_encode($settings['register']['left_title'] ?? 'Join the Revolution of Clean.') }},
        registerLeftSubtitle: {{ json_encode($settings['register']['left_subtitle'] ?? 'Create your account today and experience garment care that exceeds expectations, every single time.') }},
        registerRightTitle: {{ json_encode($settings['register']['right_title'] ?? 'Create Account') }},
        registerRightSubtitle: {{ json_encode($settings['register']['right_subtitle'] ?? 'Experience premium laundry services with ease.') }},
        forgotPasswordLeftTitle: {{ json_encode($settings['forgot_password']['left_title'] ?? 'Reset Your Password.') }},
        forgotPasswordLeftSubtitle: {{ json_encode($settings['forgot_password']['left_subtitle'] ?? 'No worries — enter your email and we\'ll send a secure link to get you back in.') }},
        forgotPasswordRightTitle: {{ json_encode($settings['forgot_password']['right_title'] ?? 'Forgot Password?') }},
        forgotPasswordRightSubtitle: {{ json_encode($settings['forgot_password']['right_subtitle'] ?? 'We\'ll email you a link to reset your password.') }},

        // Site Identity
        siteName: {{ json_encode($settings['site']['name'] ?? 'LAUNDRYAN') }},
        logoUrl: {{ json_encode($settings['site']['logo_url'] ?? '') }},
        logoPreview: {{ json_encode($settings['site']['logo_url'] ?? '') }},

        // Hero Settings
        heroTitle1: {{ json_encode($settings['hero']['title_line1'] ?? 'Professional Care for Your') }},
        heroAccent: {{ json_encode($settings['hero']['title_accent'] ?? 'Everyday Wear') }},
        heroSubtitle: {{ json_encode($settings['hero']['subtitle'] ?? 'Premium laundry and dry cleaning services delivered straight to your doorstep.') }},
        heroCtaText: {{ json_encode($settings['hero']['cta_text'] ?? 'Schedule Pickup') }},
        imageUrl: {{ json_encode($settings['hero']['image_url'] ?? '') }},
        imagePreview: {{ json_encode($settings['hero']['image_url'] ?? '') }},

        // Location Settings
        locationHeading: {{ json_encode($settings['location']['heading'] ?? 'Find Our Outlets') }},
        locationSubtitle: {{ json_encode($settings['location']['subtitle'] ?? 'Visit our stores for manual drop-offs and consultations') }},
        mapIframe: {{ json_encode($settings['location']['map_iframe'] ?? '') }},

        // Footer & General Settings
        footerCtaTitle: {{ json_encode($settings['footer']['cta_title'] ?? 'Ready to experience the freshness?') }},
        footerCtaSubtitle: {{ json_encode($settings['footer']['cta_subtitle'] ?? 'Book your laundry pickup today and get 20% off your first order.') }},
        footerCtaButton: {{ json_encode($settings['footer']['cta_button'] ?? 'Order Now') }},
        footerPhone: {{ json_encode($settings['footer']['phone'] ?? '+62 812-3456-7890') }},
        footerEmail: {{ json_encode($settings['footer']['email'] ?? 'info@laundryan.com') }},
        footerAddress: {{ json_encode($settings['footer']['address'] ?? 'Jl. Merdeka No. 123, Jakarta') }},
        footerMission: {{ json_encode($settings['footer']['mission'] ?? 'Providing eco-friendly, premium laundry and dry cleaning solutions with top-tier care.') }},
        footerCopyright: {{ json_encode($settings['footer']['copyright'] ?? '© 2026 LAUNDRYAN. All rights reserved.') }},
        footerFacebook: {{ json_encode($settings['footer']['facebook_url'] ?? '#') }},
        footerInstagram: {{ json_encode($settings['footer']['instagram_url'] ?? '#') }},
        footerX: {{ json_encode($settings['footer']['x_url'] ?? '#') }},

        // Dynamic Lists
        services: {{ json_encode($settings['services']['items'] ?? []) }},
        servicesSubtitle: {{ json_encode($settings['services']['subtitle'] ?? 'Our Expertise') }},
        servicesHeading: {{ json_encode($settings['services']['heading'] ?? 'Curated Care Services') }},
        addService() {
            this.services.push({ icon: 'local_laundry_service', title: 'New Service', desc: 'Everyday essentials cleaned with professional-grade detergents.' });
        },
        removeService(index) {
            this.services.splice(index, 1);
        },

        steps: {{ json_encode($settings['process']['steps'] ?? []) }},
        stepsSubtitle: {{ json_encode($settings['process']['subtitle'] ?? 'How it works') }},
        stepsHeading: {{ json_encode($settings['process']['heading'] ?? 'The Cycle of Freshness') }},
        addStep() {
            this.steps.push({ icon: 'route', title: 'Next Step', desc: 'Expert treatment tailored to each specific garment.' });
        },
        removeStep(index) {
            this.steps.splice(index, 1);
        },

        benefits: {{ json_encode($settings['benefits']['items'] ?? []) }},
        benefitsSubtitle: {{ json_encode($settings['benefits']['subtitle'] ?? 'Why Choose Us') }},
        benefitsHeading: {{ json_encode($settings['benefits']['heading'] ?? 'The Laundryan Advantage') }},
        addBenefit() {
            this.benefits.push({ key: 'custom_' + Date.now(), icon: 'workspace_premium', title: 'Premium Advantage', desc: 'High standards of washing and attention to details.' });
        },
        removeBenefit(index) {
            this.benefits.splice(index, 1);
        },

        plans: {{ json_encode($settings['pricing']['plans'] ?? []) }}.map(p => ({
            name: p.name || '',
            subtitle: p.subtitle || '',
            price: p.price || '',
            popular: !!p.popular,
            features_raw: p.features ? p.features.join(', ') : ''
        })),
        pricingSubtitle: {{ json_encode($settings['pricing']['subtitle'] ?? 'Flexible Plans') }},
        pricingHeading: {{ json_encode($settings['pricing']['heading'] ?? 'Transparent Pricing') }},
        pricingDesc: {{ json_encode($settings['pricing']['desc'] ?? 'Choose the perfect care plan for your garments') }},
        addPlan() {
            this.plans.push({ name: 'Standard Care', subtitle: 'Perfect for daily wash', price: 'Rp 10.000/kg', popular: false, features_raw: 'Wash & Fold, Ironing, 48h Delivery' });
        },
        removePlan(index) {
            this.plans.splice(index, 1);
        },

        faqs: {{ json_encode($settings['faqs']['items'] ?? []) }},
        faqsSubtitle: {{ json_encode($settings['faqs']['subtitle'] ?? 'Got Questions?') }},
        faqsHeading: {{ json_encode($settings['faqs']['heading'] ?? 'Frequently Asked Questions') }},
        addFaq() {
            this.faqs.push({ question: 'How long does it take?', answer: 'Standard turnaround time is 48 hours.' });
        },
        removeFaq(index) {
            this.faqs.splice(index, 1);
        },

        reviews: {{ json_encode($settings['reviews']['items'] ?? []) }}.map(r => ({
            name: r.name || '',
            role: r.role || '',
            avatar: r.avatar || '',
            text: r.text || '',
            localPreview: r.avatar || ''
        })),
        reviewsSubtitle: {{ json_encode($settings['reviews']['subtitle'] ?? 'Testimonials') }},
        reviewsHeading: {{ json_encode($settings['reviews']['heading'] ?? 'What Our Customers Say') }},
        addReview() {
            this.reviews.push({ name: 'Sarah Jenkins', role: 'Business Owner', avatar: '', text: 'Absolutely spectacular service! Extremely fast and professional care.', localPreview: '' });
        },
        removeReview(index) {
            this.reviews.splice(index, 1);
        }
    }">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Toast Notifications -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                    x-transition:enter="transform ease-out duration-300 transition"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed top-6 right-6 z-50 max-w-sm w-full bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-5 shadow-2xl text-white flex items-center justify-between overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined text-white text-xl">check_circle</span>
                        </div>
                        <div>
                            <h4 class="font-black text-xs uppercase tracking-wider">Changes Saved</h4>
                            <p class="text-[11px] text-emerald-50 font-medium mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-white/60 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10 relative z-10">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            @endif

            <!-- Main Panel Container -->
            <div class="bg-slate-100/40 rounded-[32px] border border-slate-200/60 shadow-xl overflow-hidden min-h-[700px] flex flex-col lg:flex-row">
                
                <!-- Left Sidebar Navigation Panel -->
                <div class="w-full lg:w-72 bg-white border-b lg:border-b-0 lg:border-r border-slate-200/80 flex flex-col shrink-0">
                    
                    <!-- Sidebar Top Header -->
                    <div class="p-5 border-b border-slate-150 bg-gradient-to-br from-slate-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-[#005bc0]/10 flex items-center justify-center text-[#005bc0] shadow-inner">
                                <span class="material-symbols-outlined font-black">tune</span>
                            </div>
                            <div>
                                <h3 class="font-black text-xs text-slate-800 uppercase tracking-wide">CMS Manager</h3>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Customize Public Site</p>
                            </div>
                        </div>
                    </div>

                    <!-- Category Tab Switcher -->
                    <div class="p-3 bg-slate-50/50 border-b border-slate-150">
                        <div class="flex p-1 bg-slate-200/60 rounded-2xl gap-1">
                            <button @click="setActiveCategory('landing'); setActiveTab('hero')"
                                :class="activeCategory === 'landing' ? 'bg-white text-slate-800 shadow-sm font-black' : 'text-slate-555 hover:text-slate-800 font-bold'"
                                class="flex-1 py-1.5 rounded-xl text-[9px] uppercase tracking-wider text-center transition-all duration-200">
                                Landing Page
                            </button>
                            <button @click="setActiveCategory('general'); setActiveTab('site')"
                                :class="activeCategory === 'general' ? 'bg-white text-slate-800 shadow-sm font-black' : 'text-slate-555 hover:text-slate-800 font-bold'"
                                class="flex-1 py-1.5 rounded-xl text-[9px] uppercase tracking-wider text-center transition-all duration-200">
                                General Info
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Items List -->
                    <div class="flex-1 p-3 space-y-1.5 overflow-y-auto custom-scrollbar bg-white">
                        
                        <!-- LANDING PAGE CMS TAB GROUP -->
                        <div x-show="activeCategory === 'landing'" x-transition class="space-y-1">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2.5 mb-1.5">Sections Config</span>
                            
                            <!-- Hero Tab -->
                            <button @click="setActiveTab('hero')"
                                :class="activeTab === 'hero' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">campaign</span>
                                    <span>Hero Section</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'hero' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Services Tab -->
                            <button @click="setActiveTab('services')"
                                :class="activeTab === 'services' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">design_services</span>
                                    <span>Services Catalog</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'services' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Process Tab -->
                            <button @click="setActiveTab('process')"
                                :class="activeTab === 'process' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">route</span>
                                    <span>Process Steps</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'process' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Benefits Tab -->
                            <button @click="setActiveTab('benefits')"
                                :class="activeTab === 'benefits' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">workspace_premium</span>
                                    <span>Core Benefits</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'benefits' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Pricing Tab -->
                            <button @click="setActiveTab('pricing')"
                                :class="activeTab === 'pricing' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">price_change</span>
                                    <span>Pricing Plans</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'pricing' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- FAQs Tab -->
                            <button @click="setActiveTab('faqs')"
                                :class="activeTab === 'faqs' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">quiz</span>
                                    <span>FAQ Accordion</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'faqs' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Reviews Tab -->
                            <button @click="setActiveTab('reviews')"
                                :class="activeTab === 'reviews' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">reviews</span>
                                    <span>Testimonials</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'reviews' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Location Tab -->
                            <button @click="setActiveTab('location')"
                                :class="activeTab === 'location' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">location_on</span>
                                    <span>Location Map</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'location' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Footer CTA Tab -->
                            <button @click="setActiveTab('footer_cta')"
                                :class="activeTab === 'footer_cta' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">ads_click</span>
                                    <span>Footer CTA</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'footer_cta' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>
                        </div>

                        <!-- GENERAL INFO TAB GROUP -->
                        <div x-show="activeCategory === 'general'" x-transition class="space-y-1">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2.5 mb-1.5">Platform Identity</span>
                            
                            <!-- Brand Identity Tab -->
                            <button @click="setActiveTab('site')"
                                :class="activeTab === 'site' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">id_card</span>
                                    <span>Brand Identity</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'site' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Contact Info Tab -->
                            <button @click="setActiveTab('contact')"
                                :class="activeTab === 'contact' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">contact_page</span>
                                    <span>Contact Details</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'contact' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Social Connections Tab -->
                            <button @click="setActiveTab('socials')"
                                :class="activeTab === 'socials' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">share</span>
                                    <span>Social Media</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'socials' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>

                            <!-- Auth Settings Tab -->
                            <button @click="setActiveTab('auth')"
                                :class="activeTab === 'auth' ? 'bg-[#005bc0] text-white shadow-md shadow-[#005bc0]/20 font-black' : 'text-slate-500 hover:bg-slate-55 hover:text-slate-800 font-bold'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all duration-200 text-[10px] uppercase tracking-wider group">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px]">lock_open</span>
                                    <span>Auth Pages Content</span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeTab === 'auth' ? 'bg-white' : 'bg-transparent group-hover:bg-[#005bc0]/40'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Workspace Editor and Previews Panel -->
                <div class="flex-1 p-5 md:p-6 min-w-0 bg-slate-50/50 custom-scrollbar overflow-y-auto">

                    <!-- =====================================================================
                         1. HERO SECTION SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'hero'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Editor Form (Left Col) -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Hero Banner Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Edit main title headers, button labels, and banner hero image</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">campaign</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'hero')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Configuration Updated Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'hero') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="hero">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Title Main Header</label>
                                            <input type="text" name="title_line1" x-model="heroTitle1" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Title Accent Italic</label>
                                            <input type="text" name="title_accent" x-model="heroAccent" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Subtitle / Pitch Description</label>
                                            <textarea name="subtitle" x-model="heroSubtitle" rows="2" class="w-full rounded-xl border border-slate-200 p-3 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all"></textarea>
                                        </div>
                                        <div class="space-y-1 col-span-2">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">CTA Action Button Text</label>
                                            <input type="text" name="cta_text" x-model="heroCtaText" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        
                                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-100">
                                            <div class="space-y-3">
                                                <div class="space-y-1">
                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Option A: Image URL Link</label>
                                                    <input type="text" name="image_url" x-model="imageUrl" @input="imagePreview = imageUrl" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all" placeholder="Paste direct image link...">
                                                </div>
                                                
                                                <div class="space-y-1">
                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Option B: Upload Image</label>
                                                    <div class="relative flex flex-col items-center justify-center p-3 border border-dashed border-slate-255 hover:border-[#005bc0]/50 rounded-xl bg-slate-55/50 hover:bg-slate-50 transition-all cursor-pointer">
                                                        <input type="file" name="hero_image_file" @change="const file = $event.target.files[0]; if (file) { imagePreview = URL.createObjectURL(file); }" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                                        <span class="material-symbols-outlined text-lg text-slate-400 mb-0.5">upload</span>
                                                        <span class="text-[8px] font-black text-slate-500 uppercase">Select File</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-1.5">
                                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Image Preview</label>
                                                <div class="relative rounded-xl overflow-hidden border border-slate-200 h-[105px] w-full bg-slate-100 shadow-inner flex items-center justify-center">
                                                    <template x-if="imagePreview">
                                                        <img :src="imagePreview" alt="Hero Preview" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!imagePreview">
                                                        <div class="text-center p-2">
                                                            <span class="material-symbols-outlined text-slate-400 text-xl">image</span>
                                                            <p class="text-[8px] text-slate-405 font-bold uppercase tracking-wider mt-0.5">No image</p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Banner
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Live Mockup Preview (Right Col) -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Public Banner Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-lg overflow-hidden preview-browser-frame">
                                    <div class="bg-slate-50 border-b border-slate-200 px-3 py-1.5 flex items-center justify-between">
                                        <div class="bg-white border border-slate-200/80 rounded-lg px-2 py-0.5 text-[8px] text-slate-400 font-bold w-full max-w-[200px] truncate shadow-inner flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[9px] text-emerald-500">lock</span>
                                            laundryan.com/public
                                        </div>
                                    </div>

                                    <div class="relative min-h-[220px] bg-slate-900 flex items-center px-4 py-6 overflow-hidden">
                                        <div class="absolute inset-0 z-0">
                                            <template x-if="imagePreview">
                                                <img :src="imagePreview" alt="Hero Mock BG" class="w-full h-full object-cover opacity-30">
                                            </template>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/80 to-transparent z-1"></div>

                                        <div class="relative z-10 space-y-2 max-w-[240px]">
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <template x-if="logoPreview">
                                                    <img :src="logoPreview" alt="Logo Mock" class="h-4 w-auto object-contain">
                                                </template>
                                                <span class="text-white font-black text-[9px] tracking-wider" x-text="siteName"></span>
                                            </div>

                                            <h1 class="text-xs font-black text-white leading-tight">
                                                <span x-text="heroTitle1"></span> 
                                                <span class="text-blue-400 italic font-medium" x-text="heroAccent"></span>
                                            </h1>
                                            
                                            <p class="text-[8px] text-slate-400 leading-normal font-semibold" x-text="heroSubtitle"></p>
                                            
                                            <div class="pt-1">
                                                <button class="px-3 py-1.5 bg-[#005bc0] text-white text-[8px] font-black uppercase tracking-wider rounded-lg shadow-md" x-text="heroCtaText">
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         2. SERVICES CATALOG SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'services'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor & List -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Services Catalog Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Manage list of laundry service packages and public titles</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">design_services</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'services')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Successfully Updated</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'services') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="services">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Subtitle</label>
                                            <input type="text" name="subtitle" x-model="servicesSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Heading</label>
                                            <input type="text" name="heading" x-model="servicesHeading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <!-- Dynamic Items container scrollable to limit height -->
                                    <div class="border-t border-slate-100 pt-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-[10px] font-black text-slate-850 uppercase tracking-wider">Service Cards List</h5>
                                            <button type="button" @click="addService()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-[#005bc0] text-[9px] font-black uppercase tracking-wider rounded-lg hover:bg-[#005bc0] hover:text-white transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-xs font-black">add</span>
                                                Add Card
                                            </button>
                                        </div>

                                        <div class="max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar space-y-3">
                                            <template x-for="(item, index) in services" :key="index">
                                                <div class="bg-slate-50/50 rounded-xl border border-slate-200 p-4 space-y-3 relative">
                                                    <div class="flex items-center justify-between border-b border-slate-200/50 pb-2">
                                                        <span class="text-[8px] font-black text-[#005bc0] bg-blue-50 px-2 py-0.5 rounded uppercase tracking-wider" x-text="'Card #' + (index + 1)"></span>
                                                        <button type="button" @click="removeService(index)" class="text-rose-500 hover:text-rose-700 p-0.5 rounded hover:bg-rose-50 transition-colors flex items-center gap-0.5 text-[8px] font-black uppercase tracking-wider">
                                                            <span class="material-symbols-outlined text-[10px] font-black">delete</span>
                                                            Remove
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Icon Name</label>
                                                            <input type="text" :name="'items[' + index + '][icon]'" x-model="item.icon" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Service Title</label>
                                                            <input type="text" :name="'items[' + index + '][title]'" x-model="item.title" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Description</label>
                                                        <textarea :name="'items[' + index + '][desc]'" x-model="item.desc" rows="2" class="w-full rounded-lg border border-slate-200 p-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all"></textarea>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Services
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live Interactive Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Services Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-4 space-y-4">
                                    <div class="text-center space-y-1 border-b border-slate-100 pb-2">
                                        <span class="text-[9px] font-black text-[#005bc0] uppercase tracking-widest" x-text="servicesSubtitle"></span>
                                        <h3 class="text-xs font-black text-slate-800" x-text="servicesHeading"></h3>
                                    </div>

                                    <div class="max-h-[300px] overflow-y-auto pr-1 custom-scrollbar space-y-3">
                                        <template x-for="(item, index) in services" :key="index">
                                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 flex gap-3 items-start shadow-sm">
                                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-inner shrink-0">
                                                    <span class="material-symbols-outlined text-base" x-text="item.icon || 'local_laundry_service'"></span>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-wide" x-text="item.title || 'Untitled Service'"></h4>
                                                    <p class="text-[9px] text-slate-400 font-bold leading-relaxed" x-text="item.desc || 'No description provided.'"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         3. PROCESS STEPS SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'process'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor & List -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Work Process Steps</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Edit visual workflow sequence cards and progress explanations</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">route</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'process')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Successfully Updated</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'process') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="process">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Subtitle</label>
                                            <input type="text" name="subtitle" x-model="stepsSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Heading</label>
                                            <input type="text" name="heading" x-model="stepsHeading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <!-- Scrollable dynamic steps container -->
                                    <div class="border-t border-slate-100 pt-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-[10px] font-black text-slate-850 uppercase tracking-wider">Workflow Steps List</h5>
                                            <button type="button" @click="addStep()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-[#005bc0] text-[9px] font-black uppercase tracking-wider rounded-lg hover:bg-[#005bc0] hover:text-white transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-xs font-black">add</span>
                                                Add Step
                                            </button>
                                        </div>

                                        <div class="max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar space-y-3">
                                            <template x-for="(step, index) in steps" :key="index">
                                                <div class="bg-slate-50/50 rounded-xl border border-slate-200 p-4 space-y-3 relative">
                                                    <div class="flex items-center justify-between border-b border-slate-200/50 pb-2">
                                                        <span class="text-[8px] font-black text-[#005bc0] bg-blue-50 px-2 py-0.5 rounded uppercase tracking-wider" x-text="'Step #' + (index + 1)"></span>
                                                        <button type="button" @click="removeStep(index)" class="text-rose-500 hover:text-rose-700 p-0.5 rounded hover:bg-rose-50 transition-colors flex items-center gap-0.5 text-[8px] font-black uppercase tracking-wider">
                                                            <span class="material-symbols-outlined text-[10px] font-black">delete</span>
                                                            Remove
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Icon Name</label>
                                                            <input type="text" :name="'steps[' + index + '][icon]'" x-model="step.icon" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Step Title</label>
                                                            <input type="text" :name="'steps[' + index + '][title]'" x-model="step.title" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Description</label>
                                                        <textarea :name="'steps[' + index + '][desc]'" x-model="step.desc" rows="2" class="w-full rounded-lg border border-slate-200 p-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all"></textarea>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Process
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live Steps Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Workflow Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-4 space-y-4">
                                    <div class="text-center space-y-1 border-b border-slate-100 pb-2">
                                        <span class="text-[9px] font-black text-[#005bc0] uppercase tracking-widest" x-text="stepsSubtitle"></span>
                                        <h3 class="text-xs font-black text-slate-800" x-text="stepsHeading"></h3>
                                    </div>

                                    <div class="max-h-[300px] overflow-y-auto pr-1 custom-scrollbar space-y-3">
                                        <template x-for="(step, index) in steps" :key="index">
                                            <div class="bg-slate-50 border border-slate-200/85 rounded-xl p-3 flex gap-3 items-center shadow-sm relative">
                                                <div class="absolute top-2 right-2 w-4 h-4 rounded-full bg-[#005bc0] text-white flex items-center justify-center text-[8px] font-black" x-text="index + 1"></div>
                                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-inner shrink-0">
                                                    <span class="material-symbols-outlined text-base" x-text="step.icon || 'route'"></span>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-wide" x-text="step.title || 'Untitled Step'"></h4>
                                                    <p class="text-[9px] text-slate-400 font-bold leading-normal" x-text="step.desc || 'No details provided.'"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         4. CORE BENEFITS SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'benefits'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor & List -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Core Benefits Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Configure benefits detailing why clients choose your service</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">workspace_premium</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'benefits')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Successfully Updated</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'benefits') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="benefits">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Subtitle</label>
                                            <input type="text" name="subtitle" x-model="benefitsSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Heading</label>
                                            <input type="text" name="heading" x-model="benefitsHeading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <!-- Scrollable dynamic benefits list -->
                                    <div class="border-t border-slate-100 pt-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-[10px] font-black text-slate-850 uppercase tracking-wider">Benefit Cards List</h5>
                                            <button type="button" @click="addBenefit()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-[#005bc0] text-[9px] font-black uppercase tracking-wider rounded-lg hover:bg-[#005bc0] hover:text-white transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-xs font-black">add</span>
                                                Add Benefit
                                            </button>
                                        </div>

                                        <div class="max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar space-y-3">
                                            <template x-for="(benefit, index) in benefits" :key="index">
                                                <div class="bg-slate-50/50 rounded-xl border border-slate-200 p-4 space-y-3 relative">
                                                    <input type="hidden" :name="'items[' + index + '][key]'" :value="benefit.key">
                                                    <div class="flex items-center justify-between border-b border-slate-200/50 pb-2">
                                                        <span class="text-[8px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded uppercase tracking-wider" x-text="'Benefit #' + (index + 1)"></span>
                                                        <button type="button" @click="removeBenefit(index)" class="text-rose-500 hover:text-rose-700 p-0.5 rounded hover:bg-rose-50 transition-colors flex items-center gap-0.5 text-[8px] font-black uppercase tracking-wider">
                                                            <span class="material-symbols-outlined text-[10px] font-black">delete</span>
                                                            Remove
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Icon Name</label>
                                                            <input type="text" :name="'items[' + index + '][icon]'" x-model="benefit.icon" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Benefit Title</label>
                                                            <input type="text" :name="'items[' + index + '][title]'" x-model="benefit.title" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Description</label>
                                                        <textarea :name="'items[' + index + '][desc]'" x-model="benefit.desc" rows="2" class="w-full rounded-lg border border-slate-200 p-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all"></textarea>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Benefits
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live Benefits Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Benefits Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-4 space-y-4">
                                    <div class="text-center space-y-1 border-b border-slate-100 pb-2">
                                        <span class="text-[9px] font-black text-[#005bc0] uppercase tracking-widest" x-text="benefitsSubtitle"></span>
                                        <h3 class="text-xs font-black text-slate-800" x-text="benefitsHeading"></h3>
                                    </div>

                                    <div class="max-h-[300px] overflow-y-auto pr-1 custom-scrollbar space-y-3">
                                        <template x-for="(benefit, index) in benefits" :key="index">
                                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 flex gap-3 items-start shadow-sm">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner shrink-0">
                                                    <span class="material-symbols-outlined text-base" x-text="benefit.icon || 'workspace_premium'"></span>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-wide" x-text="benefit.title || 'Advantage Title'"></h4>
                                                    <p class="text-[9px] text-slate-400 font-bold leading-normal" x-text="benefit.desc || 'Benefit description...'"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         5. PRICING PLANS SETTINGS
                         ==================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'pricing'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor & List -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Pricing Plans Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Customize service packages, prices, features and highlights badges</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">price_change</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'pricing')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Successfully Updated</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'pricing') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="pricing">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Subtitle</label>
                                            <input type="text" name="subtitle" x-model="pricingSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Heading</label>
                                            <input type="text" name="heading" x-model="pricingHeading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Description</label>
                                            <input type="text" name="desc" x-model="pricingDesc" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <!-- Scrollable dynamic pricing tiers list -->
                                    <div class="border-t border-slate-100 pt-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-[10px] font-black text-slate-850 uppercase tracking-wider">Pricing Plans List</h5>
                                            <button type="button" @click="addPlan()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-[#005bc0] text-[9px] font-black uppercase tracking-wider rounded-lg hover:bg-[#005bc0] hover:text-white transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-xs font-black">add</span>
                                                Add Plan
                                            </button>
                                        </div>

                                        <div class="max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar space-y-3">
                                            <template x-for="(plan, index) in plans" :key="index">
                                                <div class="bg-slate-50/50 rounded-xl border p-4 space-y-3 relative"
                                                    :class="plan.popular ? 'border-[#005bc0]' : 'border-slate-200'">
                                                    <div class="flex items-center justify-between border-b border-slate-200/50 pb-2">
                                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider" x-text="'Plan #' + (index + 1)"></span>
                                                        <div class="flex items-center gap-2">
                                                            <div class="flex items-center gap-1">
                                                                <input type="checkbox" :name="'plans[' + index + '][popular]'" value="1" x-model="plan.popular" class="rounded border-slate-350 text-[#005bc0] focus:ring-[#005bc0] w-3 h-3">
                                                                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Popular</span>
                                                            </div>
                                                            <button type="button" @click="removePlan(index)" class="text-rose-500 hover:text-rose-700 p-0.5 rounded hover:bg-rose-50 transition-colors">
                                                                <span class="material-symbols-outlined text-[10px] font-black">delete</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Plan Name</label>
                                                            <input type="text" :name="'plans[' + index + '][name]'" x-model="plan.name" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Plan Subtitle</label>
                                                            <input type="text" :name="'plans[' + index + '][subtitle]'" x-model="plan.subtitle" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Price / kg</label>
                                                            <input type="text" :name="'plans[' + index + '][price]'" x-model="plan.price" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Features list (comma-separated)</label>
                                                            <input type="text" :name="'plans[' + index + '][features_raw]'" x-model="plan.features_raw" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Pricing
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live Pricing Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Pricing Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-4 space-y-4">
                                    <div class="text-center space-y-1 border-b border-slate-100 pb-2">
                                        <span class="text-[9px] font-black text-[#005bc0] uppercase tracking-widest" x-text="pricingSubtitle"></span>
                                        <h3 class="text-xs font-black text-slate-800" x-text="pricingHeading"></h3>
                                    </div>

                                    <div class="max-h-[300px] overflow-y-auto pr-1 custom-scrollbar space-y-3">
                                        <template x-for="(plan, index) in plans" :key="index">
                                            <div class="bg-white border rounded-2xl p-4 space-y-3 shadow-sm relative flex flex-col justify-between"
                                                :class="plan.popular ? 'border-[#005bc0]' : 'border-slate-200/80'">
                                                <div class="space-y-2">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-wide" x-text="plan.name || 'Plan Title'"></h4>
                                                            <p class="text-[8px] text-slate-400 font-bold" x-text="plan.subtitle || 'Short details'"></p>
                                                        </div>
                                                        <template x-if="plan.popular">
                                                            <span class="bg-[#005bc0]/10 text-[#005bc0] text-[7px] font-black px-1.5 py-0.5 rounded uppercase">Best Value</span>
                                                        </template>
                                                    </div>

                                                    <div class="border-b border-slate-100 pb-2 flex items-baseline">
                                                        <span class="text-base font-black text-slate-850" x-text="plan.price || 'Rp 0'"></span>
                                                    </div>

                                                    <ul class="space-y-1">
                                                        <template x-for="feature in (plan.features_raw ? plan.features_raw.split(',') : [])" :key="feature">
                                                            <li class="flex items-center gap-1.5 text-[9px] text-slate-500 font-bold">
                                                                <span class="material-symbols-outlined text-emerald-500 text-xs font-black">check_circle</span>
                                                                <span x-text="feature.trim()"></span>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         6. FAQ SECTION SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'faqs'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor & List -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">FAQ Accordion Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Edit common questions and answers displayed at the bottom of the page</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">quiz</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'faqs')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Successfully Updated</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'faqs') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="faqs">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Subtitle</label>
                                            <input type="text" name="subtitle" x-model="faqsSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Heading</label>
                                            <input type="text" name="heading" x-model="faqsHeading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <!-- Scrollable dynamic FAQs list -->
                                    <div class="border-t border-slate-100 pt-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-[10px] font-black text-slate-850 uppercase tracking-wider">FAQ Items List</h5>
                                            <button type="button" @click="addFaq()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-[#005bc0] text-[9px] font-black uppercase tracking-wider rounded-lg hover:bg-[#005bc0] hover:text-white transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-xs font-black">add</span>
                                                Add FAQ
                                            </button>
                                        </div>

                                        <div class="max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar space-y-3">
                                            <template x-for="(faq, index) in faqs" :key="index">
                                                <div class="bg-slate-50/50 rounded-xl border border-slate-200 p-4 space-y-3 relative">
                                                    <div class="flex items-center justify-between border-b border-slate-200/50 pb-2">
                                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider" x-text="'FAQ #' + (index + 1)"></span>
                                                        <button type="button" @click="removeFaq(index)" class="text-rose-500 hover:text-rose-700 p-0.5 rounded hover:bg-rose-50 transition-colors flex items-center gap-0.5 text-[8px] font-black uppercase tracking-wider">
                                                            <span class="material-symbols-outlined text-[10px] font-black">delete</span>
                                                            Remove
                                                        </button>
                                                    </div>
                                                    <div class="space-y-1.5">
                                                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Question Text</label>
                                                        <input type="text" :name="'items[' + index + '][question]'" x-model="faq.question" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                    </div>
                                                    <div class="space-y-1.5">
                                                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Answer Text</label>
                                                        <textarea :name="'items[' + index + '][answer]'" x-model="faq.answer" rows="2" class="w-full rounded-lg border border-slate-200 p-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all"></textarea>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save FAQs
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live FAQ Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live FAQs Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-4 space-y-4">
                                    <div class="text-center space-y-1 border-b border-slate-100 pb-2">
                                        <span class="text-[9px] font-black text-[#005bc0] uppercase tracking-widest" x-text="faqsSubtitle"></span>
                                        <h3 class="text-xs font-black text-slate-800" x-text="faqsHeading"></h3>
                                    </div>

                                    <div class="max-h-[300px] overflow-y-auto pr-1 custom-scrollbar space-y-3.5">
                                        <template x-for="(faq, index) in faqs" :key="index">
                                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 space-y-1 shadow-sm">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="text-[9px] font-black text-slate-800 uppercase" x-text="faq.question || 'Untitled Question'"></h4>
                                                    <span class="material-symbols-outlined text-[#005bc0] text-[12px] font-black">expand_more</span>
                                                </div>
                                                <p class="text-[9px] text-slate-400 font-bold border-t border-slate-200/50 pt-1 leading-normal" x-text="faq.answer || 'Answer details...'"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         7. TESTIMONIALS SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'reviews'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor & List -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Testimonials Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Manage details of public review cards, client roles and avatar icons</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">reviews</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'reviews')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Successfully Updated</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'reviews') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="reviews">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Subtitle</label>
                                            <input type="text" name="subtitle" x-model="reviewsSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Heading</label>
                                            <input type="text" name="heading" x-model="reviewsHeading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <!-- Scrollable dynamic testimonials list -->
                                    <div class="border-t border-slate-100 pt-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-[10px] font-black text-slate-850 uppercase tracking-wider">Customer Reviews List</h5>
                                            <button type="button" @click="addReview()" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-[#005bc0] text-[9px] font-black uppercase tracking-wider rounded-lg hover:bg-[#005bc0] hover:text-white transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-xs font-black">add</span>
                                                Add Review
                                            </button>
                                        </div>

                                        <div class="max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar space-y-4">
                                            <template x-for="(review, index) in reviews" :key="index">
                                                <div class="bg-slate-50/50 rounded-xl border border-slate-200 p-4 space-y-3 relative">
                                                    <div class="flex items-center justify-between border-b border-slate-200/50 pb-2">
                                                        <span class="text-[8px] font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded uppercase tracking-wider" x-text="'Review #' + (index + 1)"></span>
                                                        <button type="button" @click="removeReview(index)" class="text-rose-500 hover:text-rose-700 p-0.5 rounded hover:bg-rose-50 transition-colors flex items-center gap-0.5 text-[8px] font-black uppercase tracking-wider">
                                                            <span class="material-symbols-outlined text-[10px] font-black">delete</span>
                                                            Remove
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Client Full Name</label>
                                                            <input type="text" :name="'items[' + index + '][name]'" x-model="review.name" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Client Role</label>
                                                            <input type="text" :name="'items[' + index + '][role]'" x-model="review.role" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-2 gap-3 pt-1">
                                                        <div class="space-y-2">
                                                            <div class="space-y-1">
                                                                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Avatar Link</label>
                                                                <input type="text" :name="'items[' + index + '][avatar]'" x-model="review.avatar" @input="review.localPreview = review.avatar" class="w-full rounded-lg border border-slate-200 px-2 py-1 text-[10px] font-bold text-slate-700 bg-white focus:outline-none shadow-sm" placeholder="Paste URL...">
                                                            </div>
                                                            <div class="space-y-1">
                                                                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Upload File</label>
                                                                <div class="relative flex flex-col items-center justify-center p-1 border border-dashed border-slate-300 rounded-lg bg-white hover:bg-slate-50 transition-all cursor-pointer">
                                                                    <input type="file" :name="'items[' + index + '][avatar_file]'" @change="const file = $event.target.files[0]; if (file) { review.localPreview = URL.createObjectURL(file); }" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                                                    <span class="text-[7px] font-black text-slate-500 uppercase">Upload</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col items-center justify-center border border-slate-200 rounded-lg bg-white p-2">
                                                            <img :src="review.localPreview || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(review.name || 'User') + '&background=005bc0&color=fff'" alt="Avatar Preview" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="space-y-1">
                                                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Review Quote</label>
                                                        <textarea :name="'items[' + index + '][text]'" x-model="review.text" rows="2" class="w-full rounded-lg border border-slate-200 p-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-1 focus:ring-[#005bc0] shadow-sm transition-all"></textarea>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Reviews
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live Testimonials Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Reviews Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-4 space-y-4">
                                    <div class="text-center space-y-1 border-b border-slate-100 pb-2">
                                        <span class="text-[9px] font-black text-[#005bc0] uppercase tracking-widest" x-text="reviewsSubtitle"></span>
                                        <h3 class="text-xs font-black text-slate-800" x-text="reviewsHeading"></h3>
                                    </div>

                                    <div class="max-h-[300px] overflow-y-auto pr-1 custom-scrollbar space-y-3">
                                        <template x-for="(review, index) in reviews" :key="index">
                                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 space-y-3 shadow-sm flex flex-col justify-between">
                                                <p class="text-[9px] text-slate-550 font-bold italic leading-relaxed" x-text="'“' + (review.text || 'Client feedback description...') + '”'"></p>
                                                
                                                <div class="flex items-center gap-2 pt-2 border-t border-slate-100/60">
                                                    <img :src="review.localPreview || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(review.name || 'User') + '&background=005bc0&color=fff'" alt="Client Mock" class="w-7 h-7 rounded-full object-cover shadow-sm">
                                                    <div>
                                                        <h5 class="text-[9px] font-black text-slate-800 uppercase tracking-wide" x-text="review.name || 'Anonymous Client'"></h5>
                                                        <p class="text-[8px] text-slate-400 font-bold" x-text="review.role || 'Verified Customer'"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         8. LOCATION MAP SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'location'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Location Map Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Embed Google Maps code to show your physical stores</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">location_on</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'location')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Configuration Updated Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'location') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="location">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Subtitle</label>
                                            <input type="text" name="subtitle" x-model="locationSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Section Heading</label>
                                            <input type="text" name="heading" x-model="locationHeading" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Google Maps Iframe Embed HTML</label>
                                            <textarea name="map_iframe" x-model="mapIframe" rows="3" class="w-full rounded-xl border border-slate-200 p-3 text-xs font-mono font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all" placeholder='<iframe src="..."></iframe>'></textarea>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Location
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Map Live Preview -->
                            <div class="xl:col-span-5 space-y-3" x-show="mapIframe">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Map Preview</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-4 space-y-3">
                                    <div class="text-center space-y-0.5 border-b border-slate-100 pb-2">
                                        <span class="text-[9px] font-black text-[#005bc0] uppercase tracking-widest" x-text="locationSubtitle"></span>
                                        <h3 class="text-xs font-black text-slate-800" x-text="locationHeading"></h3>
                                    </div>
                                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-inner h-48 w-full bg-slate-100 [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0" x-html="mapIframe">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         9. FOOTER CTA SECTION SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'landing' && activeTab === 'footer_cta'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Footer CTA Banner Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Customize final promotional order banners at the bottom of landing</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">ads_click</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'footer_cta')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Configuration Updated Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'footer') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="footer_cta">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Final CTA Main Title</label>
                                            <input type="text" name="cta_title" x-model="footerCtaTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Final CTA Button Text</label>
                                            <input type="text" name="cta_button" x-model="footerCtaButton" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Final CTA Subtitle / Pitch text</label>
                                            <textarea name="cta_subtitle" x-model="footerCtaSubtitle" rows="2" class="w-full rounded-xl border border-slate-200 p-3 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all"></textarea>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save CTA Banner
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live CTA Banner Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live CTA Preview</span>
                                <div class="bg-[#005bc0] rounded-[20px] p-5 text-center text-white relative overflow-hidden shadow-lg">
                                    <div class="max-w-xl mx-auto space-y-2 relative z-10">
                                        <h3 class="text-xs font-black uppercase tracking-tight" x-text="footerCtaTitle"></h3>
                                        <p class="text-[9px] text-blue-100 leading-relaxed font-bold max-w-xs mx-auto" x-text="footerCtaSubtitle"></p>
                                        <div class="pt-2">
                                            <button class="px-4 py-2 bg-white text-[#005bc0] text-[8px] font-black uppercase tracking-wider rounded-lg shadow-sm" x-text="footerCtaButton">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         10. BRAND IDENTITY SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'general' && activeTab === 'site'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Editor Form (Left Col) -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Brand Identity Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Configure site brand names and logo asset files</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">id_card</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'site')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Configuration Updated Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'site') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="site">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Site brand name</label>
                                            <input type="text" name="name" x-model="siteName" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Logo File Preview</label>
                                            <div class="flex items-center p-2.5 bg-slate-50 border border-slate-200 rounded-xl min-h-[36px] justify-center shadow-inner">
                                                <template x-if="logoPreview">
                                                    <img :src="logoPreview" alt="Brand Logo Preview" class="h-6 w-auto object-contain">
                                                </template>
                                                <template x-if="!logoPreview">
                                                    <span class="text-[9px] font-black text-slate-400 italic">Using text brand instead.</span>
                                                </template>
                                            </div>
                                        </div>
                                        
                                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-100">
                                            <div class="space-y-1">
                                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Option A: Logo Asset URL</label>
                                                <input type="text" name="logo_url" x-model="logoUrl" @input="logoPreview = logoUrl" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all" placeholder="Paste image link...">
                                            </div>
                                            
                                            <div class="space-y-1">
                                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Option B: Upload File</label>
                                                <div class="relative flex flex-col items-center justify-center p-2 border border-dashed border-slate-350 rounded-xl bg-slate-55/50 hover:bg-slate-50 transition-all cursor-pointer">
                                                    <input type="file" name="logo_file" @change="const file = $event.target.files[0]; if (file) { logoPreview = URL.createObjectURL(file); }" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                                    <span class="material-symbols-outlined text-lg text-slate-400 mb-0.5">upload</span>
                                                    <span class="text-[8px] font-black text-slate-555 uppercase">Upload logo</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Identity
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Live Header Preview Mockup (Right Col) -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Header Preview</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden preview-browser-frame">
                                    <div class="bg-white px-4 py-3 flex items-center justify-between border-b border-slate-100">
                                        <div class="flex items-center gap-1.5">
                                            <template x-if="logoPreview">
                                                <img :src="logoPreview" alt="Brand Logo Mock" class="h-4 w-auto object-contain">
                                            </template>
                                            <span class="text-slate-800 font-black text-[9px] uppercase tracking-wider" x-text="siteName"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] font-black text-slate-400 uppercase">Home</span>
                                            <span class="text-[8px] font-black text-[#005bc0] bg-blue-50 px-2 py-1.5 rounded-lg uppercase">Book</span>
                                        </div>
                                    </div>
                                    <div class="p-4 text-center bg-slate-50/50">
                                        <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Public content area</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         11. CONTACT DETAILS SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'general' && activeTab === 'contact'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Contact Details & Metadata</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Customize corporate phone numbers, business email, physical address, and copyright text</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">contact_page</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'contact')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Changes Saved Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'footer') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="contact">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Phone Number</label>
                                            <input type="text" name="phone" x-model="footerPhone" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all" placeholder="+62 ...">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Email Address</label>
                                            <input type="email" name="email" x-model="footerEmail" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all" placeholder="info@laundryan.com">
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Physical Head Office Address</label>
                                            <input type="text" name="address" x-model="footerAddress" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all" placeholder="Enter physical address...">
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Brand Mission Pitch (Footer Column 1)</label>
                                            <textarea name="mission" x-model="footerMission" rows="2" class="w-full rounded-xl border border-slate-200 p-3 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all" placeholder="Short description of your brand's core mission..."></textarea>
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Copyright Bottom Text</label>
                                            <input type="text" name="copyright" x-model="footerCopyright" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Details
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live Footer Preview Mockup -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Footer Preview</span>
                                <div class="bg-slate-900 rounded-3xl p-5 text-white space-y-4 shadow-md">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <template x-if="logoPreview">
                                                <img :src="logoPreview" alt="Logo Mock" class="h-4 w-auto object-contain">
                                            </template>
                                            <span class="text-white font-black text-[10px] uppercase tracking-wider" x-text="siteName"></span>
                                        </div>
                                        <p class="text-[8px] text-slate-405 font-bold leading-relaxed" x-text="footerMission"></p>
                                    </div>
                                    
                                    <div class="space-y-1.5 border-t border-slate-800 pt-3">
                                        <h4 class="text-[8px] font-black text-blue-400 uppercase tracking-widest">Contact details</h4>
                                        <div class="space-y-1 text-[8px] font-bold text-slate-400">
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[10px] font-black">call</span>
                                                <span x-text="footerPhone"></span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[10px] font-black">mail</span>
                                                <span x-text="footerEmail"></span>
                                            </div>
                                            <div class="flex items-start gap-1">
                                                <span class="material-symbols-outlined text-[10px] font-black mt-0.5">location_on</span>
                                                <span x-text="footerAddress"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-[7px] text-slate-500 font-bold border-t border-slate-800 pt-3" x-text="footerCopyright"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         12. SOCIAL MEDIA CONNECTIONS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'general' && activeTab === 'socials'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            
                            <!-- Left Column: Form Editor -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Social Media Connections</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Link your official social media pages for Facebook, Instagram, and Twitter (X)</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">share</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'socials')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Changes Saved Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'footer') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="socials">
                                    <div class="space-y-3">
                                        <!-- Facebook -->
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Facebook Profile Link</label>
                                            <div class="relative flex items-center">
                                                <div class="absolute left-3 text-slate-400 pointer-events-none text-[10px] font-bold">https://facebook.com/</div>
                                                <input type="text" name="facebook_url" x-model="footerFacebook" class="w-full rounded-xl border border-slate-200 pl-32 pr-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                            </div>
                                        </div>
                                        <!-- Instagram -->
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Instagram Profile Link</label>
                                            <div class="relative flex items-center">
                                                <div class="absolute left-3 text-slate-400 pointer-events-none text-[10px] font-bold">https://instagram.com/</div>
                                                <input type="text" name="instagram_url" x-model="footerInstagram" class="w-full rounded-xl border border-slate-200 pl-32 pr-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                            </div>
                                        </div>
                                        <!-- Twitter / X -->
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Twitter / X Profile Link</label>
                                            <div class="relative flex items-center">
                                                <div class="absolute left-3 text-slate-400 pointer-events-none text-[10px] font-bold">https://x.com/</div>
                                                <input type="text" name="x_url" x-model="footerX" class="w-full rounded-xl border border-slate-200 pl-20 pr-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Socials
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Column: Live Socials Preview -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Live Socials Mockup</span>
                                <div class="bg-slate-900 rounded-3xl p-4 flex items-center justify-between shadow-md">
                                    <div class="text-[8px] font-bold text-slate-400 uppercase" x-text="'Follow ' + siteName"></div>
                                    <div class="flex items-center gap-2">
                                        <a :href="'https://facebook.com/' + footerFacebook" target="_blank" class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-white">
                                            <span class="material-symbols-outlined text-xs">public</span>
                                        </a>
                                        <a :href="'https://instagram.com/' + footerInstagram" target="_blank" class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-white">
                                            <span class="material-symbols-outlined text-xs">photo_camera</span>
                                        </a>
                                        <a :href="'https://x.com/' + footerX" target="_blank" class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-white">
                                            <span class="material-symbols-outlined text-xs">share</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =====================================================================
                         13. AUTHENTICATION PAGES SETTINGS
                         ===================================================================== -->
                    <div x-show="activeCategory === 'general' && activeTab === 'auth'" x-transition class="space-y-6">
                        <!-- Login settings -->
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            <!-- Editor Form (Left Col) -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Login Page Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Customize titles and subtitles on the sign-in page</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">login</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'login')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Login Settings Updated Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'login') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="login">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Left Branding Title</label>
                                            <input type="text" name="left_title" x-model="loginLeftTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Left Branding Subtitle</label>
                                            <textarea name="left_subtitle" x-model="loginLeftSubtitle" rows="2" class="w-full rounded-xl border border-slate-200 p-3 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all"></textarea>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Right Form Title</label>
                                            <input type="text" name="right_title" x-model="loginRightTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Right Form Subtitle</label>
                                            <input type="text" name="right_subtitle" x-model="loginRightSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Login Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Preview (Right Col) -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Login Page Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden preview-browser-frame">
                                    <div class="bg-slate-50 border-b border-slate-200 px-3 py-1.5 text-[8px] text-slate-400 font-bold">
                                        laundryan.com/login
                                    </div>
                                    <div class="flex h-48">
                                        <div class="w-5/12 bg-[#005bc0] p-3 flex flex-col justify-center text-white">
                                            <h5 class="text-[8px] font-black leading-tight" x-text="loginLeftTitle"></h5>
                                            <p class="text-[6px] text-white/70 mt-1 leading-normal" x-text="loginLeftSubtitle"></p>
                                        </div>
                                        <div class="w-7/12 p-3 flex flex-col justify-center bg-white">
                                            <h5 class="text-[8px] font-black text-slate-800" x-text="loginRightTitle"></h5>
                                            <p class="text-[6px] text-slate-400" x-text="loginRightSubtitle"></p>
                                            <div class="mt-2 space-y-1">
                                                <div class="h-2 bg-slate-100 rounded"></div>
                                                <div class="h-2 bg-slate-100 rounded"></div>
                                                <div class="h-4 bg-[#005bc0] rounded"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Register settings -->
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            <!-- Editor Form (Left Col) -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Registration Page Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Customize titles and subtitles on the sign-up page</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">person_add</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'register')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Registration Settings Updated Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'register') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="register">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Left Branding Title</label>
                                            <input type="text" name="left_title" x-model="registerLeftTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Left Branding Subtitle</label>
                                            <textarea name="left_subtitle" x-model="registerLeftSubtitle" rows="2" class="w-full rounded-xl border border-slate-200 p-3 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all"></textarea>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Right Form Title</label>
                                            <input type="text" name="right_title" x-model="registerRightTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Right Form Subtitle</label>
                                            <input type="text" name="right_subtitle" x-model="registerRightSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Register Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Preview (Right Col) -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Register Page Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden preview-browser-frame">
                                    <div class="bg-slate-50 border-b border-slate-200 px-3 py-1.5 text-[8px] text-slate-400 font-bold">
                                        laundryan.com/register
                                    </div>
                                    <div class="flex h-48">
                                        <div class="w-5/12 bg-[#005bc0] p-3 flex flex-col justify-center text-white">
                                            <h5 class="text-[8px] font-black leading-tight" x-text="registerLeftTitle"></h5>
                                            <p class="text-[6px] text-white/70 mt-1 leading-normal" x-text="registerLeftSubtitle"></p>
                                        </div>
                                        <div class="w-7/12 p-3 flex flex-col justify-center bg-white">
                                            <h5 class="text-[8px] font-black text-slate-800" x-text="registerRightTitle"></h5>
                                            <p class="text-[6px] text-slate-400" x-text="registerRightSubtitle"></p>
                                            <div class="mt-2 space-y-1">
                                                <div class="h-2 bg-slate-100 rounded"></div>
                                                <div class="h-2 bg-slate-100 rounded"></div>
                                                <div class="h-4 bg-[#005bc0] rounded"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Forgot Password settings -->
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                            <!-- Editor Form (Left Col) -->
                            <div class="xl:col-span-7 bg-white rounded-3xl border border-slate-200/70 p-5 md:p-6 space-y-4 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Forgot Password Settings</h4>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Customize titles and subtitles on the password recovery page</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#005bc0] flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-base">lock_reset</span>
                                    </div>
                                </div>

                                @if(session('updated_key') === 'forgot_password')
                                    <div x-data="{ showBadge: true }" x-show="showBadge" x-init="setTimeout(() => showBadge = false, 4000)" 
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm mb-3">
                                        <span class="material-symbols-outlined text-[14px] font-black text-emerald-600">check_circle</span>
                                        <span>Forgot Password Settings Updated Successfully</span>
                                    </div>
                                @endif

                                <form action="{{ route('admin.landing-page.update', 'forgot_password') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="section_subtype" value="forgot_password">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Left Branding Title</label>
                                            <input type="text" name="left_title" x-model="forgotPasswordLeftTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Left Branding Subtitle</label>
                                            <textarea name="left_subtitle" x-model="forgotPasswordLeftSubtitle" rows="2" class="w-full rounded-xl border border-slate-200 p-3 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all"></textarea>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Right Form Title</label>
                                            <input type="text" name="right_title" x-model="forgotPasswordRightTitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Right Form Subtitle</label>
                                            <input type="text" name="right_subtitle" x-model="forgotPasswordRightSubtitle" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 bg-white focus:outline-none focus:border-[#005bc0] focus:ring-2 focus:ring-[#005bc0]/10 shadow-sm transition-all">
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#005bc0] text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-[#004899] transition-all">
                                            <span class="material-symbols-outlined text-xs">save</span>
                                            Save Forgot Password Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Preview (Right Col) -->
                            <div class="xl:col-span-5 space-y-3">
                                <span class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.25em] px-2">Forgot Password Mockup</span>
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden preview-browser-frame">
                                    <div class="bg-slate-50 border-b border-slate-200 px-3 py-1.5 text-[8px] text-slate-400 font-bold">
                                        laundryan.com/password/reset
                                    </div>
                                    <div class="flex h-48">
                                        <div class="w-5/12 bg-[#005bc0] p-3 flex flex-col justify-center text-white">
                                            <h5 class="text-[8px] font-black leading-tight" x-text="forgotPasswordLeftTitle"></h5>
                                            <p class="text-[6px] text-white/70 mt-1 leading-normal" x-text="forgotPasswordLeftSubtitle"></p>
                                        </div>
                                        <div class="w-7/12 p-3 flex flex-col justify-center bg-white">
                                            <h5 class="text-[8px] font-black text-slate-800" x-text="forgotPasswordRightTitle"></h5>
                                            <p class="text-[6px] text-slate-400 mb-1" x-text="forgotPasswordRightSubtitle"></p>
                                            <div class="mt-2 space-y-1">
                                                <div class="h-2 bg-slate-100 rounded"></div>
                                                <div class="h-4 bg-[#005bc0] rounded"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
