<?php

namespace App\Http\Controllers;

use App\GalleryItem;
use App\SiteSetting;
use App\Support\GalleryEmbed;
use App\Support\SiteContent;
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

        $data = [
            'tab'          => $tab,
            'landing'      => SiteMenu::landingLabels(),
            'side'         => SiteMenu::sideItems(),
            'settings'     => SiteMenu::settingsItems(),
            'people'       => SiteMenu::peopleItems(),
            'landingHidden' => SiteMenu::landingHidden(),
            'sideHidden'   => SiteMenu::sideHidden(),
            'sideLocked'   => SiteMenu::sideLocked(),
            'landingOrder' => SiteMenu::landingOrder(),
            'sideOrder'    => SiteMenu::sideOrder(),
            'settingsOrder' => SiteMenu::settingsOrder(),
            'peopleOrder'  => SiteMenu::peopleOrder(),
            'schema'       => SiteContent::orderedSchema(),
            'pageSchema'   => SiteContent::pageSchema($tab),
            'galleryItems' => $tab === 'gallery' ? GalleryItem::ordered()->get() : collect(),
            'galleryTypes' => GalleryEmbed::types(),
        ];

        return view('site_content.index', $data);
    }

    public function gallery()
    {
        $this->authorizeAdmin();

        return view('gallery.admin', [
            'galleryItems' => GalleryItem::ordered()->get(),
            'galleryTypes' => GalleryEmbed::types(),
            'pageSchema' => SiteContent::pageSchema('gallery'),
        ]);
    }

    public function saveLandingMenu(Request $request)
    {
        $this->authorizeAdmin();
        $this->saveOrder($request, 'landing_menu_order', SiteMenu::landingItems());
        $this->saveHidden($request, 'landing_menu_hidden', SiteMenu::landingItems());

        $valid = array_keys(SiteMenu::landingItems());
        $incoming = (array) $request->input('labels', []);
        $clean = [];
        foreach ($valid as $key) {
            if (! array_key_exists($key, $incoming)) {
                continue;
            }
            $label = trim((string) $incoming[$key]);
            if ($label !== '') {
                $clean[$key] = mb_substr($label, 0, 60);
            }
        }
        SiteSetting::setValue('landing_menu_labels', json_encode($clean, JSON_UNESCAPED_UNICODE));

        return redirect('/admin/site-content?tab=landing-menu')->with('message', 'Landing menu order, names, and visibility saved.');
    }

    public function saveSideMenu(Request $request)
    {
        $this->authorizeAdmin();
        $this->saveOrder($request, 'side_menu_order', SiteMenu::sideItems());
        $this->saveHidden($request, 'side_menu_hidden', SiteMenu::sideItems(), SiteMenu::sideLocked());

        return redirect('/admin/site-content?tab=side-menu')->with('message', 'Side menu order and visibility saved.');
    }

    public function saveSettingsMenu(Request $request)
    {
        $this->authorizeAdmin();
        $this->saveOrder($request, 'settings_menu_order', SiteMenu::settingsItems());

        return redirect('/admin/site-content?tab=settings-menu')->with('message', 'Settings menu order saved.');
    }

    public function savePeopleMenu(Request $request)
    {
        $this->authorizeAdmin();
        $this->saveOrder($request, 'people_menu_order', SiteMenu::peopleItems());

        return redirect('/admin/site-content?tab=people-menu')->with('message', 'People menu order saved.');
    }

    public function saveContentTabs(Request $request)
    {
        $this->authorizeAdmin();
        $this->saveOrder($request, 'content_tabs_order', SiteContent::contentTabItems());

        return redirect('/admin/site-content?tab=content-tabs')->with('message', 'Content tab order saved.');
    }

    public function saveContent(Request $request, $page)
    {
        $this->authorizeAdmin();

        $pageSchema = SiteContent::pageSchema($page);
        if (! $pageSchema) {
            abort(404);
        }

        $content = (array) $request->input('content', []);

        foreach ($pageSchema['fields'] as $key => [$type, $label, $default]) {
            if ($type === 'image') {
                $file = $request->file('image.' . $key);
                if ($file && $file->isValid()) {
                    $dir = public_path('images/site');
                    if (! is_dir($dir)) {
                        @mkdir($dir, 0775, true);
                    }
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
                    $name = 'site_' . $page . '_' . $key . '_' . time() . '.' . $ext;
                    $file->move($dir, $name);
                    SiteContent::put($page . '.' . $key, 'images/site/' . $name);
                }
                // no file uploaded -> keep existing value
                continue;
            }

            if (array_key_exists($key, $content)) {
                SiteContent::put($page . '.' . $key, $content[$key]);
            }
        }

        return $this->galleryRedirect(ucfirst($page).' content saved.', '/admin/site-content?tab='.$page);
    }

    public function storeGalleryItem(Request $request)
    {
        $this->authorizeAdmin();

        $types = array_keys(GalleryEmbed::types());
        $request->validate([
            'type'        => 'required|in:' . implode(',', $types),
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'media_url'   => 'nullable|url|max:2048',
            'file'        => 'nullable|file|max:51200',
        ]);

        $type = $request->input('type');
        $filePath = null;

        if (in_array($type, GalleryEmbed::fileTypes(), true)) {
            $file = $request->file('file');
            if (! $file || ! $file->isValid()) {
                return back()->withErrors(['file' => 'Please upload a file for this media type.'])->withInput();
            }
            $filePath = $this->storeGalleryFile($file, $type);
        } elseif (in_array($type, GalleryEmbed::urlTypes(), true)) {
            if (! $request->filled('media_url')) {
                return back()->withErrors(['media_url' => 'Please paste a link for this media type.'])->withInput();
            }
        }

        $maxSort = (int) GalleryItem::max('sort_order');

        GalleryItem::create([
            'type'        => $type,
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'file_path'   => $filePath,
            'media_url'   => $request->input('media_url'),
            'sort_order'  => $maxSort + 1,
            'is_published'=> true,
        ]);

        return $this->galleryRedirect('Gallery item added.');
    }

    public function updateGalleryItem(Request $request, $id)
    {
        $this->authorizeAdmin();

        $item = GalleryItem::findOrFail($id);
        $types = array_keys(GalleryEmbed::types());

        $request->validate([
            'type'        => 'required|in:' . implode(',', $types),
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'media_url'   => 'nullable|url|max:2048',
            'file'        => 'nullable|file|max:51200',
        ]);

        $type = $request->input('type');
        $filePath = $item->file_path;

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $filePath = $this->storeGalleryFile($request->file('file'), $type);
        } elseif (in_array($type, GalleryEmbed::urlTypes(), true)) {
            $filePath = null;
        }

        $item->update([
            'type'        => $type,
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'file_path'   => $filePath,
            'media_url'   => $request->input('media_url'),
        ]);

        return $this->galleryRedirect('Gallery item updated.');
    }

    public function deleteGalleryItem($id)
    {
        $this->authorizeAdmin();
        GalleryItem::findOrFail($id)->delete();

        return $this->galleryRedirect('Gallery item removed.');
    }

    public function reorderGalleryItems(Request $request)
    {
        $this->authorizeAdmin();

        $order = array_map('intval', (array) $request->input('order', []));
        foreach ($order as $pos => $id) {
            GalleryItem::where('id', $id)->update(['sort_order' => $pos]);
        }

        return $this->galleryRedirect('Gallery order saved.');
    }

    private function galleryRedirect($message, $fallback = '/admin/gallery')
    {
        $return = (string) request()->input('return_to');
        $allowed = ['/admin/gallery', '/admin/site-content?tab=gallery'];
        if (! in_array($return, $allowed, true)) {
            $return = $fallback;
        }

        return redirect($return)->with('message', $message);
    }

    private function storeGalleryFile($file, $type)
    {
        $dir = public_path('images/gallery');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $name = 'gallery_' . $type . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($dir, $name);

        return 'images/gallery/' . $name;
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

    /** Persist which items are unchecked (hidden). Locked keys cannot be hidden. */
    private function saveHidden(Request $request, $settingKey, array $items, array $locked = [])
    {
        $enabled = (array) $request->input('enabled', []);
        $hidden = [];
        foreach (array_keys($items) as $key) {
            if (in_array($key, $locked, true)) {
                continue;
            }
            if (! in_array($key, $enabled, true)) {
                $hidden[] = $key;
            }
        }
        SiteSetting::setValue($settingKey, $hidden);
    }
}
