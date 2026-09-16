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
            'about'        => 'About Us',
            'membership'   => 'Membership',
            'events'       => 'Calendar',
            'gallery'      => 'Gallery',
            'resources'    => 'Resources',
            'trainings'    => 'Training',
            'rentals'      => 'Rentals',
            'register'     => 'Register Now',
            'apply'        => 'Apply Now',
            'permissions'  => 'Permissions',
            'shareholders' => 'Shareholders',
            // Contact is merged into About Us (#contact). Join CWA is a header button.
        ];
    }

    /** Hidden from the public header until an admin unhides them in Site Content. */
    public static function landingDefaultHidden()
    {
        return ['resources', 'trainings', 'rentals', 'register', 'apply', 'permissions', 'shareholders'];
    }

    /** Admin sidebar top-level items: key => label (default order). Keys match
     *  the sidebar collapse targets (#product, #purchase, ...). */
    public static function sideItems()
    {
        return [
            'dashboard'    => 'Dashboard',
            'site-content' => 'Site Content',
            'leaders'      => 'About Us Leaders',
            'product'      => 'Product',
            'purchase'     => 'Purchase',
            'sale'         => 'Sale',
            'booking'      => 'Rental Module',
            'events'       => 'Events',
            'invitations'  => 'Digital Invitations',
            'tasks'        => 'Task Manager',
            'jobs'         => 'Job Board',
            'membership'   => 'Membership',
            'contracts'    => 'Contracts',
            'permissions'  => 'Permissions',
            'announcements'=> 'Announcements',
            'courses'      => 'Courses',
            'timesheets'   => 'TimeSheets (Employee)',
            'timesheet-admin' => 'TimeSheet Admin',
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

    /** Keys hidden from the public header. New items default to visible. */
    public static function hiddenKeys($settingKey)
    {
        $raw = SiteSetting::getValue($settingKey, []);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($raw)) {
            $raw = [];
        }

        $out = [];
        foreach ($raw as $k) {
            if (is_string($k) && $k !== '' && ! in_array($k, $out, true)) {
                $out[] = $k;
            }
        }

        return $out;
    }

    public static function landingHidden()
    {
        try {
            $row = SiteSetting::find('landing_menu_hidden');
        } catch (\Throwable $e) {
            return self::landingDefaultHidden();
        }
        if (! $row) {
            return self::landingDefaultHidden();
        }

        return self::hiddenKeys('landing_menu_hidden');
    }

    /** URL + label map for the public header (used by layout and coming-soon). */
    public static function landingNavDefs()
    {
        return [
            'home'         => ['label' => self::landingLabel('home'), 'url' => url('/')],
            'about'        => ['label' => self::landingLabel('about'), 'url' => url('/about')],
            'membership'   => ['label' => self::landingLabel('membership'), 'url' => url('/membership'), 'match' => ['/membership', '/join']],
            'events'       => ['label' => self::landingLabel('events'), 'url' => url('/calendar'), 'match' => ['/calendar', '/events']],
            'gallery'      => ['label' => self::landingLabel('gallery'), 'url' => url('/gallery')],
            'resources'    => ['label' => self::landingLabel('resources'), 'url' => url('/documents')],
            'trainings'    => ['label' => self::landingLabel('trainings'), 'url' => url('/trainings')],
            'rentals'      => ['label' => self::landingLabel('rentals'), 'url' => url('/rentals')],
            'register'     => ['label' => self::landingLabel('register'), 'url' => url('/register-now')],
            'apply'        => ['label' => self::landingLabel('apply'), 'url' => url('/apply-now'), 'special' => true],
            'permissions'  => ['label' => self::landingLabel('permissions'), 'url' => url('/permissions')],
            'shareholders' => ['label' => self::landingLabel('shareholders'), 'url' => url('/shareholders')],
        ];
    }

    public static function landingNavLinks()
    {
        $defs = self::landingNavDefs();
        $links = [];
        foreach (self::landingVisibleOrder() as $key) {
            if ($key === 'contact') {
                continue;
            }
            if (isset($defs[$key])) {
                $links[] = $defs[$key];
            }
        }

        return $links;
    }

    public static function navLinkIsActive(array $link, $currentUrl)
    {
        $current = rtrim($currentUrl, '/');
        if ($current === rtrim($link['url'], '/')) {
            return true;
        }
        if (! empty($link['match'])) {
            foreach ((array) $link['match'] as $needle) {
                if ($needle !== '' && strpos($currentUrl, $needle) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function landingVisibleOrder()
    {
        $hidden = self::landingHidden();
        $out = [];
        foreach (self::landingOrder() as $key) {
            if (! in_array($key, $hidden, true)) {
                $out[] = $key;
            }
        }

        return $out;
    }

    /**
     * Sidebar items that must stay visible so an admin cannot lock themselves
     * out of Site Content.
     */
    public static function sideLocked()
    {
        return ['dashboard', 'site-content'];
    }

    public static function sideHidden()
    {
        $hidden = self::hiddenKeys('side_menu_hidden');
        $locked = self::sideLocked();

        return array_values(array_filter($hidden, function ($k) use ($locked) {
            return ! in_array($k, $locked, true);
        }));
    }

    /**
     * Saved custom labels for public header tabs. Falls back to landingItems().
     * Stored as a JSON object string so keys are preserved (setValue() strips
     * associative keys when given a PHP array).
     */
    public static function landingLabels()
    {
        $defaults = self::landingItems();
        $raw = SiteSetting::getValue('landing_menu_labels', []);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($raw)) {
            $raw = [];
        }

        $out = [];
        foreach ($defaults as $key => $label) {
            $custom = isset($raw[$key]) ? trim((string) $raw[$key]) : '';
            $out[$key] = $custom !== '' ? $custom : $label;
        }

        return $out;
    }

    public static function landingLabel($key)
    {
        $translated = trans('cwa.nav.'.$key);
        if (is_string($translated) && $translated !== 'cwa.nav.'.$key) {
            return $translated;
        }

        $labels = self::landingLabels();
        $defaults = self::landingItems();

        return $labels[$key] ?? ($defaults[$key] ?? $key);
    }

    public static function sideOrder()
    {
        return self::ordered('side_menu_order', self::sideItems());
    }

    /** Settings submenu items inside #setting (key => label). */
    public static function settingsItems()
    {
        return [
            'role'               => 'Role Permission',
            'notification'       => 'Send Notification',
            'warehouse'          => 'Warehouse',
            'customer-group'     => 'Customer Group',
            'brand'              => 'Brand',
            'unit'               => 'Unit',
            'currency'           => 'Currency',
            'tax'                => 'Tax',
            'user'               => 'User Profile',
            'my-transactions'    => 'My Transactions',
            'backup-database'    => 'Backup Database',
            'empty-database'     => 'Empty Database',
            'general-setting'    => 'General Setting',
            'activity-logs'      => 'Activity Logs',
            'env-setting'        => '.env Settings',
            'mail-setting'       => 'Mail Setting',
            'reward-point-setting' => 'Reward Point Setting',
            'pos-setting'        => 'POS Settings',
        ];
    }

    public static function settingsOrder()
    {
        return self::ordered('settings_menu_order', self::settingsItems());
    }

    /** People submenu items inside #people (key => label). */
    public static function peopleItems()
    {
        return [
            'user-list'       => 'User List',
            'interns'         => 'Interns',
            'add-user'        => 'Add User',
            'customer-list'   => 'Customer List',
            'add-customer'    => 'Add Customer',
            'people-transfer' => 'Export / Import People',
            'biller-list'     => 'Biller List',
            'add-biller'      => 'Add Biller',
            'supplier-list'   => 'Supplier List',
            'add-supplier'    => 'Add Supplier',
        ];
    }

    public static function peopleOrder()
    {
        return self::ordered('people_menu_order', self::peopleItems());
    }

    /** Map people submenu <li id="..."> to stable reorder keys. */
    public static function peopleLiKeyMap()
    {
        return [
            'user-list-menu'       => 'user-list',
            'user-applicants-menu' => 'interns',
            'user-create-menu'     => 'add-user',
            'customer-list-menu'   => 'customer-list',
            'customer-create-menu' => 'add-customer',
            'people-transfer-menu' => 'people-transfer',
            'biller-list-menu'     => 'biller-list',
            'biller-create-menu'   => 'add-biller',
            'supplier-list-menu'   => 'supplier-list',
            'supplier-create-menu' => 'add-supplier',
        ];
    }

    /** Map settings submenu <li id="..."> to stable reorder keys. */
    public static function settingsLiKeyMap()
    {
        return [
            'role-menu'               => 'role',
            'notification-menu'         => 'notification',
            'warehouse-menu'          => 'warehouse',
            'customer-group-menu'     => 'customer-group',
            'brand-menu'              => 'brand',
            'unit-menu'               => 'unit',
            'currency-menu'           => 'currency',
            'tax-menu'                => 'tax',
            'user-menu'               => 'user',
            'my-transactions-menu'    => 'my-transactions',
            'backup-database-menu'    => 'backup-database',
            'empty-database-menu'     => 'empty-database',
            'general-setting-menu'    => 'general-setting',
            'activity-logs-menu'      => 'activity-logs',
            'env-setting-menu'        => 'env-setting',
            'mail-setting-menu'       => 'mail-setting',
            'reward-point-setting-menu' => 'reward-point-setting',
            'pos-setting-menu'        => 'pos-setting',
        ];
    }
}
