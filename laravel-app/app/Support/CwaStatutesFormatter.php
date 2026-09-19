<?php

namespace App\Support;

/**
 * Turn extracted statute/bylaw HTML into readable outline:
 * section headings (A. B.), numbered points, lettered sub-points.
 */
class CwaStatutesFormatter
{
    public static function statuteIcons()
    {
        return [
            'award', 'heart', 'users', 'layers', 'shield', 'user', 'home', 'clock',
            'calendar', 'dollar-sign', 'check-square', 'link', 'mail', 'alert-triangle',
            'type', 'edit-3', 'x-circle',
        ];
    }

    public static function bylawIcons()
    {
        return [
            'target', 'user-plus', 'briefcase', 'shield', 'map-pin', 'folder', 'users',
            'dollar-sign', 'user-check', 'check-square', 'check-circle', 'star', 'tag',
            'award', 'alert-triangle', 'file-text', 'coffee', 'mail', 'type',
            'edit-3', 'book-open',
        ];
    }

    public static function iconFor($kind, $index)
    {
        $list = $kind === 'bylaws' ? self::bylawIcons() : self::statuteIcons();
        $n = count($list);
        if ($n === 0) {
            return 'info';
        }

        return $list[$index % $n];
    }

    public static function heading($kind, $roman, $title)
    {
        $prefix = $kind === 'bylaws' ? 'Bylaw' : 'Article';
        $roman = trim((string) $roman);
        $title = trim((string) $title);
        if ($roman === '') {
            return $title;
        }
        if ($title === '' || strcasecmp($title, $roman) === 0) {
            return $prefix.' '.$roman;
        }

        return $prefix.' '.$roman.' — '.$title;
    }

