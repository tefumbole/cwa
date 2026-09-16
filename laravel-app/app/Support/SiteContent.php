<?php

namespace App\Support;

use App\SiteSetting;

/**
 * Editable front-end content. Values are stored in site_settings under the
 * "content." prefix. The schema() drives the admin editor and the defaults
 * keep the public site unchanged until an admin overrides a field.
 */
class SiteContent
{
    /** Raw stored value for a content key, or the given default. */
    public static function get($key, $default = '')
    {
        $val = SiteSetting::getValue('content.' . $key, null);

        return ($val === null || $val === '') ? $default : $val;
    }

    public static function text($key, $default = '')
    {
        $stored = SiteSetting::getValue('content.' . $key, null);
        $translated = trans('cwa.'.$key);
        $hasTrans = is_string($translated) && $translated !== 'cwa.'.$key;
        $schemaDefault = self::schemaDefaultFor($key);

        $isCustom = is_string($stored) && $stored !== ''
            && $stored !== $schemaDefault
            && $stored !== $default;

        if ($isCustom) {
            return $stored;
        }
        if ($hasTrans) {
            return $translated;
        }

        return ($stored !== null && $stored !== '') ? $stored : $default;
    }

    private static function schemaDefaultFor($key)
    {
        $parts = explode('.', $key, 2);
        if (count($parts) !== 2) {
            return null;
        }
        $schema = self::schema();
        if (! isset($schema[$parts[0]]['fields'][$parts[1]][2])) {
            return null;
        }

        return $schema[$parts[0]]['fields'][$parts[1]][2];
    }

    public static function html($key, $default = '')
    {
        return self::get($key, $default);
    }

    /** Resolve an image field to a usable URL, falling back to the default. */
    public static function image($key, $default = '')
    {
        $val = SiteSetting::getValue('content.' . $key, null);
        if (! $val) {
            return $default;
        }
        if (preg_match('#^(https?:)?//#', $val)) {
            return $val;
        }
        // This nginx root is laravel-app/, so public files live under /public/...
        if (strpos($val, '/branding/') === 0 || strpos($val, 'branding/') === 0) {
            return url('public/' . ltrim($val, '/'));
        }
        if (strpos($val, '/') === 0) {
            return $val;
        }

        return url('public/' . ltrim($val, '/'));
    }

    /** Persist a scalar content value. */
    public static function put($key, $value)
    {
        SiteSetting::setValue('content.' . $key, $value);
    }

