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
                    ['id' => 'home-open', 'text' => 'Open the website: cwacam.org. The first screen is the homepage. You should see the CWACAM logo, a row of menu links, EN / FR, Donate, and Login.', 'steps' => [
                        'Leave this test page open. Open a new browser tab.',
                        'Go to https://cwacam.org.',
                        'Look at the top of the page. Confirm the logo, the menu links, EN / FR, Donate, and Login.',
                    ]],
                    ['id' => 'home-lang', 'text' => 'Switch the homepage language.', 'steps' => [
                        'On the homepage, click FR.',
                        'The words on the page should change to French.',
                        'Click EN. The page should return to English.',
                    ]],
                    ['id' => 'home-join', 'text' => 'Open Join CWA.', 'steps' => [
                        'On the homepage, click Join CWA. On a phone it is inside the menu.',
                        'The membership page should open.',
                    ]],
                    ['id' => 'home-donate', 'text' => 'Open Donate. Do not pay.', 'steps' => [
                        'Click Donate.',
                        'The donate page should open and show an amount.',
                        'Stop there. Do not send any money.',
                    ]],
                    ['id' => 'about', 'text' => 'Open About Us and look at the leaders.', 'steps' => [
                        'Click About Us in the menu.',
                        'Read Vision, Mission, and Motto.',
                        'Under the motto, each leader should show a round photo, a name, and a title. Email and phone should not be on the card.',
                    ]],
                    ['id' => 'branches', 'text' => 'Open Branches and search.', 'steps' => [
                        'Click Branches.',
                        'Cameroon should be selected first, with province headings and diocese cards.',
                        'Type Buea in the search. Only matching cards should stay.',
                        'Clear the search, then open Diaspora. Search Belgium. Clearing the search should bring the full list back.',
                    ]],
                    ['id' => 'membership-cm', 'text' => 'Start a Cameroon membership. Use a test name.', 'steps' => [
                        'Open Membership and click Register.',
                        'Choose Cameroon.',
                        'Click Diocese and type. Real diocese names should appear.',
                        'Region, address, city, and state should be required. You can stop before the final submit if you are only checking the form.',
                    ]],
                    ['id' => 'membership-diaspora', 'text' => 'Start a Diaspora membership. Use a test name.', 'steps' => [
                        'Start Register again and choose Diaspora.',
                        'The Cameroon Region field should not be shown.',
                        'Click Country and type. Diaspora countries should appear.',
                        'Click Diocese and type. Branch names for that country should appear.',
                    ]],
                    ['id' => 'events-public', 'text' => 'Open Events.', 'steps' => [
                        'Click Events in the menu.',
                        'A list of events should show, or a clear message that there are none.',
                        'If an event is listed, open it. The page should not be an error.',
                    ]],
                    ['id' => 'gallery', 'text' => 'Open Gallery.', 'steps' => [
                        'Click Gallery.',
                        'Photos should show, or a clear empty message. It should not be an error page.',
                    ]],
                    ['id' => 'contact', 'text' => 'Open Contact.', 'steps' => [
                        'Click Contact, or open https://cwacam.org/contact.',
                        'You should see a form, plus the organisation email and phone.',
                    ]],
                    ['id' => 'resources', 'text' => 'Open Resources.', 'steps' => [
                        'Open https://cwacam.org/documents.',
                        'The documents page should load.',
                    ]],
                    ['id' => 'mobile', 'text' => 'Repeat the homepage on a phone.', 'steps' => [
                        'Open cwacam.org on a phone, or make the browser window narrow.',
                        'Tap the menu button, the three lines at the top.',
                        'The links, Join CWA, and Donate should be easy to tap. The page should not slide sideways.',
                    ]],
                ],
            ],
            [
                'id' => 'login',
                'title' => 'Sign in',
                'intro' => 'Use an account you are allowed to test.',
                'checks' => [
                    ['id' => 'login-ok', 'text' => 'Sign in with the username and password you were given.', 'steps' => [
                        'On the homepage, click Login.',
                        'Type the username and password you were given. If you were not given a login, mark this Not tested.',
                        'You should enter the office. A blue menu appears on the left.',
                    ]],
                    ['id' => 'login-bad', 'text' => 'Try a wrong password.', 'steps' => [
                        'Open Login again, or log out first.',
                        'Type the right username and a wrong password.',
                        'The site should refuse it. You should not enter the office.',
                    ]],
                    ['id' => 'logout', 'text' => 'Sign out.', 'steps' => [
                        'Sign in again with the correct password.',
                        'Click Logout.',
                        'You should return to a public page, and the blue office menu should be gone.',
                    ]],
                ],
            ],
            [
                'id' => 'content',
                'title' => 'Site content and leaders',
                'intro' => 'Change something small, confirm it on the public site, then put the original text back.',
                'checks' => [
                    ['id' => 'site-text', 'text' => 'Change one public sentence, then put it back.', 'steps' => [
                        'Sign in. In the blue menu, click Site Content.',
                        'Change one word in an About or homepage sentence and save.',
                        'Open the public page in another tab. The new word should be there.',
                        'Change the word back and save again.',
                    ]],
                    ['id' => 'site-menu', 'text' => 'Move one menu item, then put it back.', 'steps' => [
                        'In Site Content, open Side Menu.',
                        'Drag one item, save, and look at the blue menu. It should follow the new order. Help stays last.',
                        'Drag it back and save.',
                    ]],
                    ['id' => 'leader-publish', 'text' => 'Show and hide a leader on About Us.', 'steps' => [
                        'In the blue menu, click About Us Leaders.',
                        'Open a published leader. On the public About page, that person should show a photo, name, and title.',
                        'Turn publishing off and save. Refresh About Us. That person should disappear. Turn publishing on again.',
                    ]],
                ],
            ],
            [
                'id' => 'people',
                'title' => 'People, signatures and WhatsApp',
                'intro' => 'Use the admin user, or another user whose phone you can check.',
                'checks' => [
                    ['id' => 'user-list', 'text' => 'Open the user list.', 'steps' => [
                        'In the blue menu, click People, then User List.',
                        'The list of users should appear.',
                    ]],
                    ['id' => 'user-add', 'text' => 'Open Add User.', 'steps' => [
                        'In People, click Add User.',
                        'The new-user form should open, with name, email or username, and phone.',
                        'Stop before saving, unless the name starts with TEST.',
                    ]],
                    ['id' => 'customer-list', 'text' => 'Open the customer list.', 'steps' => [
                        'In People, click Customer List.',
                        'The list should open, or show a clear empty message.',
                    ]],
                    ['id' => 'supplier-list', 'text' => 'Open the supplier list.', 'steps' => [
                        'In People, click Supplier List.',
                        'The list should open, or show a clear empty message.',
                    ]],
                    ['id' => 'user-edit', 'text' => 'Open one user and look at the signatures.', 'steps' => [
                        'Click Edit on a user, or click the row.',
                        'Find Signature, Comment, and Approver.',
                        'If a signature exists, it should show once, as a small picture, not twice.',
                    ]],
                    ['id' => 'wa-sign', 'text' => 'Send a WhatsApp sign link.', 'steps' => [
                        'On Update User, click WhatsApp sign link next to Signature.',
                        'Confirm the send.',
                        'The phone should receive a WhatsApp message with a heading, a line, and a link.',
                    ]],
                    ['id' => 'wa-sign-page', 'text' => 'Sign from the WhatsApp link.', 'steps' => [
                        'Open the link in the WhatsApp message.',
                        'Draw a signature and save.',
                        'Go back to Update User and refresh. The new signature should show as a small picture.',
                    ]],
                    ['id' => 'wa-all', 'text' => 'Send the other sign requests.', 'steps' => [
                        'Click WhatsApp sign link for Comment, then for Approver.',
                        'From the user list menu, try Send for all.',
                        'Each one should send a WhatsApp message for that choice.',
                    ]],
                ],
            ],
            [
                'id' => 'membership-admin',
                'title' => 'Membership office',
                'intro' => 'Use the test registrations from the public section. Do not approve a real member by mistake.',
                'checks' => [
                    ['id' => 'member-queue', 'text' => 'Find a test membership waiting for approval.', 'steps' => [
                        'In the blue menu, click Membership, then Awaiting Approvals.',
                        'A test registration from earlier should be in the list. If you did not submit one, mark this Not tested.',
                    ]],
                    ['id' => 'member-approve', 'text' => 'Approve one test record.', 'steps' => [
                        'Open a test record, not a real member.',
                        'Approve it.',
                        'It should leave Awaiting Approvals and appear under Members.',
                    ]],
                    ['id' => 'member-reject', 'text' => 'Reject one test record.', 'steps' => [
                        'Open another test record.',
                        'Reject it and type a short reason.',
                        'It should appear under Rejected.',
                    ]],
                ],
            ],
            [
                'id' => 'letters',
                'title' => 'Letters and announcements',
                'intro' => 'Create a test item and delete it when you are finished, if the screen allows delete.',
                'checks' => [
                    ['id' => 'letter-create', 'text' => 'Create a test letter.', 'steps' => [
                        'In the blue menu, click Letters.',
                        'Create a short test letter and save it.',
                        'Open it again. The page should not be a server error. Delete the test letter if the screen allows it.',
                    ]],
                    ['id' => 'announce-create', 'text' => 'Compose a test announcement.', 'steps' => [
                        'In the blue menu, click Announcements.',
                        'Write a short test message.',
                        'If you send it, it should arrive on WhatsApp with a heading. Do not send it to a large list.',
                    ]],
                ],
            ],
            [
                'id' => 'tasks',
                'title' => 'Tasks',
                'intro' => 'Use a test task with a person you can ask.',
                'checks' => [
                    ['id' => 'task-create', 'text' => 'Create a test task.', 'steps' => [
                        'In the blue menu, click Task Manager.',
                        'Create a task with a short title and save it.',
                        'The task should appear in the list.',
                    ]],
                    ['id' => 'task-notice', 'text' => 'Check that the assigned person can see the task.', 'steps' => [
                        'Assign the test task to someone whose phone you can check, or to yourself.',
                        'That person should get a WhatsApp message, or see the task after signing in.',
                    ]],
                ],
            ],
            [
                'id' => 'settings',
                'title' => 'Settings',
                'intro' => 'Do not click Empty Database.',
                'checks' => [
                    ['id' => 'settings-open', 'text' => 'Open General Setting.', 'steps' => [
                        'In the blue menu, click Settings, then General Setting.',
                        'The page should open.',
                        'It should not show the message “This feature is disable for demo”.',
                    ]],
                    ['id' => 'settings-save', 'text' => 'Save a small change, then undo it.', 'steps' => [
                        'Change one harmless field.',
                        'Click save. The page should say it saved.',
                        'Put the old value back and save again.',
                    ]],
                    ['id' => 'footer-image', 'text' => 'Check the email footer picture.', 'steps' => [
                        'On General Setting, find the email footer image.',
                        'If you upload a new one, the preview should stay inside its box and not cover the next field.',
                    ]],
                    ['id' => 'role-permission', 'text' => 'Open Role Permission.', 'steps' => [
                        'In Settings, click Role Permission.',
                        'The roles should be listed.',
                        'Open one role. The permission checkboxes should show. Do not remove an administrator permission.',
                    ]],
                    ['id' => 'activity-logs', 'text' => 'Open Activity Logs.', 'steps' => [
                        'In Settings, click Activity Logs.',
                        'Recent office actions should be listed, or the page should say there are none.',
                    ]],
                    ['id' => 'empty-db', 'text' => 'Find Empty Database, and do not click it.', 'steps' => [
                        'In Settings, look for Empty Database.',
                        'Confirm the button is there.',
                        'Do not click it.',
                    ]],
                ],
            ],
        ];
    }

    protected static function menuSection()
    {
        $checks = [];
        $covered = ['site-content', 'leaders', 'people', 'membership', 'announcements', 'letter', 'tasks', 'setting'];
        try {
            $hidden = SiteMenu::sideHidden();
            foreach (SiteMenu::sideOrder() as $key) {
                if (in_array($key, $hidden, true) || in_array($key, $covered, true)) {
                    continue;
                }
                $label = SiteMenu::sideItems()[$key] ?? $key;
                $activity = self::adminActivity($key, $label);
                $checks[] = [
                    'id' => 'menu-'.$key,
                    'text' => $activity['text'],
                    'steps' => $activity['steps'],
                ];
            }
        } catch (\Throwable $e) {
            $checks[] = self::adminActivity('dashboard', 'Dashboard') + ['id' => 'menu-dashboard'];
        }
        foreach (['internship' => 'Internships', 'supervisor' => 'Supervisor'] as $key => $label) {
            $activity = self::adminActivity($key, $label);
            $checks[] = [
                'id' => 'menu-'.$key,
                'text' => $activity['text'],
                'steps' => $activity['steps'],
            ];
        }
        $checks[] = [
            'id' => 'menu-help',
            'text' => 'Open Help. It should be the last item in the blue menu.',
            'steps' => [
                'Scroll to the bottom of the blue menu.',
                'Help should be the last name.',
                'Click Help. This test link should be on that page.',
            ],
        ];

        return [
            'id' => 'menus',
            'title' => 'Admin panel activities',
            'intro' => 'Stay signed in. For each activity, use the blue menu on the left, do the steps, then mark the result. Use a name that starts with TEST, and do not delete real records.',
            'checks' => $checks,
        ];
    }

    protected static function adminActivity($key, $label)
    {
        $known = [
            'dashboard' => ['Read the Dashboard.', [
                'In the blue menu, click Dashboard.',
                'You should see summary numbers or charts for the office.',
                'The page should not be blank and should not be a server error.',
            ]],
            'product' => ['Work in Product.', [
                'In the blue menu, click Product, then Product List.',
                'The list of products should open.',
                'Click Add Product. Type a name that starts with TEST. You may stop before saving. If you save it, delete that test product afterwards.',
            ]],
            'purchase' => ['Work in Purchase.', [
                'In the blue menu, click Purchase, then Purchase List.',
                'The list should open.',
                'Click Add Purchase and confirm the form opens. Do not save a real purchase unless you name it TEST and can delete it.',
            ]],
            'sale' => ['Work in Sale.', [
                'In the blue menu, click Sale, then Sale List.',
                'The list should open.',
                'Open POS or Add Sale. The screen should load. Do not complete a real sale.',
            ]],
            'booking' => ['Work in the rental module.', [
                'In the blue menu, click Rental Module, then Booking List.',
                'The list should open.',
                'Click Booking Create. The form should open. Stop before saving a real booking.',
            ]],
            'events' => ['Create a test event in the office.', [
                'In the blue menu, click Events, then All Events.',
                'The event list or an empty list should show.',
                'Click Create Event. Give it a title that starts with TEST, save it, then open it again. Delete it if the screen allows delete.',
            ]],
            'invitations' => ['Work in Digital Invitations.', [
                'In the blue menu, click Digital Invitations, then All Invitations.',
                'The list should open.',
                'Click Create Invitation. The form should open. Do not send it to a real guest list.',
            ]],
            'jobs' => ['Work in the Job Board.', [
                'In the blue menu, click Job Board, then Job Postings.',
                'The list should open.',
                'Click Add Job. The form should open. Do not publish a real vacancy unless the title starts with TEST.',
            ]],
            'contracts' => ['Work in Contracts.', [
                'In the blue menu, click Contracts, then Contract List.',
                'The list should open.',
                'Click Create Contract. The form should open. Stop before sending it for signature.',
            ]],
            'permissions' => ['Work in Permissions.', [
                'In the blue menu, click Permissions, then Permissions Listings.',
                'The list should open.',
                'Open Awaiting if it is in the submenu. A list or a clear empty message should show.',
            ]],
            'courses' => ['Work in Courses.', [
                'In the blue menu, click Courses.',
                'The course list should open.',
                'Open one course, or start a new one named TEST and stop before publishing it.',
            ]],
            'timesheets' => ['Open your own timesheet.', [
                'In the blue menu, click TimeSheets (Employee).',
                'Your timesheet screen should open.',
                'If a clock-in or add-hours button is there, do not submit hours unless they are marked TEST.',
            ]],
            'timesheet-admin' => ['Open timesheet admin.', [
                'In the blue menu, click TimeSheet Admin.',
                'The admin timesheet list should open.',
                'You should be able to see staff rows, or a clear empty message.',
            ]],
            'shop' => ['Work in Shops.', [
                'In the blue menu, click Shops.',
                'The shop list should open.',
                'Open one shop, or start Add Shop and stop before saving.',
            ]],
            'order' => ['Work in Online Order.', [
                'In the blue menu, click Online Order.',
                'The order list should open.',
                'Open one order if any exist. Do not mark a real order as paid.',
            ]],
            'payments' => ['Work in Payments.', [
                'In the blue menu, click Payments.',
                'The payments screen should open.',
                'You should see a list or a clear empty message. Do not record a real payment.',
            ]],
            'expense' => ['Work in Expense.', [
                'In the blue menu, click Expense, then Expense List.',
                'The list should open.',
                'Click Add Expense. The form should open. If you save one, name it TEST and delete it afterwards.',
            ]],
            'quotation' => ['Work in Quotation.', [
                'In the blue menu, click Quotation, then Quotation List.',
                'The list should open.',
                'Click Add Quotation. The form should open. Stop before sending it to a real customer.',
            ]],
            'assets' => ['Work in Fixed Assets.', [
                'In the blue menu, click Fixed Assets.',
                'The asset list should open.',
                'Open one asset, or start a new one named TEST and stop before saving.',
            ]],
            'transfer' => ['Work in Transfer.', [
                'In the blue menu, click Transfer.',
                'The transfer list should open.',
                'Start a new transfer if the button is there. Stop before moving real stock.',
            ]],
            'return' => ['Work in Return.', [
                'In the blue menu, click Return.',
                'The return list should open.',
                'The page should not be a server error. Do not return a real sale.',
            ]],
            'account' => ['Work in Accounting.', [
                'In the blue menu, click Accounting.',
                'Open the first item in that submenu, such as the account list.',
                'The screen should show accounts or a clear empty message.',
            ]],
            'hrm' => ['Work in HRM.', [
                'In the blue menu, click HRM, then Employee.',
                'The employee list should open.',
                'Open Attendance or Payroll from the same menu. Each one should load. Do not change a real salary.',
            ]],
            'report' => ['Open one report.', [
                'In the blue menu, click Reports.',
                'Pick any report, such as a sale or summary report.',
                'The report should open or say there is no data. It should not be a server error.',
            ]],
            'internship' => ['Work in Internships.', [
                'In the blue menu, click Internships.',
                'Open the list inside that menu.',
                'The screen should show internships or a clear empty message.',
            ]],
            'supervisor' => ['Work in Supervisor.', [
                'In the blue menu, click Supervisor.',
                'Open the list inside that menu.',
                'The screen should show supervisors or a clear empty message.',
            ]],
        ];
        if (isset($known[$key])) {
            return ['text' => $known[$key][0], 'steps' => $known[$key][1]];
        }

        return [
            'text' => 'Work in '.$label.'.',
            'steps' => [
                'In the blue menu, click '.$label.'.',
                'Open the first list inside that menu.',
                'The screen should open. It should not be blank and it should not be a server error. Do not delete real records.',
            ],
        ];
    }
}