    public static function bodyHtml($html)
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $paragraphs = [];
        if (preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $html, $m)) {
            foreach ($m[1] as $chunk) {
                $plain = self::plain($chunk);
                if ($plain !== '') {
                    $paragraphs[] = $plain;
                }
            }
        }
        if (! $paragraphs) {
            $plain = self::plain($html);
            if ($plain !== '') {
                $paragraphs[] = $plain;
            }
        }

        $structured = false;
        foreach ($paragraphs as $paragraph) {
            if (preg_match('/^(?:\d+\.\s|[A-Z][.\)]\s+[A-ZÀ-Ÿ])/u', $paragraph)) {
                $structured = true;
                break;
            }
        }
        if ($structured) {
            return self::formatBlock(implode(' ', $paragraphs));
        }

        $out = '';
        foreach ($paragraphs as $paragraph) {
            $out .= self::formatBlock($paragraph);
        }

        return $out;
    }

    protected static function plain($html)
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8');
        $text = str_replace(["\xc2\xa0", "\xE2\x80\xA2", '', '–', '—'], [' ', ' ', ' ', '-', '-'], $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    protected static function formatBlock($text)
    {
        $html = '';
        foreach (self::splitSections($text) as $section) {
            $html .= '<div class="cwa-block">';
            if ($section['title'] !== '') {
                $html .= '<p class="cwa-section">'.self::esc(self::prettyHeading($section['title'])).'</p>';
            }
            $html .= self::formatPoints($section['body']);
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Split "A. TITLE … B. TITLE …" or "A) TITLE …".
     */
    protected static function splitSections($text)
    {
        $parts = preg_split(
            '/(?=(?:^|\s)(?:[A-Z]\.\s+[A-ZÀ-Ÿ]|[A-Z]\)\s+[A-ZÀ-Ÿ]))/u',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^([A-Z])([.\)]\s+)(.+)$/us', $part, $m)
                && preg_match('/^[A-ZÀ-Ÿ]/u', $m[3])
            ) {
                $rest = $m[3];
                $title = $rest;
                $body = '';
                if (preg_match('/^(.+?)\s+(?=\d+\.\s)/u', $rest, $tm)) {
                    $title = trim($tm[1]);
                    $body = trim(substr($rest, strlen($tm[1])));
                } elseif (preg_match('/^([A-ZÀ-Ÿ0-9\/\'’ ,&-]+)$/u', $rest)) {
                    $title = $rest;
                    $body = '';
                }
                $letter = $m[1].'.';
                $out[] = [
                    'title' => $letter.' '.self::prettyHeading($title),
                    'body' => $body,
                ];
            } else {
                $out[] = ['title' => '', 'body' => $part];
            }
        }

        return $out ?: [['title' => '', 'body' => $text]];
    }

    protected static function formatPoints($text)
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $clauses = self::splitNumbered($text);
        if (count($clauses) === 1 && empty($clauses[0]['n'])) {
            $dashes = self::splitDashes($clauses[0]['text']);
            if (count($dashes) > 1) {
                return self::dashList($dashes);
            }

            return '<p class="cwa-para">'.self::inline($clauses[0]['text']).'</p>';
        }

        $html = '<ol class="cwa-points">';
        foreach ($clauses as $clause) {
            if ($clause['text'] === '' && empty($clause['n'])) {
                continue;
            }
            $html .= '<li>'.self::clauseInner($clause['text']).'</li>';
        }
        $html .= '</ol>';

        return $html;
    }

    protected static function splitNumbered($text)
    {
        if (! preg_match('/(?:^|\s)\d+\.\s/u', $text)) {
            return [['n' => null, 'text' => $text]];
        }

        $parts = preg_split('/(?=(?:^|\s)\d+\.\s)/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^(\d+)\.\s*(.*)$/us', $part, $m)) {
                $body = trim($m[2]);
                if ($body === '' || preg_match('/^\d+\.\s/u', $body)) {
                    continue;
                }
                $out[] = ['n' => $m[1], 'text' => $body];
            } else {
                $out[] = ['n' => null, 'text' => $part];
            }
        }

        return $out ?: [['n' => null, 'text' => $text]];
    }

    protected static function clauseInner($text)
    {
        $text = trim($text);
        $letters = self::splitLettered($text);
        if (count($letters) > 1) {
            $lead = trim($letters[0]['letter'] ? '' : $letters[0]['text']);
            $start = $letters[0]['letter'] ? 0 : 1;
            $leadHtml = $lead !== '' ? self::inline($lead) : '';
            $html = $leadHtml !== '' ? '<p class="cwa-lead">'.$leadHtml.'</p>' : '';
            $html .= '<ul class="cwa-letters">';
            for ($i = $start; $i < count($letters); $i++) {
                $html .= '<li>'.self::inline($letters[$i]['text']).'</li>';
            }
            $html .= '</ul>';

            return $html;
        }

        $dashes = self::splitDashes($text);
        if (count($dashes) > 1) {
            $lead = trim($dashes[0]);
            $html = $lead !== '' && ! preg_match('/^[-–]\s/', $lead)
                ? '<p class="cwa-lead">'.self::inline($lead).'</p>'
                : '';
            $items = preg_match('/^[-–]\s/', $lead) ? $dashes : array_slice($dashes, 1);

            return $html.self::dashList($items);
        }

        return self::inline($text);
    }

    protected static function splitLettered($text)
    {
        if (! preg_match('/(?:^|\s)[a-z]\)\s/u', $text)) {
            return [['letter' => null, 'text' => $text]];
        }

        $parts = preg_split('/(?=(?:^|\s)[a-z]\)\s)/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($parts as $i => $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^([a-z])\)\s*(.*)$/us', $part, $m)) {
                $out[] = ['letter' => $m[1], 'text' => trim($m[2])];
            } elseif ($i === 0) {
                $out[] = ['letter' => null, 'text' => $part];
            } else {
                $out[] = ['letter' => null, 'text' => $part];
            }
        }

        return $out ?: [['letter' => null, 'text' => $text]];
    }

    protected static function splitDashes($text)
    {
        if (! preg_match('/(?:^|\s)[-–]\s+\S/u', $text)) {
            return [$text];
        }

        $parts = preg_split('/(?=(?:^|\s)[-–]\s+\S)/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $out[] = $part;
        }

        return $out ?: [$text];
    }

    protected static function dashList(array $items)
    {
        $html = '<ul class="cwa-dashes">';
        foreach ($items as $item) {
            $item = preg_replace('/^[-–]\s*/u', '', trim($item));
            if ($item === '') {
                continue;
            }
            $html .= '<li>'.self::inline($item).'</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    protected static function prettyHeading($text)
    {
        $text = trim($text);
        $text = preg_replace('/\s*\/\s*/u', ' / ', $text);
        if ($text === '') {
            return $text;
        }
        $letters = preg_replace('/[^A-Za-zÀ-ÿ]/u', '', $text);
        if ($letters !== '' && $letters === mb_strtoupper($letters, 'UTF-8') && mb_strlen($letters, 'UTF-8') > 3) {
            $text = mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }
        $swap = [
            'Cwa' => 'CWA',
            'Afc' => 'AFC',
            'Bapec' => 'BAPEC',
            'Agm' => 'AGM',
        ];
        foreach ($swap as $from => $to) {
            $text = preg_replace('/\b'.preg_quote($from, '/').'\b/u', $to, $text);
        }

        return $text;
    }

    protected static function inline($text)
    {
        $text = trim($text);
        $text = preg_replace(
            '/^(NAME|NATURE|MOTTO|PATRON SAINT|OBJECTIVES|MEMBERSHIP|STRUCTURE|CATERING)\b[:\s]*/u',
            '',
            $text
        );
        $text = self::esc($text);
        $text = preg_replace(
            '/\b(To serve and not to be served|Servir et non être servi)\b/u',
            '<strong class="cwa-motto">$1</strong>',
            $text
        );
        $text = preg_replace(
            '/\b(\d+\s*(?:months?|years?|Francs CFA|members?|officials?|semaines?|ans?|mois))\b/iu',
            '<strong>$1</strong>',
            $text
        );

        return $text;
    }

    protected static function esc($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
