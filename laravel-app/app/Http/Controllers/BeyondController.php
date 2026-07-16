<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class BeyondController extends Controller
{
    public function home()
    {
        $pubService = app(\App\Services\EventPublicationService::class);
        $homeEvents = $pubService->publishedQuery()
            ->orderBy('event_start_at')
            ->limit(6)
            ->get()
            ->map(function ($event) use ($pubService) {
                $pub = $event->publication;

                return [
                    'slug' => $event->slug,
                    'title' => $pub->public_title ?: $event->name,
                    'summary' => $pub->public_summary,
                    'flyer' => $pubService->publicFlyerUrl($event, $pub),
                    'start' => $event->event_start_at,
                    'venue' => $pub->public_venue ?: $event->venue,
                    'status' => $pubService->computePublicStatus($event, $pub),
                ];
            });

        return view('beyond.home', ['homeEvents' => $homeEvents]);
    }

    public function about()
    {
        return view('beyond.about');
    }

    public function services()
    {
        return view('beyond.services', [
            'services' => $this->servicesList(),
        ]);
    }

    public function projects()
    {
        return view('beyond.projects', [
            'projects' => [
                [
                    'url' => 'https://www.tiktok.com/@tefurolandmbole/video/7495818139272301829',
                    'title' => 'Community Service Highlight',
                ],
                [
                    'url' => 'https://www.tiktok.com/@tefurolandmbole/video/7493245944540974341',
                    'title' => 'Fellowship & Formation',
                ],
                [
                    'url' => 'https://www.tiktok.com/@tefurolandmbole/video/7492891748327361797',
                    'title' => 'Charity in Action',
                ],
            ],
        ]);
    }

    public function gallery()
    {
        return view('beyond.gallery', [
            'items' => \App\GalleryItem::published()->ordered()->get(),
        ]);
    }

    public function contact()
    {
        return redirect(url('/about') . '#contact', 301);
    }

    public function events()
    {
        return view('beyond.events', ['events' => []]);
    }

    private function servicesList()
    {
        return [
            ['emoji' => '🙏', 'title' => 'Spiritual Growth & Renewal', 'description' => 'Promoting the spiritual growth and renewal of Catholic women'],
            ['emoji' => '📖', 'title' => 'God\'s Word & Faith', 'description' => 'Deepening knowledge of God\'s Word and strengthening Christian faith'],
            ['emoji' => '✝️', 'title' => 'Evangelization', 'description' => 'Translating Christian values into action through evangelization and witness'],
            ['emoji' => '❤️', 'title' => 'Charity & Works of Mercy', 'description' => 'Serving neighbours through charity, mercy, and social development'],
            ['emoji' => '👨‍👩‍👧‍👦', 'title' => 'Family & Church Support', 'description' => 'Supporting families, the Church, and society through community service'],
            ['emoji' => '🌱', 'title' => 'Women\'s Well-being', 'description' => 'Uplifting the spiritual, social, and economic well-being of women and families'],
            ['emoji' => '🌹', 'title' => 'Marian Discipleship', 'description' => 'Modelling our lives after the Blessed Virgin Mary in holiness and service'],
        ];
    }
}
