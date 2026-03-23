<?php
require_once __DIR__ . '/app/settings.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/MailTemplate.php';

function detectRecruitmentLang(): string
{
    $postedLang = strtolower(trim((string) ($_POST['lang'] ?? '')));
    if (in_array($postedLang, ['fr', 'en'], true)) {
        return $postedLang;
    }

    $acceptLanguage = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    return strpos($acceptLanguage, 'fr') === 0 ? 'fr' : 'en';
}

function getRecruitmentCopy(string $lang): array
{
    if ($lang === 'fr') {
        return [
            'method_not_allowed' => 'Methode non autorisee',
            'required' => 'Veuillez remplir tous les champs obligatoires',
            'invalid_email' => 'Adresse email invalide',
            'invalid_duration' => 'Duree de mission invalide',
            'user_subject' => 'Demande de recrutement recue - MHTECH Consulting',
            'user_preheader' => 'Votre demande de recrutement a bien ete recue par MHTECH Consulting.',
            'user_eyebrow' => 'Staffing IT',
            'user_title' => 'Votre demande est bien enregistree',
            'user_intro_prefix' => 'Bonjour ',
            'user_intro_suffix' => ', merci pour votre confiance. Notre equipe analyse votre besoin et reviendra vers vous rapidement avec une reponse adaptee.',
            'user_badge' => 'Demande RH recue',
            'user_summary' => 'Recapitulatif de votre besoin',
            'user_need_title' => 'Description du besoin',
            'user_closing' => 'Un consultant MHTECH Consulting reviendra vers vous dans les plus brefs delais.',
            'admin_subject_prefix' => 'Nouvelle demande de recrutement - ',
            'admin_preheader' => 'Une nouvelle demande de recrutement attend votre traitement.',
            'admin_eyebrow' => 'Alerte recrutement',
            'admin_title' => 'Nouvelle demande de recrutement',
            'admin_intro' => 'Une nouvelle entreprise a soumis un besoin en staffing IT. Repondez directement a ce message pour contacter le demandeur.',
            'admin_badge' => 'Suivi commercial',
            'admin_company' => 'Entreprise et contact',
            'admin_mission' => 'Mission demandee',
            'admin_need_title' => 'Description du besoin',
            'admin_closing' => 'La demande a ete enregistree en base de donnees et attend maintenant un suivi commercial.',
            'label_company' => 'Entreprise',
            'label_contact' => 'Contact',
            'label_email' => 'Email',
            'label_phone' => 'Telephone',
            'label_profile' => 'Profil recherche',
            'label_duration' => 'Duree de mission',
            'label_reference' => 'Reference',
            'label_db_id' => 'ID en base',
            'label_date' => 'Date',
            'label_ip' => 'IP',
            'not_available' => 'Non disponible',
            'duration_1_3' => '1 a 3 mois',
            'duration_3_6' => '3 a 6 mois',
            'duration_6_12' => '6 a 12 mois',
            'duration_12_plus' => '12 mois et plus',
            'duration_permanent' => 'CDI',
            'step_1' => 'Qualification du besoin par notre equipe.',
            'step_2' => 'Selection de profils adaptes a votre contexte.',
            'step_3' => 'Prise de contact pour organiser la suite du process.',
            'success_html' => "<strong>Demande envoyee avec succes !</strong><br>Votre numero de demande est <strong>#%s</strong>.<br>Nous vous contacterons dans les 48 heures.",
            'error_prefix_html' => '<strong>Erreur:</strong> '
        ];
    }

    return [
        'method_not_allowed' => 'Method not allowed',
        'required' => 'Please fill in all required fields',
        'invalid_email' => 'Invalid email address',
        'invalid_duration' => 'Invalid engagement duration',
        'user_subject' => 'Recruitment request received - MHTECH Consulting',
        'user_preheader' => 'Your recruitment request has been received by MHTECH Consulting.',
        'user_eyebrow' => 'IT Staffing',
        'user_title' => 'Your request has been recorded',
        'user_intro_prefix' => 'Hello ',
        'user_intro_suffix' => ', thank you for your trust. Our team is reviewing your need and will get back to you quickly with an adapted response.',
        'user_badge' => 'Staffing request received',
        'user_summary' => 'Summary of your need',
        'user_need_title' => 'Need description',
        'user_closing' => 'An MHTECH Consulting consultant will get back to you shortly.',
        'admin_subject_prefix' => 'New recruitment request - ',
        'admin_preheader' => 'A new recruitment request is waiting for review.',
        'admin_eyebrow' => 'Recruitment alert',
        'admin_title' => 'New recruitment request',
        'admin_intro' => 'A new company has submitted an IT staffing need. Reply directly to this message to contact the requester.',
        'admin_badge' => 'Sales follow-up',
        'admin_company' => 'Company and contact',
        'admin_mission' => 'Requested engagement',
        'admin_need_title' => 'Need description',
        'admin_closing' => 'The request has been stored in the database and is now waiting for commercial follow-up.',
        'label_company' => 'Company',
        'label_contact' => 'Contact',
        'label_email' => 'Email',
        'label_phone' => 'Phone',
        'label_profile' => 'Requested profile',
        'label_duration' => 'Engagement duration',
        'label_reference' => 'Reference',
        'label_db_id' => 'Database ID',
        'label_date' => 'Date',
        'label_ip' => 'IP',
        'not_available' => 'Not available',
        'duration_1_3' => '1 to 3 months',
        'duration_3_6' => '3 to 6 months',
        'duration_6_12' => '6 to 12 months',
        'duration_12_plus' => '12 months and more',
        'duration_permanent' => 'Permanent hire',
        'step_1' => 'Qualification of the need by our team.',
        'step_2' => 'Selection of profiles adapted to your context.',
        'step_3' => 'Follow-up to organize the next steps.',
        'success_html' => '<strong>Request sent successfully.</strong><br>Your request number is <strong>#%s</strong>.<br>We will contact you within 48 hours.',
        'error_prefix_html' => '<strong>Error:</strong> '
    ];
}

