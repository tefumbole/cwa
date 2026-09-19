<?php

namespace App\Services;

/**
 * Read Name, date of issue and place of issue from a Cameroon CNI or passport image.
 */
class CameroonIdOcrService
{
    public function extract($absolutePath, $idType = 'national_id')
    {
        $text = $this->ocrText($absolutePath);
        $parsed = $this->parse($text, $idType);
        $parsed['raw'] = $text;

        return $parsed;
    }

    protected function ocrText($path)
    {
        if (! is_file($path)) {
            return '';
        }
        $bin = trim((string) shell_exec('command -v tesseract 2>/dev/null'));
        if ($bin === '') {
            return '';
        }
        $tmp = sys_get_temp_dir().'/cwa_ocr_'.uniqid('', true);
        $cmd = escapeshellcmd($bin).' '.escapeshellarg($path).' '.escapeshellarg($tmp)
            .' -l fra+eng --psm 6 2>/dev/null';
        @exec($cmd);
        $out = is_file($tmp.'.txt') ? file_get_contents($tmp.'.txt') : '';
        @unlink($tmp.'.txt');

        return $this->normalizeText($out);
    }

    protected function normalizeText($text)
    {
        $text = strtoupper((string) $text);
        $text = str_replace(['É', 'È', 'Ê', 'Ë'], 'E', $text);
        $text = str_replace(['À', 'Â'], 'A', $text);
        $text = str_replace(['Ô'], 'O', $text);
        $text = str_replace(['Î', 'Ï'], 'I', $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);

        return trim($text);
    }

    protected function parse($text, $idType)
    {
        $name = $this->firstMatch($text, [
            '/(?:NOM(?:S)?(?: ET PRENOMS?)?|SURNAME|NAME)\s*[:\-]?\s*([A-Z][A-Z \'\-]{2,60})/u',
            '/PRENOMS?\s*[:\-]?\s*([A-Z][A-Z \'\-]{2,60})/u',
            '/GIVEN NAMES?\s*[:\-]?\s*([A-Z][A-Z \'\-]{2,60})/u',
        ]);
        $nom = $this->firstMatch($text, ['/(?:^|\n)\s*NOM\s*[:\-]?\s*([A-Z][A-Z \'\-]{2,40})/u']);
        $prenom = $this->firstMatch($text, ['/(?:PRENOMS?|GIVEN NAMES?)\s*[:\-]?\s*([A-Z][A-Z \'\-]{2,40})/u']);
        if ($nom && $prenom) {
            $name = trim($prenom.' '.$nom);
        }

        $issueDate = $this->firstMatch($text, [
            '/(?:DATE (?:DE )?DELIVRANCE|DATE OF ISSUE|DELIVRE[E]?\s+LE)\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/u',
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/u',
        ]);

        $place = $this->firstMatch($text, [
            '/(?:LIEU (?:DE )?DELIVRANCE|PLACE OF ISSUE|DELIVRE[E]?\s+A)\s*[:\-]?\s*([A-Z][A-Z \-]{2,40})/u',
        ]);
        if (! $place && $idType === 'passport') {
            if (strpos($text, 'YAOUNDE') !== false) {
                $place = 'Yaoundé';
            }
        }

        return [
            'name' => $this->titleCase($name),
            'issue_date' => $issueDate,
            'issue_place' => $this->titleCase($place),
        ];
    }

    protected function firstMatch($text, array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $val = trim($m[1]);
                $val = preg_replace('/\b(REPUBLIQUE|CAMEROUN|CAMEROON|PASSEPORT|PASSPORT|CARTE|NATIONALE|IDENTITE)\b/u', '', $val);
                $val = trim(preg_replace('/\s+/', ' ', $val));
                if (strlen($val) >= 3) {
                    return $val;
                }
            }
        }

        return '';
    }

    protected function titleCase($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        return mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
