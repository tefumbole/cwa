<?php

namespace App\Support;

class MembershipWhatsApp
{
    const VERSE = 'To serve and not to be served / Servir et non être servi (Mt 20:28)';

    public static function requestReceived($name)
    {
        return self::compose(
            'Membership request received / Demande d\'adhésion reçue',
            $name,
            "Thank you. Your CWA Cameroon membership has been received and is under review by the National Secretariat.\n\n"
            ."Merci. Votre adhésion à l'AFC Cameroun a été reçue et est examinée par le Secrétariat national."
        );
    }

    public static function letterSigned($name, $year, $pdfUrl = null)
    {
        $body = "The National Executive is pleased to send your Letter of Admission. Your year of joining CWA Cameroon is *{$year}*.\n\n"
            ."L'Exécutif général a le plaisir de vous envoyer votre lettre d'admission. Votre année d'entrée à l'AFC Cameroun est *{$year}*.\n\n"
            .'Please find the signed letter attached. / Veuillez trouver la lettre signée en pièce jointe.';
        if ($pdfUrl) {
            $body .= "\n\n".$pdfUrl;
        }

        return self::compose(
            'Letter of Admission / Lettre d\'admission',
            $name,
            $body
        );
    }

    public static function compose($heading, $name, $body)
    {
        $msg = "*CWA Cameroon*\n\n";
        $msg .= '*'.$heading."*\n";
        $msg .= "────────────────\n";
        $greeting = trim((string) $name);
        if ($greeting !== '') {
            $msg .= 'Hello *'.$greeting."*, / Bonjour *".$greeting."*,\n\n";
        }
        $msg .= rtrim($body)."\n\n";
        $msg .= '_'.self::VERSE."_\n\n";
        $msg .= '🌐 cwacam.org';

        return $msg;
    }
}