    /**
     * Editable page schema. Each page: label, url, and fields keyed by name.
     * Field: [type, label, default]. type in {text, textarea, html, image}.
     */
    public static function schema()
    {
        return [
            'home' => [
                'label' => 'Home',
                'url' => '/home',
                'fields' => [
                    'hero_title'            => ['html', 'Hero title (HTML allowed)', 'Your Technology Bridge to <span class="text-brand-gold">Kigali</span>'],
                    'hero_subtitle'         => ['textarea', 'Hero subtitle', 'Professional IT Consultancy, Enterprise Networking, and Audio-Visual Production, Cloud, AI and Cyber'],
                    'hero_image'            => ['image', 'Hero background image', '/branding/beyond-hero.png'],
                    'cta_primary'           => ['text', 'Hero primary button text', 'Get a Free Quote'],
                    'services_heading'      => ['text', 'Services heading', 'Our Services'],
                    'services_subheading'   => ['text', 'Services subheading', 'Comprehensive technology solutions for your needs'],
                    'why_heading'           => ['text', 'Why-us heading', 'Why Beyond Enterprise?'],
                    'why_subheading'        => ['text', 'Why-us subheading', 'Excellence in every solution we deliver'],
                    'industries_heading'    => ['text', 'Industries heading', 'Industries We Serve'],
                    'industries_subheading' => ['text', 'Industries subheading', 'Trusted by diverse organizations across Africa and the World'],
                    'testimonials_heading'  => ['text', 'Testimonials heading', 'What Our Clients Say'],
                    'testimonials_subheading' => ['text', 'Testimonials subheading', 'Trusted by businesses and organizations across Kigali'],
                    'cta_heading'           => ['text', 'Bottom CTA heading', 'Ready to Get Started?'],
                    'cta_text'              => ['textarea', 'Bottom CTA text', 'Contact us today for a consultation and let us bridge your technology needs.'],
                ],
            ],
            'trainings' => [
                'label' => 'Training',
                'url' => '/trainings',
                'fields' => [
                    'page_title'          => ['text', 'Browser tab title', 'Professional IT Training Programs 2026'],
                    'hero_title'          => ['html', 'Hero title (HTML allowed)', 'Professional <span class="text-brand-gold">IT Training</span>'],
                    'hero_subtitle'       => ['textarea', 'Hero subtitle', 'Master cutting-edge technologies with industry-leading programs'],
                    'hero_tagline'        => ['textarea', 'Hero tagline', 'Hands-on training in AI, Cloud, Security, Networking, and more — designed for 2026 and beyond'],
                    'cta_programs'        => ['text', 'Explore programs button', 'Explore Programs'],
                    'cta_register'        => ['text', 'Register button', 'Register Now'],
                    'stat2_value'         => ['text', 'Stat 2 value', '8-14'],
                    'stat2_label'         => ['text', 'Stat 2 label', 'Weeks Duration'],
                    'stat3_value'         => ['text', 'Stat 3 value', '100%'],
                    'stat3_label'         => ['text', 'Stat 3 label', 'Hands-on Labs'],
                    'stat4_value'         => ['text', 'Stat 4 value', '24/7'],
                    'stat4_label'         => ['text', 'Stat 4 label', 'Support Access'],
                    'stat1_label'         => ['text', 'Stat 1 label (count is automatic)', 'Training Programs'],
                    'programs_heading'    => ['text', 'Courses heading', 'Our Courses'],
                    'programs_subheading' => ['textarea', 'Courses subheading', 'Courses managed in Course Manager appear here. Select a program to explore the curriculum and register.'],
                    'empty_heading'       => ['text', 'Empty-state heading', 'No courses published yet'],
                    'empty_text'          => ['textarea', 'Empty-state text', 'Active courses from Course Manager will appear here under Training.'],
                    'why_heading'         => ['text', 'Why-us heading', 'Why Train With Us?'],
                    'why1_title'          => ['text', 'Why card 1 title', 'Industry Experts'],
                    'why1_text'           => ['text', 'Why card 1 text', 'Learn from certified professionals with real-world experience'],
                    'why2_title'          => ['text', 'Why card 2 title', 'Hands-On Labs'],
                    'why2_text'           => ['text', 'Why card 2 text', 'Practical training with real equipment and enterprise tools'],
                    'why3_title'          => ['text', 'Why card 3 title', 'Career Support'],
                    'why3_text'           => ['text', 'Why card 3 text', 'Job placement assistance and certification preparation'],
                    'cta_heading'         => ['text', 'Bottom CTA heading', 'Ready to Transform Your Career?'],
                    'cta_text'            => ['textarea', 'Bottom CTA text', 'Join thousands of professionals who have upgraded their skills with Beyond Enterprise'],
                    'cta_button'          => ['text', 'Bottom CTA button', 'Enroll Now — Limited Seats Available'],
                    'cta_email'           => ['text', 'Bottom CTA email', 'info@beyondtechworld.com'],
                    'cta_phone'           => ['text', 'Bottom CTA phone', '+237 675 321 739'],
                ],
            ],
            'events' => [
                'label' => 'Calendar',
                'url' => '/calendar',
                'fields' => [
                    'page_title'    => ['text', 'Browser tab title', 'Calendar'],
                    'hero_title'    => ['text', 'Page heading', 'CWA Calendar'],
                    'hero_subtitle' => ['textarea', 'Page subtitle', 'Congresses, retreats, feast days and gatherings published by CWA Cameroon.'],
                    'empty_heading' => ['text', 'Empty-state heading', 'No events in this period'],
                    'empty_text'    => ['textarea', 'Empty-state text', 'Published events from the admin calendar appear here. Check another month or the yearly list.'],
                ],
            ],
            'rentals' => [
                'label' => 'Rentals',
                'url' => '/rentals',
                'fields' => [
                    'page_title'    => ['text', 'Browser tab title', 'Equipment Rentals'],
                    'hero_title'    => ['text', 'Hero title', 'Equipment Rentals'],
                    'hero_subtitle' => ['textarea', 'Hero subtitle', 'Submit a booking request — our team will confirm availability and follow up on WhatsApp.'],
                ],
            ],
            'register' => [
                'label' => 'Register Now',
                'url' => '/register-now',
                'fields' => [
                    'page_title'       => ['text', 'Browser tab title', 'Register Now'],
                    'hero_title'       => ['text', 'Hero title', 'Register Now'],
                    'hero_subtitle'    => ['textarea', 'Hero subtitle', 'Join Beyond Enterprise and elevate your skills with our premium courses. Select your courses below to get started.'],
                    'hero_image'       => ['image', 'Hero background image', 'https://images.unsplash.com/photo-1693045181224-9fc2f954f054'],
                    'courses_heading'  => ['text', 'Courses column heading', 'Select Courses'],
                    'details_heading'  => ['text', 'Details column heading', 'Your Details'],
                    'submit_button'    => ['text', 'Submit button', 'Submit Registration'],
                ],
            ],
            'apply' => [
                'label' => 'Apply Now',
                'url' => '/apply-now',
                'fields' => [
                    'page_title'             => ['text', 'Browser tab title', 'Apply Now — Jobs & Internships'],
                    'hero_title'             => ['text', 'Hero title', 'Apply Now'],
                    'hero_subtitle'          => ['textarea', 'Hero subtitle', 'Browse real jobs and internship adverts — then apply online in minutes.'],
                    'jobs_title'             => ['text', 'Jobs section title', 'Jobs'],
                    'jobs_subtitle'          => ['text', 'Jobs section subtitle', 'Paid roles with salary details.'],
                    'jobs_empty'             => ['text', 'Jobs empty heading', 'No jobs available'],
                    'jobs_empty_hint'        => ['textarea', 'Jobs empty hint', 'Active job postings will appear here.'],
                    'internships_title'      => ['text', 'Internships section title', 'Internships'],
                    'internships_subtitle'   => ['text', 'Internships section subtitle', 'Unpaid internship adverts for students.'],
                    'internships_empty'      => ['text', 'Internships empty heading', 'No internships available'],
                    'internships_empty_hint' => ['textarea', 'Internships empty hint', 'Active internship adverts will appear here.'],
                ],
            ],
            'permissions' => [
                'label' => 'Permissions',
                'url' => '/permissions',
                'fields' => [
                    'page_title'          => ['text', 'Browser tab title', 'Apply for Permission'],
                    'kicker'              => ['text', 'Small label above title', 'Permission request'],
                    'hero_title'          => ['text', 'Hero title', 'Apply for Permission'],
                    'hero_subtitle'       => ['textarea', 'Hero subtitle', 'WhatsApp first. We find your name, you add job title, subject and reason, then verify with a code.'],
                    'progress_heading'    => ['text', 'Progress panel heading', 'Your progress'],
                    'progress_subheading' => ['text', 'Progress panel subtitle', 'Each stage lights up as you complete it.'],
                ],
            ],
            'about' => [
                'label' => 'About',
                'url' => '/about',
                'fields' => [
                    'hero_title'          => ['text', 'Hero title', 'CWA Cameroon'],
                    'hero_subtitle'       => ['textarea', 'Hero subtitle', 'Faith, service and sisterhood — a brighter tomorrow for Catholic women and families in Cameroon.'],
                    'vision_heading'      => ['text', 'Vision heading', 'Our Vision'],
                    'vision_text'         => ['textarea', 'Vision text', 'To bear witness to Christ while committing to the holistic development of Catholic women and families in Cameroon.'],
                    'mission_heading'     => ['text', 'Mission heading', 'Our Mission'],
                    'mission_text'        => ['textarea', 'Mission text', 'To empower members spiritually, nurturing stronger faith and promoting evangelization within families and society.'],
                    'objectives_heading'  => ['text', 'Objectives heading', 'Our Objectives'],
                    'about_image'         => ['image', 'Hero image', '/public/branding/cwa-about-home.jpg'],
                    'leadership_heading'  => ['text', 'Leadership heading', 'Our Leadership'],
                    'leadership_subtext'  => ['text', 'Leadership subtext', 'Women serving the Church and families across Cameroon'],
                    'cta_heading'         => ['text', 'CTA heading', 'Walk with us'],
                    'cta_text'            => ['text', 'CTA text', 'Together we build a new home for faith, service and sisterhood.'],
                    'story_heading'       => ['text', 'Story heading', 'Who We Are'],
                    'story_text'          => ['textarea', 'Story text', 'Founded in 1964 in Buea by Mama Anna Foncha with ten Catholic women, CWA Cameroon has grown into a nationwide apostolate of more than 18,000 members. The National Seat is in Bamenda. We are a Catholic, lay, non-profit and apolitical association of women serving the Church, the family and society.'],
                    'motto_text'          => ['textarea', 'Motto', 'The Son of Man came not to be served but to serve.'],
                    'motto_ref'           => ['text', 'Motto scripture', 'Matthew 20:28'],
                    'patron_title'        => ['text', 'Patron', 'Our Lady of the Immaculate Conception'],
                    'patron_feast'        => ['text', 'Patron feast', '8 December'],
                    'programs_heading'    => ['text', 'Programs heading', 'Programs & Impact'],
                    'structure_heading'   => ['text', 'Structure heading', 'Our Structure'],
                ],
            ],
            'resources' => [
                'label' => 'Resources',
                'url' => '/documents',
                'fields' => [
                    'page_title'    => ['text', 'Browser tab title', 'Resources'],
                    'hero_title'    => ['text', 'Page heading', 'Resources'],
                    'hero_subtitle' => ['textarea', 'Page subtitle', 'Official CWA Cameroon documents for members, parishes and partners.'],
                ],
            ],
            'join' => [
                'label' => 'Join CWA',
                'url' => '/membership',
                'fields' => [
                    'page_title'    => ['text', 'Browser tab title', 'Join CWA Cameroon'],
                    'hero_title'    => ['text', 'Page heading', 'Join CWA Cameroon'],
                    'hero_subtitle' => ['textarea', 'Page subtitle', 'Catholic women are welcome in every diocese and parish. Tell us who you are and the National Secretariat will follow up.'],
                ],
            ],
            'gallery' => [
                'label' => 'Gallery',
                'url' => '/gallery',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <span class="text-brand-gold">Gallery</span>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Events, projects, and moments from CWA Cameroon'],
                ],
            ],
            'shareholders' => [
                'label' => 'Shareholders',
                'url' => '/shareholders',
                'fields' => [
                    'page_title'       => ['text', 'Browser tab title', 'Shareholders Agreement'],
                    'hero_title'       => ['text', 'Hero title', 'Shareholder Agreement'],
                    'hero_subtitle'    => ['textarea', 'Hero subtitle', 'Please read the following terms carefully before proceeding.'],
                    'terms_heading'    => ['text', 'Terms heading', 'Terms & Conditions of Investment'],
                    'terms_intro'      => ['textarea', 'Terms intro', 'This document serves as a binding understanding between Beyond Enterprise (the "Company") and you (the "Investor"). By clicking "I Agree" below, you acknowledge that you have read, understood, and accepted these terms.'],
                    'agreement_html'   => ['html', 'Custom agreement body (replaces the default numbered sections when filled)', ''],
                    'risk_heading'     => ['text', 'Risk heading', 'Risk Disclosure'],
                    'risk_text'        => ['textarea', 'Risk text', 'Investing in startups and growing companies involves risk, including potential loss of capital. Past performance does not guarantee future results.'],
                    'accept_prompt'    => ['text', 'Accept prompt', 'Do you accept the terms outlined in the Shareholders Agreement?'],
                    'disagree_button'  => ['text', 'Disagree button', 'I Disagree'],
                    'agree_button'     => ['text', 'Agree button', 'I Agree'],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'url' => '/services',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <span class="text-brand-gold">Services</span>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Comprehensive technology solutions tailored to your needs'],
                    'heading'       => ['text', 'Section heading', 'Explore Our Expertise'],
                    'subheading'    => ['textarea', 'Section subheading', "From IT infrastructure to cutting-edge AI solutions, we've got you covered."],
                ],
            ],
            'projects' => [
                'label' => 'Projects',
                'url' => '/projects',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <span class="text-brand-gold">Projects</span>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'See our engineering precision in action'],
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'url' => '/contact',
                'fields' => [
                    'heading'       => ['text', 'Page heading', 'Get in Touch'],
                    'intro'         => ['textarea', 'Intro text', 'Have a question or want to walk with us? Reach out to CWA Cameroon.'],
                    'office_name'   => ['text', 'Office name', 'CWA Cameroon'],
                    'office_line1'  => ['text', 'Office address line 1', 'Catholic Women\'s Association Cameroon'],
                    'office_line2'  => ['text', 'Office address line 2', 'Cameroon'],
                    'person_name'   => ['text', 'Contact person name', 'National Secretariat'],
                    'person_role'   => ['text', 'Contact person role', 'Catholic Women\'s Association Cameroon'],
                    'phone'         => ['text', 'Phone', ''],
                    'email'         => ['text', 'Email', 'info@cwacam.org'],
                    'website'       => ['text', 'Website', 'www.cwacam.org'],
                    'hours_weekday' => ['text', 'Business hours (Mon-Fri)', '9:00 AM - 6:00 PM'],
                    'hours_weekend' => ['text', 'Business hours (Sat-Sun)', 'Closed'],
                ],
            ],
        ];
    }

    public static function pageSchema($page)
    {
        $schema = self::schema();

        return $schema[$page] ?? null;
    }

    /** Keys for editable content pages (Home, About, …). */
    public static function contentTabItems()
    {
        $items = [];
        foreach (self::schema() as $key => $page) {
            $items[$key] = $page['label'];
        }

        return $items;
    }

    /** Saved order of content page tabs in Site Content admin. */
    public static function contentTabOrder()
    {
        return SiteMenu::ordered('content_tabs_order', self::contentTabItems());
    }

    /** Page schema keyed by page, sorted for the admin tab bar. */
    public static function orderedSchema()
    {
        $schema = self::schema();
        $ordered = [];
        foreach (self::contentTabOrder() as $key) {
            if (isset($schema[$key])) {
                $ordered[$key] = $schema[$key];
            }
        }
        foreach ($schema as $key => $page) {
            if (! isset($ordered[$key])) {
                $ordered[$key] = $page;
            }
        }

        return $ordered;
    }
}
