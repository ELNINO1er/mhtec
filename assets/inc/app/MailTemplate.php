<?php

use PHPMailer\PHPMailer\PHPMailer;

class MailTemplate
{
    private const BRAND_NAME = 'MHTECH Consulting';
    private const BRAND_WEBSITE = 'https://mhtechconsulting.com';
    private const BRAND_EMAIL = 'contact@mhtechconsulting.com';
    private const BRAND_PHONE = '+1 (248) 938 1944';
    private const BRAND_LOCATION_FR = 'A distance, sur rendez-vous';
    private const BRAND_LOCATION_EN = 'Remote by appointment';

    public static function normalizeLang(?string $lang): string
    {
        return strtolower(trim((string) $lang)) === 'fr' ? 'fr' : 'en';
    }

    public static function resetMailer(PHPMailer $mail, ?string $lang = null): void
    {
        $mail->clearAllRecipients();
        $mail->clearReplyTos();
        $mail->clearAttachments();
        $mail->Subject = '';
        $mail->Body = '';
        $mail->AltBody = '';
        $mail->setLanguage(self::normalizeLang($lang), __DIR__ . '/PHPMailer/language/');
    }

    public static function build(PHPMailer $mail, array $config): array
    {
        $lang = self::normalizeLang($config['lang'] ?? null);
        $copy = self::dictionary($lang);
        $logoCid = self::embedLogo($mail);

        $preheader = (string) ($config['preheader'] ?? '');
        $eyebrow = (string) ($config['eyebrow'] ?? '');
        $title = (string) ($config['title'] ?? '');
        $intro = (string) ($config['intro'] ?? '');
        $badge = (string) ($config['badge'] ?? '');
        $cards = is_array($config['cards'] ?? null) ? $config['cards'] : [];
        $steps = is_array($config['steps'] ?? null) ? $config['steps'] : [];
        $closing = (string) ($config['closing'] ?? '');
        $footerNote = (string) ($config['footer_note'] ?? $copy['footer_note']);

        $html = '<!DOCTYPE html>
<html lang="' . self::escape($lang) . '">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . self::escape($title) . '</title>
</head>
<body style="margin:0; padding:0; background-color:#eef3f8; font-family:Arial, Helvetica, sans-serif; color:#22324a;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">' . self::escape($preheader) . '</div>
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#eef3f8; width:100%; border-collapse:collapse;">
        <tr>
            <td align="center" style="padding:28px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:700px; width:100%; border-collapse:separate;">
                    <tr>
                        <td style="background:linear-gradient(135deg, #0f2339 0%, #39597c 100%); border-radius:24px 24px 0 0; padding:28px 32px 24px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                <tr>
                                    <td align="left" valign="middle">';

        if ($logoCid !== null) {
            $html .= '<img src="cid:' . self::escape($logoCid) . '" alt="' . self::escape(self::BRAND_NAME) . '" width="230" style="display:block; width:230px; max-width:100%; height:auto; margin-bottom:18px;">';
        } else {
            $html .= '<div style="font-size:26px; line-height:32px; color:#ffffff; font-weight:700; margin-bottom:18px;">' . self::escape(self::BRAND_NAME) . '</div>';
        }

        $html .= '<div style="font-size:12px; line-height:18px; letter-spacing:1.6px; text-transform:uppercase; color:#b9d5ee; font-weight:700;">' . self::escape($eyebrow) . '</div>
                                        <div style="font-size:32px; line-height:40px; color:#ffffff; font-weight:700; margin-top:10px;">' . self::escape($title) . '</div>
                                        <div style="font-size:16px; line-height:26px; color:#e8f1f8; margin-top:14px;">' . self::escape($intro) . '</div>';

        if ($badge !== '') {
            $html .= '<div style="margin-top:18px;"><span style="display:inline-block; padding:8px 14px; border-radius:999px; background-color:#d8ecfb; color:#16324f; font-size:13px; line-height:18px; font-weight:700;">' . self::escape($badge) . '</span></div>';
        }

        $html .= '                  </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; padding:24px; border-radius:0 0 24px 24px;">' .
                            self::renderCards($cards, $copy) .
                            self::renderSteps($steps, $copy);

        if ($closing !== '') {
            $html .= '<div style="margin-top:24px; font-size:15px; line-height:24px; color:#31445f;">' . nl2br(self::escape($closing)) . '</div>';
        }

        $html .= self::renderContactBlock($copy) . '
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 8px 0 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                <tr>
                                    <td style="font-size:13px; line-height:22px; color:#62758f; text-align:center; padding-bottom:8px;">' . self::escape($footerNote) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-size:12px; line-height:20px; color:#7d8ea5; text-align:center; padding-top:10px;">' . self::escape($copy['copyright']) . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

        $textParts = [];

        foreach ([$title, $intro] as $part) {
            if ($part !== '') {
                $textParts[] = $part;
            }
        }

        foreach ($cards as $card) {
            $cardTitle = trim((string) ($card['title'] ?? ''));
            if ($cardTitle !== '') {
                $textParts[] = '';
                $textParts[] = $cardTitle;
            }

            foreach (($card['rows'] ?? []) as $row) {
                $label = trim((string) ($row['label'] ?? ''));
                $value = trim((string) ($row['value'] ?? ''));
                if ($label !== '' && $value !== '') {
                    $textParts[] = $label . ': ' . $value;
                }
            }

            $messageValue = trim((string) ($card['message'] ?? ''));
            if ($messageValue !== '') {
                $messageLabel = trim((string) ($card['message_label'] ?? $copy['message_label']));
                $textParts[] = $messageLabel . ':';
                $textParts[] = $messageValue;
            }

            foreach (($card['notes'] ?? []) as $note) {
                $note = trim((string) $note);
                if ($note !== '') {
                    $textParts[] = $note;
                }
            }
        }

        if ($steps !== []) {
            $textParts[] = '';
            $textParts[] = $copy['next_steps_title'] . ':';
            foreach ($steps as $index => $step) {
                $step = trim((string) $step);
                if ($step !== '') {
                    $textParts[] = ($index + 1) . '. ' . $step;
                }
            }
        }

        if ($closing !== '') {
            $textParts[] = '';
            $textParts[] = $closing;
        }

        $textParts[] = '';
        $textParts[] = $copy['contact_heading'];
        $textParts[] = self::BRAND_WEBSITE;
        $textParts[] = self::BRAND_EMAIL;
        $textParts[] = self::BRAND_PHONE;
        $textParts[] = $copy['location_label'] . ': ' . $copy['location_value'];
        $textParts[] = '';
        $textParts[] = $footerNote;
        $textParts[] = $copy['copyright'];

        return [
            'html' => $html,
            'text' => implode("\n", array_values(array_filter($textParts, static function ($line) {
                return $line !== null;
            })))
        ];
    }

