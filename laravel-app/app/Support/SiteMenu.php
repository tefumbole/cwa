<?php

namespace App\Support;

use App\SiteSetting;

/**
 * Canonical definitions and saved ordering for the public landing menu and the
 * admin side menu. Used by the Site Content admin screen and both layouts.
 */
class SiteMenu
{
    /** Public site header items: key => label (default order). */
    public static function landingItems()
    {
        return [
            'home'         => 'Home',
            'trainings'    => 'Training',
            'events'       => 'Events',
            'register'     => 'Register Now',
            'apply'        => 'Apply Now',
            'about'        => 'About Us',
            'shareholders' => 'Shareholders',
            'contact'      => 'Contact Us',
        ];
    }

    /** Admin sidebar top-level items: key => label (default order). Keys match
     *  the sidebar collapse targets (#product, #purchase, ...). */
    public static function sideItems()
    {
        return [
            'dashboard'    => 'Dashboard',
            'site-content' => 'Site Content',
            'product'      => 'Product',
            'purchase'     => 'Purchase',
            'sale'         => 'Sale',
            'booking'      => 'Rental Module',
            'shop'         => 'Shops',
            'order'        => 'Online Order',
            'payments'     => 'Payments',
            'letter'       => 'Letters',
            'expense'      => 'Expense',
            'quotation'    => 'Quotation',
            'assets'       => 'Fixed Assets',
            'transfer'     => 'Transfer',
            'return'       => 'Return',
            'account'      => 'Accounting',
            'hrm'          => 'HRM',
            'people'       => 'People',
            'report'       => 'Reports',
            'setting'      => 'Settings',
        ];
    }

    /**
     * Merge the saved order with the canonical items: saved keys first (only if
     * still valid), then any new/unsaved keys appended in their default order.
     */
    public static function ordered($settingKey, array $items)
    {
        $saved = SiteSetting::getValue($settingKey, []);
        if (! is_array($saved)) {
            $saved = [];
        }

        $ordered = [];
        foreach ($saved as $k) {
            if (isset($items[$k]) && ! in_array($k, $ordered, true)) {
                $ordered[] = $k;
            }
        }
        foreach (array_keys($items) as $k) {
            if (! in_array($k, $ordered, true)) {
                $ordered[] = $k;
            }
        }

        return $ordered;
    }

    public static function landingOrder()
    {
        return self::ordered('landing_menu_order', self::landingItems());
    }

    public static function sideOrder()
    {
        return self::ordered('side_menu_order', self::sideItems());
    }
}
