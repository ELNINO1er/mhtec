<?php
require_once __DIR__ . '/app/settings.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/MailTemplate.php';
require_once __DIR__ . '/app/RequestGuard.php';

function detectFormLang(): string
{
    $postedLang = strtolower(trim((string) ($_POST['lang'] ?? '')));
    if (in_array($postedLang, ['fr', 'en'], true)) {
        return $postedLang;
    }

    $acceptLanguage = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    return strpos($acceptLanguage, 'fr') === 0 ? 'fr' : 'en';
}

function cleanText(string $value, string $pattern): string
{
    return trim((string) preg_replace($pattern, '', $value));
}

function getSendEmailCopy(string $lang): array
{
    if ($lang === 'fr') {
        return [
            'required' => 'Veuillez remplir tous les champs obligatoires.',
            'invalid_email' => 'Adresse email invalide.',
            'contact_save_error' => "Erreur lors de l'enregistrement de votre demande.",
            'submission_blocked' => 'La demande n a pas pu etre verifiee. Merci de reessayer.',
            'rate_limited' => 'Trop de tentatives ont ete detectees. Merci de patienter quelques minutes avant de reessayer.',
            'newsletter_success' => 'Merci pour votre abonnement ! Vous recevrez nos actualites prochainement.',
            'contact_success' => 'Merci de nous avoir contacte ! Nous vous repondrons dans les plus brefs delais.',
            'generic_error' => 'Une erreur est survenue : ',
            'newsletter_user_subject' => 'Abonnement confirme - Newsletter MHTECH Consulting',
            'newsletter_user_preheader' => 'Votre inscription a la newsletter MHTECH Consulting est confirmee.',
            'newsletter_user_eyebrow' => 'Newsletter',
            'newsletter_user_title' => 'Inscription confirmee',
            'newsletter_user_intro' => 'Merci pour votre abonnement. Vous recevrez nos actualites technologiques, nos analyses et nos conseils pratiques.',
            'newsletter_user_badge' => 'Adresse enregistree',
            'newsletter_user_card' => 'Vos informations',
            'newsletter_user_note' => 'Vous pourrez vous desinscrire a tout moment depuis nos prochains emails.',
            'newsletter_user_closing' => "Merci de votre confiance.\nL'equipe MHTECH Consulting",
            'newsletter_admin_subject' => 'Nouvel abonnement newsletter - MHTECH Consulting',
            'newsletter_admin_preheader' => 'Un nouvel abonne vient de rejoindre la newsletter.',
            'newsletter_admin_eyebrow' => 'Alerte admin',
            'newsletter_admin_title' => 'Nouvel abonnement newsletter',
            'newsletter_admin_intro' => 'Une nouvelle adresse email a ete ajoutee a la liste de diffusion MHTECH Consulting.',
            'newsletter_admin_badge' => 'Action marketing',
            'newsletter_admin_card' => "Details de l'abonnement",
            'newsletter_admin_closing' => "L'abonnement a ete enregistre en base et ne necessite aucune action technique.",
            'contact_user_subject' => 'Votre demande a bien ete recue - MHTECH Consulting',
            'contact_user_preheader' => 'Votre message a bien ete transmis a MHTECH Consulting.',
            'contact_user_eyebrow' => 'Contact',
            'contact_user_title' => 'Votre message est bien arrive',
            'contact_user_intro_prefix' => 'Bonjour ',
            'contact_user_intro_suffix' => ', merci pour votre prise de contact. Notre equipe examine votre demande et reviendra vers vous dans les plus brefs delais.',
            'contact_user_badge' => 'Demande recue',
            'contact_user_summary' => 'Recapitulatif',
            'contact_user_message_title' => 'Votre message',
            'contact_user_closing' => "Nous vous repondrons rapidement avec la suite adaptee a votre besoin.\nL'equipe MHTECH Consulting",
            'contact_admin_subject' => 'Nouvelle demande de contact - MHTECH Consulting',
            'contact_admin_preheader' => 'Une nouvelle demande de contact attend votre traitement.',
            'contact_admin_eyebrow' => 'Alerte admin',
            'contact_admin_title' => 'Nouvelle demande de contact',
            'contact_admin_intro' => "Une nouvelle soumission a ete enregistree sur le site. Vous pouvez repondre directement a l'expediteur depuis votre client email.",
            'contact_admin_badge' => 'Priorite commerciale',
            'contact_admin_coords' => 'Coordonnees',
            'contact_admin_message_title' => 'Message recu',
            'contact_admin_closing' => 'La demande a ete enregistree avec succes dans la base de donnees.',
            'label_name' => 'Nom',
            'label_email' => 'Email',
            'label_phone' => 'Telephone',
            'label_subject' => 'Sujet',
            'label_request_type' => 'Type de demande',
            'label_source' => 'Source',
            'label_ip' => 'IP',
            'label_db_id' => 'ID en base',
            'label_newsletter_source' => 'Origine',
            'not_available' => 'Non disponible',
            'source_contact_page' => 'Page contact',
            'source_staffing_page' => 'Page staffing',
            'source_chat_popup' => 'Chat popup',
            'source_unknown' => 'Source inconnue',
            'newsletter_source_newsletter' => 'Newsletter',
            'newsletter_source_newsletter_home' => 'Newsletter accueil',
            'newsletter_source_newsletter_about' => 'Newsletter a propos',
            'newsletter_source_newsletter_services' => 'Newsletter services',
            'newsletter_source_newsletter_staffing' => 'Newsletter staffing',
            'newsletter_source_newsletter_contact' => 'Newsletter contact',
            'newsletter_source_newsletter_blog' => 'Newsletter blog',
            'newsletter_source_newsletter_testimonials' => 'Newsletter temoignages',
            'request_consulting' => 'Consulting IT',
            'request_staffing' => 'Staffing IT',
            'request_candidature' => 'Candidature',
            'request_autre' => 'Autre'
        ];
    }

    return [
        'required' => 'Please fill in all required fields.',
        'invalid_email' => 'Invalid email address.',
        'contact_save_error' => 'An error occurred while saving your request.',
        'submission_blocked' => 'The request could not be verified. Please try again.',
        'rate_limited' => 'Too many attempts were detected. Please wait a few minutes before trying again.',
        'newsletter_success' => 'Thank you for subscribing. You will receive our updates soon.',
        'contact_success' => 'Thank you for contacting us. We will get back to you shortly.',
        'generic_error' => 'An error occurred: ',
        'newsletter_user_subject' => 'Subscription confirmed - MHTECH Consulting Newsletter',
        'newsletter_user_preheader' => 'Your MHTECH Consulting newsletter subscription is confirmed.',
        'newsletter_user_eyebrow' => 'Newsletter',
        'newsletter_user_title' => 'Subscription confirmed',
        'newsletter_user_intro' => 'Thank you for subscribing. You will receive our technology updates, insights and practical recommendations.',
        'newsletter_user_badge' => 'Email registered',
        'newsletter_user_card' => 'Your details',
        'newsletter_user_note' => 'You can unsubscribe at any time from one of our future emails.',
        'newsletter_user_closing' => "Thank you for your trust.\nThe MHTECH Consulting team",
        'newsletter_admin_subject' => 'New newsletter subscription - MHTECH Consulting',
        'newsletter_admin_preheader' => 'A new subscriber has joined the newsletter.',
        'newsletter_admin_eyebrow' => 'Admin alert',
        'newsletter_admin_title' => 'New newsletter subscription',
        'newsletter_admin_intro' => 'A new email address has been added to the MHTECH Consulting mailing list.',
        'newsletter_admin_badge' => 'Marketing action',
        'newsletter_admin_card' => 'Subscription details',
        'newsletter_admin_closing' => 'The subscription has been stored in the database and does not require technical action.',
        'contact_user_subject' => 'Your request has been received - MHTECH Consulting',
        'contact_user_preheader' => 'Your message has been delivered to MHTECH Consulting.',
        'contact_user_eyebrow' => 'Contact',
        'contact_user_title' => 'Your message has arrived',
        'contact_user_intro_prefix' => 'Hello ',
        'contact_user_intro_suffix' => ', thank you for reaching out. Our team is reviewing your request and will get back to you shortly.',
        'contact_user_badge' => 'Request received',
        'contact_user_summary' => 'Summary',
        'contact_user_message_title' => 'Your message',
        'contact_user_closing' => "We will get back to you quickly with the next steps adapted to your need.\nThe MHTECH Consulting team",
        'contact_admin_subject' => 'New contact request - MHTECH Consulting',
        'contact_admin_preheader' => 'A new contact request is awaiting review.',
        'contact_admin_eyebrow' => 'Admin alert',
        'contact_admin_title' => 'New contact request',
        'contact_admin_intro' => 'A new submission has been recorded on the website. You can reply directly to the sender from your email client.',
        'contact_admin_badge' => 'Sales priority',
        'contact_admin_coords' => 'Contact details',
        'contact_admin_message_title' => 'Received message',
        'contact_admin_closing' => 'The request has been saved successfully in the database.',
        'label_name' => 'Name',
        'label_email' => 'Email',
        'label_phone' => 'Phone',
        'label_subject' => 'Subject',
        'label_request_type' => 'Request type',
        'label_source' => 'Source',
        'label_ip' => 'IP',
        'label_db_id' => 'Database ID',
        'label_newsletter_source' => 'Source',
        'not_available' => 'Not available',
        'source_contact_page' => 'Contact page',
        'source_staffing_page' => 'Staffing page',
        'source_chat_popup' => 'Chat popup',
        'source_unknown' => 'Unknown source',
        'newsletter_source_newsletter' => 'Newsletter',
        'newsletter_source_newsletter_home' => 'Homepage newsletter',
        'newsletter_source_newsletter_about' => 'About newsletter',
        'newsletter_source_newsletter_services' => 'Services newsletter',
        'newsletter_source_newsletter_staffing' => 'Staffing newsletter',
        'newsletter_source_newsletter_contact' => 'Contact newsletter',
        'newsletter_source_newsletter_blog' => 'Blog newsletter',
        'newsletter_source_newsletter_testimonials' => 'Testimonials newsletter',
        'request_consulting' => 'IT Consulting',
        'request_staffing' => 'IT Staffing',
        'request_candidature' => 'Job application',
        'request_autre' => 'Other'
    ];
}

