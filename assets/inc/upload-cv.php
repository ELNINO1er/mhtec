<?php
require_once __DIR__ . '/app/settings.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/MailTemplate.php';
require_once __DIR__ . '/app/RequestGuard.php';

function detectUploadCvLang(): string
{
    $postedLang = strtolower(trim((string) ($_POST['lang'] ?? '')));
    if (in_array($postedLang, ['fr', 'en'], true)) {
        return $postedLang;
    }

    $acceptLanguage = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    return strpos($acceptLanguage, 'fr') === 0 ? 'fr' : 'en';
}

function getUploadCvCopy(string $lang): array
{
    if ($lang === 'fr') {
        return [
            'method_not_allowed' => 'Methode non autorisee',
            'required' => 'Veuillez remplir tous les champs obligatoires',
            'invalid_email' => 'Adresse email invalide',
            'missing_cv' => 'Veuillez joindre votre CV',
            'error_ini_size' => 'Le fichier depasse la taille maximale autorisee',
            'error_form_size' => 'Le fichier depasse la taille maximale du formulaire',
            'error_partial' => "Le fichier n'a ete que partiellement telecharge",
            'error_tmp_dir' => 'Dossier temporaire manquant',
            'error_cant_write' => "Echec de l'ecriture du fichier sur le disque",
            'error_extension' => "Une extension PHP a arrete l'upload du fichier",
            'error_upload' => "Erreur lors de l'upload du fichier",
            'error_file_size' => 'Le fichier CV ne doit pas depasser 5 MB',
            'error_invalid_mime' => 'Format de fichier non autorise. Formats acceptes : PDF, DOC, DOCX',
            'error_invalid_extension' => 'Extension de fichier non autorisee',
            'error_create_dir' => 'Impossible de creer le dossier de stockage',
            'error_save_file' => "Erreur lors de l'enregistrement du fichier",
            'submission_blocked' => 'La candidature n a pas pu etre verifiee. Merci de recharger la page et de reessayer.',
            'rate_limited' => 'Trop de depots ont ete detectes. Merci de patienter avant de reessayer.',
            'user_subject' => 'Candidature recue - MHTECH Consulting',
            'user_preheader' => 'Votre CV a bien ete recu par MHTECH Consulting.',
            'user_eyebrow' => 'Staffing IT',
            'user_title' => 'Votre candidature est bien enregistree',
            'user_intro_prefix' => 'Bonjour ',
            'user_intro_middle' => ', nous avons bien recu votre CV pour le poste de ',
            'user_intro_suffix' => ". Notre equipe recrutement va l'etudier rapidement.",
            'user_badge' => 'CV recu',
            'user_summary' => 'Recapitulatif de votre candidature',
            'user_closing' => "Merci pour votre interet envers MHTECH Consulting.\nNous reviendrons vers vous si votre profil correspond a un besoin actif.",
            'admin_subject_prefix' => 'Nouveau CV recu - ',
            'admin_preheader' => 'Un nouveau CV vient d etre recu sur la page Staffing.',
            'admin_eyebrow' => 'Alerte recrutement',
            'admin_title' => 'Nouveau CV recu',
            'admin_intro' => 'Une nouvelle candidature a ete enregistree et le CV original est joint a cet email.',
            'admin_badge' => 'Action RH',
            'admin_candidate' => 'Candidat',
            'admin_attachment' => 'Piece jointe et suivi',
            'admin_message' => 'Message du candidat',
            'admin_closing' => 'Le dossier a ete enregistre en base de donnees et le CV est attache a cet email.',
            'label_name' => 'Nom',
            'label_email' => 'Email',
            'label_phone' => 'Telephone',
            'label_position' => 'Poste vise',
            'label_file' => 'Fichier transmis',
            'label_reference' => 'Reference',
            'label_attached_file' => 'Fichier joint',
            'label_size' => 'Taille',
            'label_date' => 'Date',
            'label_db_id' => 'ID en base',
            'empty_message' => 'Aucun message complementaire.',
            'step_1' => 'Analyse de votre CV par notre equipe.',
            'step_2' => "Verification de l'adequation avec les besoins en cours.",
            'step_3' => 'Prise de contact si votre profil correspond a une opportunite.',
            'success' => 'Votre CV a ete envoye avec succes ! Nous vous contacterons rapidement.',
            'error_prefix' => 'Erreur: '
        ];
    }

    return [
        'method_not_allowed' => 'Method not allowed',
        'required' => 'Please fill in all required fields',
        'invalid_email' => 'Invalid email address',
        'missing_cv' => 'Please attach your resume',
        'error_ini_size' => 'The file exceeds the maximum allowed size',
        'error_form_size' => 'The file exceeds the maximum form size',
        'error_partial' => 'The file was only partially uploaded',
        'error_tmp_dir' => 'Temporary folder is missing',
        'error_cant_write' => 'Failed to write the file to disk',
        'error_extension' => 'A PHP extension stopped the file upload',
        'error_upload' => 'An error occurred during file upload',
        'error_file_size' => 'The resume file must not exceed 5 MB',
        'error_invalid_mime' => 'Invalid file format. Accepted formats: PDF, DOC, DOCX',
        'error_invalid_extension' => 'Invalid file extension',
        'error_create_dir' => 'Unable to create the storage directory',
        'error_save_file' => 'Error while saving the file',
        'submission_blocked' => 'The application could not be verified. Please reload the page and try again.',
        'rate_limited' => 'Too many uploads were detected. Please wait before trying again.',
        'user_subject' => 'Application received - MHTECH Consulting',
        'user_preheader' => 'Your resume has been received by MHTECH Consulting.',
        'user_eyebrow' => 'IT Staffing',
        'user_title' => 'Your application has been recorded',
        'user_intro_prefix' => 'Hello ',
        'user_intro_middle' => ', we have received your resume for the ',
        'user_intro_suffix' => ' role. Our recruitment team will review it shortly.',
        'user_badge' => 'Resume received',
        'user_summary' => 'Application summary',
        'user_closing' => "Thank you for your interest in MHTECH Consulting.\nWe will get back to you if your profile matches an active need.",
        'admin_subject_prefix' => 'New resume received - ',
        'admin_preheader' => 'A new resume has been received from the Staffing page.',
        'admin_eyebrow' => 'Recruitment alert',
        'admin_title' => 'New resume received',
        'admin_intro' => 'A new application has been recorded and the original resume is attached to this email.',
        'admin_badge' => 'HR action',
        'admin_candidate' => 'Candidate',
        'admin_attachment' => 'Attachment and follow-up',
        'admin_message' => 'Candidate message',
        'admin_closing' => 'The file has been stored in the database and the resume is attached to this email.',
        'label_name' => 'Name',
        'label_email' => 'Email',
        'label_phone' => 'Phone',
        'label_position' => 'Target role',
        'label_file' => 'Uploaded file',
        'label_reference' => 'Reference',
        'label_attached_file' => 'Attached file',
        'label_size' => 'Size',
        'label_date' => 'Date',
        'label_db_id' => 'Database ID',
        'empty_message' => 'No additional message.',
        'step_1' => 'Review of your resume by our team.',
        'step_2' => 'Check against current open requirements.',
        'step_3' => 'Follow-up if your profile matches an opportunity.',
        'success' => 'Your resume has been sent successfully. We will contact you soon.',
        'error_prefix' => 'Error: '
    ];
}

