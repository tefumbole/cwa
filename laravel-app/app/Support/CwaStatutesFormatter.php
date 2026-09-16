<?php

namespace App\Support;

/**
 * Turn extracted statute/bylaw HTML into shareholder-style cards:
 * gold titles, numbered clauses, gold bullet markers.
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
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $clauses = self::splitNumbered($text);
        if (count($clauses) <= 1 && empty($clauses[0]['n'])) {
            return '<p>'.self::inline($text).'</p>';
        }

        $out = '<ul class="list-disc pl-5 space-y-2 marker:text-brand-gold">';
        foreach ($clauses as $clause) {
            $out .= '<li>'.self::clauseInner($clause['text']).'</li>';
        }
        $out .= '</ul>';

        return $out;
    }

    protected static function splitNumbered($text)
    {
        if (! preg_match('/^\d+\.\s/u', $text)) {
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
                $out[] = ['n' => $m[1], 'text' => trim($m[2])];
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
            $lead = trim($letters[0]['text']);
            $html = $lead !== '' ? self::inline($lead) : '';
            $html .= '<ul class="list-disc pl-5 mt-2 space-y-1 marker:text-brand-gold text-sm">';
            for ($i = 1; $i < count($letters); $i++) {
                $html .= '<li>'.self::inline($letters[$i]['text']).'</li>';
            }
            $html .= '</ul>';

            return $html;
        }

        return self::inline($text);
    }

    protected static function splitLettered($text)
    {
        if (! preg_match('/(?:^|\s)[a-z]\)\s/u', $text)) {
            return [['text' => $text]];
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

        return $out ?: [['text' => $text]];
    }

    protected static function inline($text)
    {
        $text = trim($text);
        $text = preg_replace(
            '/^(NAME|NATURE|MOTTO|PATRON SAINT|OBJECTIVES|MEMBERSHIP|STRUCTURE)\b[:\s]*/u',
            '',
            $text
        );
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace(
            '/\b(To serve and not to be served|Servir et non être servi)\b/u',
            '<strong class="text-brand-gold">$1</strong>',
            $text
        );
        $text = preg_replace(
            '/\b(\d+\s*(?:months?|years?|Francs CFA|members?|officials?))\b/iu',
            '<strong class="text-white">$1</strong>',
            $text
        );

        return $text;
    }
}