function formatRequestTypeLabel(string $lang, string $requestType): string
{
    $copy = getSendEmailCopy($lang);
    $map = [
        'consulting' => $copy['request_consulting'],
        'staffing' => $copy['request_staffing'],
        'candidature' => $copy['request_candidature'],
        'autre' => $copy['request_autre'],
        'other' => $copy['request_autre']
    ];

    return $map[$requestType] ?? trim($requestType);
}

function formatContactSourceLabel(string $lang, string $source): string
{
    $copy = getSendEmailCopy($lang);
    $map = [
        'contact_page' => $copy['source_contact_page'],
        'staffing_page' => $copy['source_staffing_page'],
        'chat_popup' => $copy['source_chat_popup'],
        'unknown' => $copy['source_unknown']
    ];

    return $map[$source] ?? $copy['source_unknown'];
}

function formatNewsletterSourceLabel(string $lang, string $source): string
{
    $copy = getSendEmailCopy($lang);
    $key = 'newsletter_source_' . $source;
    return $copy[$key] ?? $copy['newsletter_source_newsletter'];
}

try {
    $lang = detectFormLang();
    $copy = getSendEmailCopy($lang);

    $name = isset($_POST['name']) ? cleanText((string) $_POST['name'], "/[^.\-' a-zA-Z0-9]/") : '';
    $senderEmail = isset($_POST['email']) ? cleanText((string) $_POST['email'], "/[^.\-_@a-zA-Z0-9]/") : '';
    if (RequestGuard::isHoneypotTriggered($_POST) || !RequestGuard::hasValidSubmissionTiming($_POST, 2, 43200)) {
        throw new Exception($copy['submission_blocked']);
    }

    if (RequestGuard::isRateLimited('sendemail', 8, 600)) {
        throw new Exception($copy['rate_limited']);
    }

    $phoneInput = $_POST['phone'] ?? $_POST['Phone'] ?? '';
    $phone = $phoneInput !== '' ? cleanText((string) $phoneInput, "/[^+.\-() 0-9]/") : '';
    $subject = isset($_POST['subject']) ? cleanText((string) $_POST['subject'], "/[^.\-_@a-zA-Z0-9 ]/") : '';
    $message = isset($_POST['message']) ? trim((string) preg_replace("/(From:|To:|BCC:|CC:|Subject:|Content-Type:)/i", '', (string) ($_POST['message'] ?? ''))) : '';
    $requestType = isset($_POST['request_type']) ? cleanText((string) $_POST['request_type'], "/[^a-zA-Z0-9_]/") : '';
    $adminEmail = trim((string) Env::get('ADMIN_EMAIL', 'contact@mhtechconsulting.com'));

    if ($adminEmail === '' || stripos($adminEmail, 'scriptfusions') !== false || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $adminEmail = 'contact@mhtechconsulting.com';
    }

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $refererPath = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH);
    $refererPage = basename((string) ($refererPath ?: ''));

    $newsletterSources = [
        'index.html' => 'newsletter_home',
        'about.html' => 'newsletter_about',
        'services.html' => 'newsletter_services',
        'staffing.html' => 'newsletter_staffing',
        'contact.html' => 'newsletter_contact',
        'blog.html' => 'newsletter_blog',
        'testimonials.html' => 'newsletter_testimonials'
    ];
    $newsletterSource = $newsletterSources[$refererPage] ?? 'newsletter';

    $source = 'unknown';
    if ($name !== '' && $message !== '') {
        if ($requestType !== '') {
            $source = 'contact_page';
        } elseif ($subject !== '') {
            $source = 'staffing_page';
        } else {
            $source = 'chat_popup';
        }
    }

    if ($senderEmail === '') {
        echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($copy['required']) . '</div>';
        return;
    }

    if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($copy['invalid_email']) . '</div>';
        return;
    }

    if ($name === '' && $message === '') {
        try {
            $db = Database::getInstance();
            $subscriptionId = $db->insert('newsletter_subscriptions', [
                'email' => $senderEmail,
                'source' => $newsletterSource,
                'ip_address' => $ipAddress
            ]);

            $db->insert('activity_logs', [
                'table_name' => 'newsletter_subscriptions',
                'record_id' => $subscriptionId,
                'action' => 'insert',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);
        } catch (Exception $dbError) {
            error_log('Newsletter DB Error: ' . $dbError->getMessage());
        }

        try {
            MailTemplate::resetMailer($mail, $lang);
            $mail->addAddress($senderEmail);
            $mail->Subject = $copy['newsletter_user_subject'];
            $userTemplate = MailTemplate::build($mail, [
                'lang' => $lang,
                'preheader' => $copy['newsletter_user_preheader'],
                'eyebrow' => $copy['newsletter_user_eyebrow'],
                'title' => $copy['newsletter_user_title'],
                'intro' => $copy['newsletter_user_intro'],
                'badge' => $copy['newsletter_user_badge'],
                'cards' => [
                    [
                        'title' => $copy['newsletter_user_card'],
                        'rows' => [
                            ['label' => $copy['label_email'], 'value' => $senderEmail]
                        ],
                        'notes' => [
                            $copy['newsletter_user_note']
                        ]
                    ]
                ],
                'closing' => $copy['newsletter_user_closing']
            ]);
            $mail->Body = $userTemplate['html'];
            $mail->AltBody = $userTemplate['text'];
            $mail->send();

            MailTemplate::resetMailer($mail, $lang);
            $mail->addAddress($adminEmail);
            $mail->addReplyTo($senderEmail);
            $mail->Subject = $copy['newsletter_admin_subject'];
            $adminTemplate = MailTemplate::build($mail, [
                'lang' => $lang,
                'preheader' => $copy['newsletter_admin_preheader'],
                'eyebrow' => $copy['newsletter_admin_eyebrow'],
                'title' => $copy['newsletter_admin_title'],
                'intro' => $copy['newsletter_admin_intro'],
                'badge' => $copy['newsletter_admin_badge'],
                'cards' => [
                    [
                        'title' => $copy['newsletter_admin_card'],
                        'rows' => [
                            ['label' => $copy['label_email'], 'value' => $senderEmail, 'href' => 'mailto:' . $senderEmail],
                            ['label' => $copy['label_newsletter_source'], 'value' => formatNewsletterSourceLabel($lang, $newsletterSource)],
                            ['label' => $copy['label_ip'], 'value' => (string) ($ipAddress ?? $copy['not_available'])]
                        ]
                    ]
                ],
                'closing' => $copy['newsletter_admin_closing']
            ]);
            $mail->Body = $adminTemplate['html'];
            $mail->AltBody = $adminTemplate['text'];
            $mail->send();
        } catch (Exception $mailError) {
            error_log('Newsletter Email Error: ' . $mailError->getMessage());
        }

        echo "<div class='alert alert-success' role='alert'>" . htmlspecialchars($copy['newsletter_success']) . '</div>';
        return;
    }

    if ($name === '' || $message === '') {
        echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($copy['required']) . '</div>';
        return;
    }

    try {
        $db = Database::getInstance();
        $contactData = [
            'name' => $name,
            'email' => $senderEmail,
            'phone' => $phone,
            'subject' => $subject,
            'request_type' => $requestType,
            'message' => $message,
            'source' => $source,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ];

        $contactData = array_filter($contactData, static function ($value) {
            return $value !== null && $value !== '';
        });

        $contactId = $db->insert('contacts', $contactData);

        $db->insert('activity_logs', [
            'table_name' => 'contacts',
            'record_id' => $contactId,
            'action' => 'insert',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    } catch (Exception $dbError) {
        error_log('Contact DB Error: ' . $dbError->getMessage());
        throw new Exception($copy['contact_save_error']);
    }

    try {
        $requestTypeLabel = $requestType !== '' ? formatRequestTypeLabel($lang, $requestType) : '';
        $sourceLabel = formatContactSourceLabel($lang, $source);

        $userRows = [
            ['label' => $copy['label_name'], 'value' => $name],
            ['label' => $copy['label_email'], 'value' => $senderEmail]
        ];
        if ($phone !== '') {
            $userRows[] = ['label' => $copy['label_phone'], 'value' => $phone];
        }
        if ($subject !== '') {
            $userRows[] = ['label' => $copy['label_subject'], 'value' => $subject];
        }
        if ($requestTypeLabel !== '') {
            $userRows[] = ['label' => $copy['label_request_type'], 'value' => $requestTypeLabel];
        }

        MailTemplate::resetMailer($mail, $lang);
        $mail->addAddress($senderEmail, $name);
        $mail->Subject = $copy['contact_user_subject'];
        $userTemplate = MailTemplate::build($mail, [
            'lang' => $lang,
            'preheader' => $copy['contact_user_preheader'],
            'eyebrow' => $copy['contact_user_eyebrow'],
            'title' => $copy['contact_user_title'],
            'intro' => $copy['contact_user_intro_prefix'] . $name . $copy['contact_user_intro_suffix'],
            'badge' => $copy['contact_user_badge'],
            'cards' => [
                [
                    'title' => $copy['contact_user_summary'],
                    'rows' => $userRows
                ],
                [
                    'title' => $copy['contact_user_message_title'],
                    'message' => $message
                ]
            ],
            'closing' => $copy['contact_user_closing']
        ]);
        $mail->Body = $userTemplate['html'];
        $mail->AltBody = $userTemplate['text'];
        $mail->send();

        $adminRows = [
            ['label' => $copy['label_name'], 'value' => $name],
            ['label' => $copy['label_email'], 'value' => $senderEmail, 'href' => 'mailto:' . $senderEmail]
        ];
        if ($phone !== '') {
            $adminRows[] = ['label' => $copy['label_phone'], 'value' => $phone, 'href' => 'tel:' . preg_replace('/\s+/', '', $phone)];
        }
        if ($subject !== '') {
            $adminRows[] = ['label' => $copy['label_subject'], 'value' => $subject];
        }
        if ($requestTypeLabel !== '') {
            $adminRows[] = ['label' => $copy['label_request_type'], 'value' => $requestTypeLabel];
        }
        $adminRows[] = ['label' => $copy['label_source'], 'value' => $sourceLabel];
        $adminRows[] = ['label' => $copy['label_db_id'], 'value' => '#' . $contactId];

        MailTemplate::resetMailer($mail, $lang);
        $mail->addAddress($adminEmail);
        $mail->addReplyTo($senderEmail, $name);
        $mail->Subject = $copy['contact_admin_subject'];
        $adminTemplate = MailTemplate::build($mail, [
            'lang' => $lang,
            'preheader' => $copy['contact_admin_preheader'],
            'eyebrow' => $copy['contact_admin_eyebrow'],
            'title' => $copy['contact_admin_title'],
            'intro' => $copy['contact_admin_intro'],
            'badge' => $copy['contact_admin_badge'],
            'cards' => [
                [
                    'title' => $copy['contact_admin_coords'],
                    'rows' => $adminRows
                ],
                [
                    'title' => $copy['contact_admin_message_title'],
                    'message' => $message
                ]
            ],
            'closing' => $copy['contact_admin_closing']
        ]);
        $mail->Body = $adminTemplate['html'];
        $mail->AltBody = $adminTemplate['text'];
        $mail->send();
    } catch (Exception $mailError) {
        error_log('Contact Email Error: ' . $mailError->getMessage());
    }

    echo "<div class='alert alert-success' role='alert'>" . htmlspecialchars($copy['contact_success']) . '</div>';
} catch (Exception $e) {
    $lang = $lang ?? detectFormLang();
    $copy = getSendEmailCopy($lang);
    error_log('Sendemail Error: ' . $e->getMessage());
    echo "<div class='alert alert-danger' role='alert'>" .
        htmlspecialchars($copy['generic_error'] . $e->getMessage()) .
        '</div>';
}