try {
    $lang = detectUploadCvLang();
    $copy = getUploadCvCopy($lang);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception($copy['method_not_allowed']);
    }

    if (RequestGuard::isHoneypotTriggered($_POST) || !RequestGuard::hasValidSubmissionTiming($_POST, 2, 43200)) {
        throw new Exception($copy['submission_blocked']);
    }

    if (RequestGuard::isRateLimited('upload-cv', 4, 900)) {
        throw new Exception($copy['rate_limited']);
    }

    $name = isset($_POST['name']) ? trim((string) preg_replace("/[^.\-' a-zA-Z0-9]/", '', (string) $_POST['name'])) : '';
    $email = isset($_POST['email']) ? trim((string) preg_replace("/[^.\-_@a-zA-Z0-9]/", '', (string) $_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? trim((string) preg_replace("/[^+.\-() 0-9]/", '', (string) $_POST['phone'])) : '';
    $position = isset($_POST['position']) ? trim((string) preg_replace("/[^.\-' a-zA-Z0-9]/", '', (string) $_POST['position'])) : '';
    $message = isset($_POST['message']) ? trim((string) preg_replace("/(From:|To:|BCC:|CC:|Subject:|Content-Type:)/i", '', (string) $_POST['message'])) : '';
    $adminEmail = trim((string) Env::get('ADMIN_EMAIL', 'contact@mhtechconsulting.com'));

    if ($adminEmail === '' || stripos($adminEmail, 'scriptfusions') !== false || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $adminEmail = 'contact@mhtechconsulting.com';
    }

    if ($name === '' || $email === '' || $phone === '' || $position === '') {
        throw new Exception($copy['required']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception($copy['invalid_email']);
    }

    if (!isset($_FILES['cv']) || $_FILES['cv']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception($copy['missing_cv']);
    }

    $file = $_FILES['cv'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => $copy['error_ini_size'],
            UPLOAD_ERR_FORM_SIZE => $copy['error_form_size'],
            UPLOAD_ERR_PARTIAL => $copy['error_partial'],
            UPLOAD_ERR_NO_TMP_DIR => $copy['error_tmp_dir'],
            UPLOAD_ERR_CANT_WRITE => $copy['error_cant_write'],
            UPLOAD_ERR_EXTENSION => $copy['error_extension']
        ];
        throw new Exception($uploadErrors[$file['error']] ?? $copy['error_upload']);
    }

    $maxFileSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxFileSize) {
        throw new Exception($copy['error_file_size']);
    }

    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes, true)) {
        throw new Exception($copy['error_invalid_mime']);
    }

    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions, true)) {
        throw new Exception($copy['error_invalid_extension']);
    }

    $uploadDir = __DIR__ . '/uploads/cvs/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception($copy['error_create_dir']);
    }

    $htaccessFile = $uploadDir . '.htaccess';
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "deny from all");
    }

    $filename = uniqid('cv_', true) . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception($copy['error_save_file']);
    }

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $db = Database::getInstance();
    $cvId = $db->insert('cv_submissions', [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'position' => $position,
        'cv_filename' => $filename,
        'cv_original_name' => $file['name'],
        'cv_file_size' => $file['size'],
        'cv_mime_type' => $mimeType,
        'message' => $message,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'status' => 'new'
    ]);

    try {
        MailTemplate::resetMailer($mail, $lang);
        $mail->addAddress($email, $name);
        $mail->Subject = $copy['user_subject'];
        $userTemplate = MailTemplate::build($mail, [
            'lang' => $lang,
            'preheader' => $copy['user_preheader'],
            'eyebrow' => $copy['user_eyebrow'],
            'title' => $copy['user_title'],
            'intro' => $copy['user_intro_prefix'] . $name . $copy['user_intro_middle'] . $position . $copy['user_intro_suffix'],
            'badge' => $copy['user_badge'],
            'cards' => [
                [
                    'title' => $copy['user_summary'],
                    'rows' => [
                        ['label' => $copy['label_name'], 'value' => $name],
                        ['label' => $copy['label_email'], 'value' => $email],
                        ['label' => $copy['label_phone'], 'value' => $phone],
                        ['label' => $copy['label_position'], 'value' => $position],
                        ['label' => $copy['label_file'], 'value' => $file['name']],
                        ['label' => $copy['label_reference'], 'value' => '#' . $cvId]
                    ]
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
        $mail->addReplyTo($email, $name);
        $mail->addAttachment($filePath, $file['name']);
        $mail->Subject = $copy['admin_subject_prefix'] . $position;
        $adminTemplate = MailTemplate::build($mail, [
            'lang' => $lang,
            'preheader' => $copy['admin_preheader'],
            'eyebrow' => $copy['admin_eyebrow'],
            'title' => $copy['admin_title'],
            'intro' => $copy['admin_intro'],
            'badge' => $copy['admin_badge'],
            'cards' => [
                [
                    'title' => $copy['admin_candidate'],
                    'rows' => [
                        ['label' => $copy['label_name'], 'value' => $name],
                        ['label' => $copy['label_email'], 'value' => $email, 'href' => 'mailto:' . $email],
                        ['label' => $copy['label_phone'], 'value' => $phone, 'href' => 'tel:' . preg_replace('/\s+/', '', $phone)],
                        ['label' => $copy['label_position'], 'value' => $position]
                    ]
                ],
                [
                    'title' => $copy['admin_attachment'],
                    'rows' => [
                        ['label' => $copy['label_attached_file'], 'value' => $file['name']],
                        ['label' => $copy['label_size'], 'value' => round($file['size'] / 1024, 2) . ' KB'],
                        ['label' => $copy['label_date'], 'value' => date('Y-m-d H:i:s')],
                        ['label' => $copy['label_db_id'], 'value' => '#' . $cvId]
                    ]
                ],
                [
                    'title' => $copy['admin_message'],
                    'message' => $message !== '' ? $message : $copy['empty_message']
                ]
            ],
            'closing' => $copy['admin_closing']
        ]);
        $mail->Body = $adminTemplate['html'];
        $mail->AltBody = $adminTemplate['text'];
        $mail->send();
    } catch (Exception $mailError) {
        error_log('Email Error (upload-cv): ' . $mailError->getMessage());
    }

    echo "<div class='alert alert-success' role='alert'>" . htmlspecialchars($copy['success']) . '</div>';
} catch (Exception $e) {
    $lang = $lang ?? detectUploadCvLang();
    $copy = getUploadCvCopy($lang);
    error_log('Upload CV Error: ' . $e->getMessage());
    echo "<div class='alert alert-danger' role='alert'>" .
        htmlspecialchars($copy['error_prefix'] . $e->getMessage()) .
        '</div>';
}