    private static function renderCards(array $cards, array $copy): string
    {
        $html = '';

        foreach ($cards as $card) {
            $title = trim((string) ($card['title'] ?? ''));
            $rows = is_array($card['rows'] ?? null) ? $card['rows'] : [];
            $notes = is_array($card['notes'] ?? null) ? $card['notes'] : [];
            $messageValue = trim((string) ($card['message'] ?? ''));
            $messageLabel = trim((string) ($card['message_label'] ?? $copy['message_label']));

            $hasContent = ($title !== '') || ($rows !== []) || ($notes !== []) || ($messageValue !== '');
            if (!$hasContent) {
                continue;
            }

            $html .= '<div style="background-color:#f7fafe; border:1px solid #dbe6f2; border-radius:18px; padding:22px; margin-bottom:18px;">';

            if ($title !== '') {
                $html .= '<div style="font-size:18px; line-height:26px; font-weight:700; color:#16324f; margin-bottom:14px;">' . self::escape($title) . '</div>';
            }

            if ($rows !== []) {
                $html .= '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%; border-collapse:collapse;">';
                foreach ($rows as $row) {
                    $label = trim((string) ($row['label'] ?? ''));
                    $value = trim((string) ($row['value'] ?? ''));
                    if ($label === '' || $value === '') {
                        continue;
                    }

                    $href = trim((string) ($row['href'] ?? ''));
                    $formattedValue = self::escape($value);
                    if ($href !== '') {
                        $formattedValue = '<a href="' . self::escape($href) . '" style="color:#315a80; text-decoration:none;">' . self::escape($value) . '</a>';
                    }

                    $html .= '<tr>
                        <td valign="top" style="padding:8px 0; width:180px; font-size:14px; line-height:22px; color:#5d7189; font-weight:700;">' . self::escape($label) . '</td>
                        <td valign="top" style="padding:8px 0; font-size:14px; line-height:22px; color:#22324a;">' . $formattedValue . '</td>
                    </tr>';
                }
                $html .= '</table>';
            }

            if ($messageValue !== '') {
                $html .= '<div style="margin-top:14px;">';
                $html .= '<div style="font-size:14px; line-height:22px; color:#5d7189; font-weight:700; margin-bottom:8px;">' . self::escape($messageLabel) . '</div>';
                $html .= '<div style="font-size:14px; line-height:24px; color:#22324a; background-color:#ffffff; border:1px solid #dbe6f2; border-radius:14px; padding:16px;">' . nl2br(self::escape($messageValue)) . '</div>';
                $html .= '</div>';
            }

            foreach ($notes as $note) {
                $note = trim((string) $note);
                if ($note === '') {
                    continue;
                }
                $html .= '<div style="margin-top:14px; font-size:13px; line-height:21px; color:#5d7189;">' . self::escape($note) . '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    private static function renderSteps(array $steps, array $copy): string
    {
        $steps = array_values(array_filter(array_map('strval', $steps), static function ($step) {
            return trim($step) !== '';
        }));

        if ($steps === []) {
            return '';
        }

        $html = '<div style="background-color:#16324f; border-radius:18px; padding:22px 22px 10px 22px; margin-top:6px;">
            <div style="font-size:18px; line-height:26px; font-weight:700; color:#ffffff; margin-bottom:16px;">' . self::escape($copy['next_steps_title']) . '</div>';

        foreach ($steps as $index => $step) {
            $html .= '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%; border-collapse:collapse; margin-bottom:12px;">
                <tr>
                    <td valign="top" style="width:36px;">
                        <span style="display:inline-block; width:26px; height:26px; border-radius:50%; background-color:#d8ecfb; color:#16324f; font-size:13px; line-height:26px; font-weight:700; text-align:center;">' . ($index + 1) . '</span>
                    </td>
                    <td valign="top" style="color:#eaf2f9; font-size:14px; line-height:23px;">' . self::escape($step) . '</td>
                </tr>
            </table>';
        }

        $html .= '</div>';

        return $html;
    }

    private static function renderContactBlock(array $copy): string
    {
        $rows = [
            ['label' => $copy['website_label'], 'value' => self::BRAND_WEBSITE, 'href' => self::BRAND_WEBSITE],
            ['label' => $copy['email_label'], 'value' => self::BRAND_EMAIL, 'href' => 'mailto:' . self::BRAND_EMAIL],
            ['label' => $copy['phone_label'], 'value' => self::BRAND_PHONE, 'href' => 'tel:+12489381944'],
            ['label' => $copy['location_label'], 'value' => $copy['location_value'], 'href' => '']
        ];

        $html = '<div style="background-color:#ffffff; border:1px solid #dbe6f2; border-radius:18px; padding:22px; margin-top:18px;">
            <div style="font-size:18px; line-height:26px; font-weight:700; color:#16324f; margin-bottom:14px;">' . self::escape($copy['contact_heading']) . '</div>
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%; border-collapse:collapse;">';

        foreach ($rows as $row) {
            $valueHtml = self::escape($row['value']);
            if ($row['href'] !== '') {
                $valueHtml = '<a href="' . self::escape($row['href']) . '" style="color:#315a80; text-decoration:none;">' . self::escape($row['value']) . '</a>';
            }

            $html .= '<tr>
                <td valign="top" style="padding:8px 0; width:180px; font-size:14px; line-height:22px; color:#5d7189; font-weight:700;">' . self::escape($row['label']) . '</td>
                <td valign="top" style="padding:8px 0; font-size:14px; line-height:22px; color:#22324a;">' . $valueHtml . '</td>
            </tr>';
        }

        $html .= '</table></div>';

        return $html;
    }

    private static function embedLogo(PHPMailer $mail): ?string
    {
        $candidatePaths = [
            dirname(__DIR__, 2) . '/images/resources/logo-mhtech-email.png',
            dirname(__DIR__, 2) . '/images/resources/logo-mhtech-header.svg'
        ];

        foreach ($candidatePaths as $logoPath) {
            if (!is_file($logoPath)) {
                continue;
            }

            $cid = 'mhtech-logo';
            $mail->addEmbeddedImage($logoPath, $cid, basename($logoPath));
            return $cid;
        }

        return null;
    }

    private static function dictionary(string $lang): array
    {
        if ($lang === 'fr') {
            return [
                'message_label' => 'Message',
                'next_steps_title' => 'Prochaines etapes',
                'contact_heading' => 'Contacts MHTECH Consulting',
                'website_label' => 'Site web',
                'email_label' => 'Email',
                'phone_label' => 'Telephone',
                'location_label' => 'Disponibilite',
                'location_value' => self::BRAND_LOCATION_FR,
                'footer_note' => 'MHTECH Consulting accompagne les entreprises sur leurs enjeux IT, cybersecurite, cloud et staffing.',
                'copyright' => 'Copyright 2026 MHTECH Consulting. Tous droits reserves.'
            ];
        }

        return [
            'message_label' => 'Message',
            'next_steps_title' => 'Next steps',
            'contact_heading' => 'MHTECH Consulting contacts',
            'website_label' => 'Website',
            'email_label' => 'Email',
            'phone_label' => 'Phone',
            'location_label' => 'Availability',
            'location_value' => self::BRAND_LOCATION_EN,
            'footer_note' => 'MHTECH Consulting supports businesses across IT consulting, cybersecurity, cloud and staffing needs.',
            'copyright' => 'Copyright 2026 MHTECH Consulting. All rights reserved.'
        ];
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
