<?php
/**
 * Upload CV Handler
 * Gère le dépôt de CV pour la page Staffing IT
 * - Validation du fichier (type, taille)
 * - Stockage sécurisé du fichier
 * - Insertion en base de données
 * - Envoi d'email de confirmation
 */

require_once __DIR__ . '/app/settings.php';
require_once __DIR__ . '/app/Database.php';

try {
    // Vérifier que c'est une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée");
    }

    // Récupérer et nettoyer les données du formulaire
    $name = isset($_POST['name']) ? trim(preg_replace("/[^\.\-\' a-zA-Z0-9]/", "", $_POST['name'])) : "";
    $email = isset($_POST['email']) ? trim(preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $_POST['email'])) : "";
    $phone = isset($_POST['phone']) ? trim(preg_replace("/[^\+\.\-\(\) 0-9]/", "", $_POST['phone'])) : "";
    $position = isset($_POST['position']) ? trim(preg_replace("/[^\.\-\' a-zA-Z0-9]/", "", $_POST['position'])) : "";
    $message = isset($_POST['message']) ? trim(preg_replace("/(From:|To:|BCC:|CC:|Subject:|Content-Type:)/", "", $_POST['message'])) : "";

    // Validation des champs obligatoires
    if (empty($name) || empty($email) || empty($phone) || empty($position)) {
        throw new Exception("Veuillez remplir tous les champs obligatoires");
    }

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Adresse email invalide");
    }

    // Vérifier qu'un fichier a été uploadé
    if (!isset($_FILES['cv']) || $_FILES['cv']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception("Veuillez joindre votre CV");
    }

    $file = $_FILES['cv'];

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale du formulaire',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier'
        ];
        throw new Exception($uploadErrors[$file['error']] ?? 'Erreur lors de l\'upload du fichier');
    }

    // Vérifier la taille du fichier (max 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxFileSize) {
        throw new Exception("Le fichier CV ne doit pas dépasser 5MB");
    }

    // Vérifier le type MIME
    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        throw new Exception("Format de fichier non autorisé. Formats acceptés: PDF, DOC, DOCX");
    }

    // Vérifier l'extension
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Extension de fichier non autorisée");
    }

    // Créer le dossier de stockage s'il n'existe pas
    $uploadDir = __DIR__ . '/uploads/cvs/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Impossible de créer le dossier de stockage");
        }
    }

    // Protéger le dossier avec .htaccess
    $htaccessFile = $uploadDir . '.htaccess';
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "deny from all");
    }

    // Générer un nom de fichier unique et sécurisé
    $filename = uniqid('cv_', true) . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $filename;

    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception("Erreur lors de l'enregistrement du fichier");
    }

    // Récupérer l'adresse IP et User Agent
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Insérer dans la base de données
    $db = Database::getInstance();

    $data = [
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
    ];

    $cvId = $db->insert('cv_submissions', $data);

    // Envoyer un email de confirmation au candidat (ne pas bloquer si ça échoue)
    try {
    $mail->clearAddresses();
    $mail->addAddress($email, $name);
    $mail->Subject = 'CV reçu - MHTECH Consulting';
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1a5f7a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>MHTECH Consulting</h1>
            </div>
            <div class='content'>
                <h2>Bonjour {$name},</h2>
                <p>Nous avons bien reçu votre CV pour le poste de <strong>{$position}</strong>.</p>
                <p>Notre équipe de recrutement va l'examiner et vous recontactera rapidement si votre profil correspond à nos besoins actuels.</p>
                <p><strong>Récapitulatif de votre candidature:</strong></p>
                <ul>
                    <li><strong>Nom:</strong> {$name}</li>
                    <li><strong>Email:</strong> {$email}</li>
                    <li><strong>Téléphone:</strong> {$phone}</li>
                    <li><strong>Poste recherché:</strong> {$position}</li>
                    <li><strong>Fichier CV:</strong> {$file['name']}</li>
                </ul>
                <p>Merci pour votre intérêt pour MHTECH Consulting.</p>
            </div>
            <div class='footer'>
                <p>© 2025 MHTECH Consulting. Tous droits réservés.</p>
                <p>+1 (248) 938 1944 | contact@mhtechconsulting.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    $mail->AltBody = "Bonjour {$name}, nous avons bien reçu votre CV pour le poste de {$position}. Notre équipe vous recontactera rapidement.";
    $mail->send();

    // Envoyer un email à l'administrateur avec le CV en pièce jointe
    $mail->clearAddresses();
    $mail->addAddress(Env::get('ADMIN_EMAIL'));
    $mail->addAttachment($filePath, $file['name']);
    $mail->Subject = 'Nouveau CV reçu - ' . $position;
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1a5f7a; color: white; padding: 20px; }
            .info { background-color: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #1a5f7a; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nouveau CV Reçu</h2>
            </div>
            <div class='info'>
                <p><strong>Candidat:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Téléphone:</strong> {$phone}</p>
                <p><strong>Poste recherché:</strong> {$position}</p>
                <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
                <p><strong>Fichier:</strong> {$file['name']} (" . round($file['size'] / 1024, 2) . " KB)</p>
                <p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>
                <p><strong>ID:</strong> #{$cvId}</p>
            </div>
        </div>
    </body>
    </html>
    ";
    $mail->AltBody = "Nouveau CV reçu de {$name} pour le poste de {$position}. Email: {$email}, Téléphone: {$phone}";
    $mail->send();
    } catch (Exception $mailError) {
        // Log l'erreur email mais continue quand même
        error_log("Email Error (upload-cv): " . $mailError->getMessage());
    }

    // Réponse succès
    echo "<div class='alert alert-success' role='alert'>
        Votre CV a été envoyé avec succès ! Nous vous contacterons rapidement.
    </div>";

} catch (Exception $e) {
    // Log l'erreur
    error_log("Upload CV Error: " . $e->getMessage());

    // Réponse erreur
    echo "<div class='alert alert-danger' role='alert'>
        Erreur: " . htmlspecialchars($e->getMessage()) . "
    </div>";
}
