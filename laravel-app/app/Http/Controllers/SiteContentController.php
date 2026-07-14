<?php

namespace App\Http\Controllers;

use App\SiteSetting;
use App\Support\SiteMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteContentController extends Controller
{
    /** Restrict Site Content management to Admin / Owner roles. */
    protected function authorizeAdmin()
    {
        if (! Auth::check() || ! in_array((int) Auth::user()->role_id, [1, 2], true)) {
            abort(403, 'You are not allowed to manage site content.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $tab = $request->get('tab', 'landing-menu');

        $landing = SiteMenu::landingItems();
        $side = SiteMenu::sideItems();
        $landingOrder = SiteMenu::landingOrder();
        $sideOrder = SiteMenu::sideOrder();

        return view('site_content.index', compact('tab', 'landing', 'side', 'landingOrder', 'sideOrder'));
    }

    public function saveLandingMenu(Request $request)
    {
        $this->authorizeAdmin();
        $this->saveOrder($request, 'landing_menu_order', SiteMenu::landingItems());

        return redirect('/admin/site-content?tab=landing-menu')->with('message', 'Landing menu order saved.');
    }

    public function saveSideMenu(Request $request)
    {
        $this->authorizeAdmin();
        $this->saveOrder($request, 'side_menu_order', SiteMenu::sideItems());

        return redirect('/admin/site-content?tab=side-menu')->with('message', 'Side menu order saved.');
    }

    private function saveOrder(Request $request, $settingKey, array $items)
    {
        $order = (array) $request->input('order', []);
        $valid = array_keys($items);
        $order = array_values(array_filter($order, function ($k) use ($valid) {
            return in_array($k, $valid, true);
        }));
        SiteSetting::setValue($settingKey, $order);
    }
}
