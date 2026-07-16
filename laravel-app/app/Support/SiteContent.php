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
        return self::get($key, $default);
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
        // Absolute URLs / root-relative paths are returned as-is.
        if (preg_match('#^(https?:)?//#', $val) || strpos($val, '/') === 0) {
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
                'url' => '/',
                'fields' => [
                    'hero_title'            => ['html', 'Hero title (HTML allowed)', 'Catholic Women\'s Association <span class="text-brand-gold">of Cameroon</span>'],
                    'hero_subtitle'         => ['textarea', 'Hero subtitle', '“To Serve and Not to Be Served” (Matthew 20:28) — Promoting the spiritual, social, and economic well-being of women and families through prayer, evangelization, service, and charity.'],
                    'hero_image'            => ['image', 'Hero background image', '/public/branding/cwa-hero.png'],
                    'cta_primary'           => ['text', 'Hero primary button text', 'About CWA'],
                    'services_heading'      => ['text', 'Pillars heading', 'Our Mission in Action'],
                    'services_subheading'   => ['text', 'Pillars subheading', 'Faith, service, and community for Catholic women across Cameroon'],
                    'why_heading'           => ['text', 'Why-us heading', 'Why the Catholic Women\'s Association?'],
                    'why_subheading'        => ['text', 'Why-us subheading', 'Serving God, the Church, and society since 1964'],
                    'industries_heading'    => ['text', 'Communities heading', 'Communities We Serve'],
                    'industries_subheading' => ['text', 'Communities subheading', 'Walking with women, families, parishes, and society across Cameroon'],
                    'testimonials_heading'  => ['text', 'Testimonials heading', 'Voices from Our Members'],
                    'testimonials_subheading' => ['text', 'Testimonials subheading', 'Women living the CWA motto through faith and service'],
                    'cta_heading'           => ['text', 'Bottom CTA heading', 'Join Us in Service'],
                    'cta_text'              => ['textarea', 'Bottom CTA text', 'Reach out to the Catholic Women\'s Association — together we serve God, the Church, and society in the spirit of the Blessed Virgin Mary.'],
                ],
            ],
            'about' => [
                'label' => 'About',
                'url' => '/about',
                'fields' => [
                    'hero_title'        => ['text', 'Hero title', 'Catholic Women\'s Association of Cameroon'],
                    'hero_subtitle'     => ['textarea', 'Hero subtitle', 'A lay association of Catholic women founded in Cameroon in 1964. Motto: “To Serve and Not to Be Served” (Matthew 20:28).'],
                    'mission_heading'   => ['text', 'Mission heading', 'Our Mission'],
                    'mission_text'      => ['textarea', 'Mission text', 'To promote the spiritual, social, and economic well-being of women and families through prayer, evangelization, service, charity, and Christian witness.'],
                    'about_image'       => ['image', 'Mission image', 'https://horizons-cdn.hostinger.com/81ef3422-3855-479e-bfe8-28a4ceb0df39/513a28b3-47b7-490b-b30a-f9398973361b-a4hCG.png'],
                    'leadership_heading' => ['text', 'Leadership heading', 'Our Leadership'],
                    'leadership_subtext' => ['text', 'Leadership subtext', 'Women of faith guiding CWA in service to God and neighbour'],
                    'values_heading'    => ['text', 'Core values heading', 'Our Core Values'],
                    'cta_heading'       => ['text', 'CTA heading', 'Walk with us in faith and service'],
                    'cta_text'          => ['text', 'CTA text', 'Contact the Catholic Women\'s Association — we are here to serve.'],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'url' => '/services',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <span class="text-brand-gold">Apostolate</span>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Spiritual growth, evangelization, charity, and community service'],
                    'heading'       => ['text', 'Section heading', 'How We Serve'],
                    'subheading'    => ['textarea', 'Section subheading', 'Translating Christian values into action through prayer, mercy, and social development.'],
                ],
            ],
            'projects' => [
                'label' => 'Projects',
                'url' => '/projects',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <span class="text-brand-gold">Works</span>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Charity, formation, and community service in action'],
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'url' => '/contact',
                'fields' => [
                    'heading'       => ['text', 'Page heading', 'Get in Touch'],
                    'intro'         => ['textarea', 'Intro text', 'Have a question, want to join a local group, or partner with us in service? Reach out to the Catholic Women\'s Association today.'],
                    'office_name'   => ['text', 'Office name', 'Catholic Women\'s Association — Head Office'],
                    'office_line1'  => ['text', 'Office address line 1', 'X559+X22 Finance Junction Nkwen'],
                    'office_line2'  => ['text', 'Office address line 2', 'Bamenda, Cameroon'],
                    'person_name'   => ['text', 'Contact person name', 'CWA Secretariat'],
                    'person_role'   => ['text', 'Contact person role', 'Catholic Women\'s Association'],
                    'phone'         => ['text', 'Phone', '+237 683 155 315'],
                    'email'         => ['text', 'Email', 'info@cwacam.org'],
                    'website'       => ['text', 'Website', 'www.cwacmr.org'],
                    'hours_weekday' => ['text', 'Hours (Tue–Sat)', '8:00 AM - 5:00 PM'],
                    'hours_weekend' => ['text', 'Hours (Sunday / Monday)', 'Closed'],
                ],
            ],
            'gallery' => [
                'label' => 'Gallery',
                'url' => '/gallery',
                'fields' => [
                    'hero_title'    => ['html', 'Hero title (HTML allowed)', 'Our <span class="text-brand-gold">Gallery</span>'],
                    'hero_subtitle' => ['text', 'Hero subtitle', 'Moments of prayer, fellowship, and service from the Catholic Women\'s Association'],
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
