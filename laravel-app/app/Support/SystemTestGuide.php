<?php

namespace App\Support;

class SystemTestGuide
{
    public static function sections()
    {
        return array_merge(self::fixedSections(), [self::menuSection()]);
    }

    public static function checkMap()
    {
        $map = [];
        foreach (self::sections() as $section) {
            foreach ($section['checks'] as $check) {
                $map[$check['id']] = $check + ['section' => $section['title']];
            }
        }

        return $map;
    }

    protected static function fixedSections()
    {
        return [
            [
                'id' => 'public',
                'title' => 'Public website',
                'intro' => 'Use a normal browser on cwacam.org. Do not make a real payment.',
                'checks' => [
                    ['id' => 'home-open', 'text' => 'The homepage opens. The logo, public menu, language switch, Donate and Login are visible.'],
                    ['id' => 'home-menu', 'text' => 'The public menu is in the same order as Help → Current menus. Hidden items are not shown.'],
                    ['id' => 'home-lang', 'text' => 'FR switches the page to French. EN switches it back to English.'],
                    ['id' => 'home-join', 'text' => 'Join CWA opens the membership page.'],
                    ['id' => 'home-donate', 'text' => 'Donate opens the donate page. Stop before paying.'],
                    ['id' => 'about', 'text' => 'About Us shows Vision, Mission and Motto, then leadership portraits. Email and phone are not on the public cards.'],
                    ['id' => 'branches', 'text' => 'Branches opens on Cameroon. Diaspora switches the list. Search filters cards and clearing it restores the list.'],
                    ['id' => 'membership-cm', 'text' => 'A Cameroon registration asks for Region, address, city and state, and a diocese can be searched.'],
                    ['id' => 'membership-diaspora', 'text' => 'A Diaspora registration does not ask for Cameroon Region. Country and branch can be searched.'],
                    ['id' => 'events-public', 'text' => 'Events opens. A published event can be opened, or the empty state is clear.'],
                    ['id' => 'gallery', 'text' => 'Gallery shows photos, or a clear empty state. It is not an error page.'],
                    ['id' => 'contact', 'text' => 'Contact shows the form and the organisation email and phone.'],
                    ['id' => 'resources', 'text' => 'Resources / documents opens.'],
                    ['id' => 'mobile', 'text' => 'On a phone, the menu opens, scrolls, and Join CWA and Donate can be tapped. The page does not slide sideways.'],
                ],
            ],
            [
                'id' => 'login',
                'title' => 'Sign in',
                'intro' => 'Use an account you are allowed to test.',
                'checks' => [
                    ['id' => 'login-ok', 'text' => 'A correct username and password signs in and opens the admin area.'],
                    ['id' => 'login-bad', 'text' => 'A wrong password is refused and does not open the admin area.'],
                    ['id' => 'logout', 'text' => 'Logout returns to a public page and the admin menu is gone.'],
                ],
            ],
            [
                'id' => 'content',
                'title' => 'Site content and leaders',
                'intro' => 'Change something small, confirm it on the public site, then put the original text back.',
                'checks' => [
                    ['id' => 'site-text', 'text' => 'A saved About or homepage sentence appears on the public page and can be restored.'],
                    ['id' => 'site-menu', 'text' => 'Dragging the Landing Menu or Side Menu and saving changes the live menu. Help stays last. Dashboard and Site Content stay visible.'],
                    ['id' => 'leader-publish', 'text' => 'A published leader appears on About Us with photo, name and title. Turning publishing off hides that person.'],
                ],
            ],
            [
                'id' => 'people',
                'title' => 'People, signatures and WhatsApp',
                'intro' => 'Use the admin user, or another user whose phone you can check.',
                'checks' => [
                    ['id' => 'user-list', 'text' => 'People → User List opens and shows the users.'],
                    ['id' => 'user-edit', 'text' => 'Update User opens. Signature, Comment and Approver each show once, as a small icon, when a file exists.'],
                    ['id' => 'wa-sign', 'text' => 'WhatsApp sign link sends a message. The message has the organisation name, a heading, a divider, and the link.'],
                    ['id' => 'wa-sign-page', 'text' => 'The person can open the link and save a signature. It then shows on Update User.'],
                    ['id' => 'wa-all', 'text' => 'Send for approver, Send for comment, and Send for all each produce the matching request.'],
                ],
            ],
            [
                'id' => 'membership-admin',
                'title' => 'Membership office',
                'intro' => 'Use the test registrations from the public section. Do not approve a real member by mistake.',
                'checks' => [
                    ['id' => 'member-queue', 'text' => 'Awaiting Approvals lists the test registrations.'],
                    ['id' => 'member-approve', 'text' => 'Approving a record moves it to Members.'],
                    ['id' => 'member-reject', 'text' => 'Rejecting a record, with a reason, moves it to Rejected.'],
                ],
            ],
            [
                'id' => 'letters',
                'title' => 'Letters and announcements',
                'intro' => 'Create a test item and delete it when you are finished, if the screen allows delete.',
                'checks' => [
                    ['id' => 'letter-create', 'text' => 'A letter can be created and opened again. The page is not a server error.'],
                    ['id' => 'announce-create', 'text' => 'An announcement can be composed. A test send, if you make one, arrives on WhatsApp with a heading.'],
                ],
            ],
            [
                'id' => 'tasks',
                'title' => 'Tasks',
                'intro' => 'Use a test task with a person you can ask.',
                'checks' => [
                    ['id' => 'task-create', 'text' => 'A task can be created and is listed.'],
                    ['id' => 'task-notice', 'text' => 'The assignee receives the task message, or can see the task after signing in.'],
                ],
            ],
            [
                'id' => 'settings',
                'title' => 'Settings',
                'intro' => 'Do not click Empty Database.',
                'checks' => [
                    ['id' => 'settings-open', 'text' => 'General Setting opens and does not show “This feature is disable for demo”.'],
                    ['id' => 'settings-save', 'text' => 'A harmless change, such as a note you immediately restore, saves successfully.'],
                    ['id' => 'footer-image', 'text' => 'The email footer image saves and the preview stays inside its box.'],
                    ['id' => 'empty-db', 'text' => 'Empty Database is visible only as a warning. You did not click it, and you confirm it is still there unused.'],
                ],
            ],
        ];
    }

    protected static function menuSection()
    {
        $checks = [];
        $hidden = [];
        try {
            $hidden = SiteMenu::sideHidden();
            foreach (SiteMenu::sideOrder() as $key) {
                if (in_array($key, $hidden, true)) {
                    continue;
                }
                $label = SiteMenu::sideItems()[$key] ?? $key;
                $checks[] = [
                    'id' => 'menu-'.$key,
                    'text' => 'Open '.$label.'. The screen loads. It is not a blank page or a server error.',
                ];
            }
        } catch (\Throwable $e) {
            $checks[] = [
                'id' => 'menu-dashboard',
                'text' => 'Open Dashboard. The screen loads.',
            ];
        }
        $checks[] = ['id' => 'menu-help', 'text' => 'Help is the last item in the side menu and this guide’s link is on that page.'];

        return [
            'id' => 'menus',
            'title' => 'Every admin menu',
            'intro' => 'Open each item in the blue menu. Mark Does not work only when the page errors or is empty when it should have content.',
            'checks' => $checks,
        ];
    }
}