function formatRecruitmentDuration(string $lang, string $duration): string
{
    $copy = getRecruitmentCopy($lang);
    $map = [
        '1-3' => $copy['duration_1_3'],
        '3-6' => $copy['duration_3_6'],
        '6-12' => $copy['duration_6_12'],
        '12+' => $copy['duration_12_plus'],
        'permanent' => $copy['duration_permanent']
    ];

    return $map[$duration] ?? $duration;
}

try {
    $lang = detectRecruitmentLang();
    $copy = getRecruitmentCopy($lang);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception($copy['method_not_allowed']);
    }

    $company = isset($_POST['company']) ? trim((string) preg_replace("/[^.\-' a-zA-Z0-9]/", '', (string) $_POST['company'])) : '';
    $contactName = isset($_POST['contact_name']) ? trim((string) preg_replace("/[^.\-' a-zA-Z0-9]/", '', (string) $_POST['contact_name'])) : '';
    $email = isset($_POST['email']) ? trim((string) preg_replace("/[^.\-_@a-zA-Z0-9]/", '', (string) $_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? trim((string) preg_replace("/[^+.\-() 0-9]/", '', (string) $_POST['phone'])) : '';
    $profile = isset($_POST['profile']) ? trim((string) preg_replace("/[^.\-,\' a-zA-Z0-9]/", '', (string) $_POST['profile'])) : '';
    $duration = isset($_POST['duration']) ? trim((string) $_POST['duration']) : '';
    $message = isset($_POST['message']) ? trim((string) preg_replace("/(From:|To:|BCC:|CC:|Subject:|Content-Type:)/i", '', (string) $_POST['message'])) : '';
    $adminEmail = trim((string) Env::get('ADMIN_EMAIL', 'contact@mhtechconsulting.com'));

    if ($adminEmail === '' || stripos($adminEmail, 'scriptfusions') !== false || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $adminEmail = 'contact@mhtechconsulting.com';
    }

    if ($company === '' || $contactName === '' || $email === '' || $phone === '' || $profile === '' || $duration === '' || $message === '') {
        throw new Exception($copy['required']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception($copy['invalid_email']);
    }

    $allowedDurations = ['1-3', '3-6', '6-12', '12+', 'permanent'];
    if (!in_array($duration, $allowedDurations, true)) {
        throw new Exception($copy['invalid_duration']);
    }

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $db = Database::getInstance();
    $requestId = $db->insert('recruitment_requests', [
        'company' => $company,
        'contact_name' => $contactName,
        'email' => $email,
        'phone' => $phone,
        'profile' => $profile,
        'duration' => $duration,
        'message' => $message,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'status' => 'new'
    ]);

    $durationLabel = formatRecruitmentDuration($lang, $duration);

    try {
        MailTemplate::resetMailer($mail, $lang);
        $mail->addAddress($email, $contactName);
        $mail->Subject = $copy['user_subject'];
        $userTemplate = MailTemplate::build($mail, [
            'lang' => $lang,
            'preheader' => $copy['user_preheader'],
            'eyebrow' => $copy['user_eyebrow'],
            'title' => $copy['user_title'],
            'intro' => $copy['user_intro_prefix'] . $contactName . $copy['user_intro_suffix'],
            'badge' => $copy['user_badge'],
            'cards' => [
                [
                    'title' => $copy['user_summary'],
                    'rows' => [
                        ['label' => $copy['label_company'], 'value' => $company],
                        ['label' => $copy['label_contact'], 'value' => $contactName],
                        ['label' => $copy['label_email'], 'value' => $email],
                        ['label' => $copy['label_phone'], 'value' => $phone],
                        ['label' => $copy['label_profile'], 'value' => $profile],
                        ['label' => $copy['label_duration'], 'value' => $durationLabel],
                        ['label' => $copy['label_reference'], 'value' => '#' . $requestId]
                    ]
                ],
                [
                    'title' => $copy['user_need_title'],
                    'message' => $message
                ]
            ],
            'steps' => [
                $copy['step_1'],
                $copy['step_2'],
                $copy['step_3']
            ],
            'closing' => $copy['user_closing']
        ]);
        $mail->Body = $userTemplate['html'];
        $mail->AltBody = $userTemplate['text'];
        $mail->send();

        MailTemplate::resetMailer($mail, $lang);
        $mail->addAddress($adminEmail);
        $mail->addReplyTo($email, $contactName);
        $mail->Subject = $copy['admin_subject_prefix'] . $company;
        $adminTemplate = MailTemplate::build($mail, [
            'lang' => $lang,
            'preheader' => $copy['admin_preheader'],
            'eyebrow' => $copy['admin_eyebrow'],
            'title' => $copy['admin_title'],
            'intro' => $copy['admin_intro'],
            'badge' => $copy['admin_badge'],
            'cards' => [
                [
                    'title' => $copy['admin_company'],
                    'rows' => [
                        ['label' => $copy['label_company'], 'value' => $company],
                        ['label' => $copy['label_contact'], 'value' => $contactName],
                        ['label' => $copy['label_email'], 'value' => $email, 'href' => 'mailto:' . $email],
                        ['label' => $copy['label_phone'], 'value' => $phone, 'href' => 'tel:' . preg_replace('/\s+/', '', $phone)]
                    ]
                ],
                [
                    'title' => $copy['admin_mission'],
                    'rows' => [
                        ['label' => $copy['label_profile'], 'value' => $profile],
                        ['label' => $copy['label_duration'], 'value' => $durationLabel],
                        ['label' => $copy['label_db_id'], 'value' => '#' . $requestId],
                        ['label' => $copy['label_date'], 'value' => date('Y-m-d H:i:s')],
                        ['label' => $copy['label_ip'], 'value' => (string) ($ipAddress ?? $copy['not_available'])]
                    ]
                ],
                [
                    'title' => $copy['admin_need_title'],
                    'message' => $message
                ]
            ],
            'closing' => $copy['admin_closing']
        ]);
        $mail->Body = $adminTemplate['html'];
        $mail->AltBody = $adminTemplate['text'];
        $mail->send();
    } catch (Exception $mailError) {
        error_log('Email Error (recruitment-request): ' . $mailError->getMessage());
    }

    $db->insert('activity_logs', [
        'table_name' => 'recruitment_requests',
        'record_id' => $requestId,
        'action' => 'insert',
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent
    ]);

    echo "<div class='alert alert-success' role='alert'>" . sprintf($copy['success_html'], $requestId) . '</div>';
} catch (Exception $e) {
    $lang = $lang ?? detectRecruitmentLang();
    $copy = getRecruitmentCopy($lang);
    error_log('Recruitment Request Error: ' . $e->getMessage());
    echo "<div class='alert alert-danger' role='alert'>" .
        $copy['error_prefix_html'] . htmlspecialchars($e->getMessage()) .
        '</div>';
}
