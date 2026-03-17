<?php
/**
 * Recruitment Request Handler
 * Gère les demandes de recrutement pour la page Staffing IT
 * - Validation des données
 * - Insertion en base de données
 * - Envoi d'emails de confirmation
 */

require_once __DIR__ . '/app/settings.php';
require_once __DIR__ . '/app/Database.php';

try {
    // Vérifier que c'est une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée");
    }

    // Récupérer et nettoyer les données du formulaire
    $company = isset($_POST['company']) ? trim(preg_replace("/[^\.\-\' a-zA-Z0-9]/", "", $_POST['company'])) : "";
    $contactName = isset($_POST['contact_name']) ? trim(preg_replace("/[^\.\-\' a-zA-Z0-9]/", "", $_POST['contact_name'])) : "";
    $email = isset($_POST['email']) ? trim(preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $_POST['email'])) : "";
    $phone = isset($_POST['phone']) ? trim(preg_replace("/[^\+\.\-\(\) 0-9]/", "", $_POST['phone'])) : "";
    $profile = isset($_POST['profile']) ? trim(preg_replace("/[^\.\-\,\' a-zA-Z0-9]/", "", $_POST['profile'])) : "";
    $duration = isset($_POST['duration']) ? trim($_POST['duration']) : "";
    $message = isset($_POST['message']) ? trim(preg_replace("/(From:|To:|BCC:|CC:|Subject:|Content-Type:)/", "", $_POST['message'])) : "";

    // Validation des champs obligatoires
    if (empty($company) || empty($contactName) || empty($email) || empty($phone) || empty($profile) || empty($duration) || empty($message)) {
        throw new Exception("Veuillez remplir tous les champs obligatoires");
    }

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Adresse email invalide");
    }

    // Validation de la durée
    $allowedDurations = ['1-3', '3-6', '6-12', '12+', 'permanent'];
    if (!in_array($duration, $allowedDurations)) {
        throw new Exception("Durée de mission invalide");
    }

    // Récupérer l'adresse IP et User Agent
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Insérer dans la base de données
    $db = Database::getInstance();

    $data = [
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
    ];

    $requestId = $db->insert('recruitment_requests', $data);

    // Mapper les durées pour l'affichage
    $durationLabels = [
        '1-3' => '1-3 mois',
        '3-6' => '3-6 mois',
        '6-12' => '6-12 mois',
        '12+' => '12+ mois',
        'permanent' => 'CDI (Contrat permanent)'
    ];
    $durationLabel = $durationLabels[$duration] ?? $duration;

    // Envoyer un email de confirmation à l'entreprise (ne pas bloquer si ça échoue)
    try {
    $mail->clearAddresses();
    $mail->addAddress($email, $contactName);
    $mail->Subject = 'Demande de recrutement reçue - MHTECH Consulting';
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1a5f7a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            .highlight { background-color: #e8f4f8; padding: 15px; margin: 15px 0; border-left: 4px solid #1a5f7a; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>MHTECH Consulting</h1>
                <p>Staffing IT</p>
            </div>
            <div class='content'>
                <h2>Bonjour {$contactName},</h2>
                <p>Nous avons bien reçu votre demande de recrutement pour <strong>{$company}</strong>.</p>
                <p>Notre équipe de recrutement va analyser votre besoin et vous proposera des profils qualifiés dans les plus brefs délais.</p>

                <div class='highlight'>
                    <h3>Récapitulatif de votre demande:</h3>
                    <ul>
                        <li><strong>Entreprise:</strong> {$company}</li>
                        <li><strong>Contact:</strong> {$contactName}</li>
                        <li><strong>Email:</strong> {$email}</li>
                        <li><strong>Téléphone:</strong> {$phone}</li>
                        <li><strong>Profil recherché:</strong> {$profile}</li>
                        <li><strong>Durée de la mission:</strong> {$durationLabel}</li>
                        <li><strong>Numéro de demande:</strong> #{$requestId}</li>
                    </ul>
                </div>

                <h3>Prochaines étapes:</h3>
                <ol>
                    <li>Analyse de votre besoin (24-48h)</li>
                    <li>Sélection de profils correspondants</li>
                    <li>Présentation des candidats qualifiés</li>
                    <li>Organisation des entretiens</li>
                </ol>

                <p>Un consultant vous contactera dans les 48 heures pour discuter de votre besoin en détail.</p>

                <p>Merci de votre confiance en MHTECH Consulting.</p>
            </div>
            <div class='footer'>
                <p>© 2025 MHTECH Consulting. Tous droits réservés.</p>
                <p>+1 (248) 938 1944 | contact@mhtechconsulting.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    $mail->AltBody = "Bonjour {$contactName}, nous avons bien reçu votre demande de recrutement pour {$profile}. Notre équipe vous contactera dans les 48 heures. Numéro de demande: #{$requestId}";
    $mail->send();

    // Envoyer un email à l'administrateur
    $mail->clearAddresses();
    $mail->addAddress(Env::get('ADMIN_EMAIL'));
    $mail->Subject = 'Nouvelle demande de recrutement - ' . $company;
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1a5f7a; color: white; padding: 20px; }
            .info { background-color: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #1a5f7a; }
            .urgent { background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🔔 Nouvelle Demande de Recrutement</h2>
            </div>

            <div class='urgent'>
                <strong>⚡ Action requise:</strong> Contacter le client dans les 48 heures
            </div>

            <div class='info'>
                <h3>Informations Client</h3>
                <p><strong>Entreprise:</strong> {$company}</p>
                <p><strong>Contact:</strong> {$contactName}</p>
                <p><strong>Email:</strong> <a href='mailto:{$email}'>{$email}</a></p>
                <p><strong>Téléphone:</strong> <a href='tel:{$phone}'>{$phone}</a></p>
            </div>

            <div class='info'>
                <h3>Détails de la Mission</h3>
                <p><strong>Profil recherché:</strong> {$profile}</p>
                <p><strong>Durée:</strong> {$durationLabel}</p>
                <p><strong>Description du besoin:</strong></p>
                <p style='background: white; padding: 10px; border-radius: 5px;'>" . nl2br(htmlspecialchars($message)) . "</p>
            </div>

            <div class='info'>
                <h3>Informations Système</h3>
                <p><strong>ID de la demande:</strong> #{$requestId}</p>
                <p><strong>Date:</strong> " . date('d/m/Y à H:i:s') . "</p>
                <p><strong>IP:</strong> {$ipAddress}</p>
                <p><strong>Statut:</strong> <span style='color: #28a745; font-weight: bold;'>NOUVEAU</span></p>
            </div>

            <div style='text-align: center; margin-top: 30px;'>
                <p style='color: #666; font-size: 14px;'>
                    Cette demande a été automatiquement enregistrée dans la base de données.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    $mail->AltBody = "Nouvelle demande de recrutement de {$company}. Contact: {$contactName} ({$email}, {$phone}). Profil: {$profile}. Durée: {$durationLabel}. ID: #{$requestId}";
    $mail->send();
    } catch (Exception $mailError) {
        // Log l'erreur email mais continue quand même
        error_log("Email Error (recruitment-request): " . $mailError->getMessage());
    }

    // Log l'activité
    $db->insert('activity_logs', [
        'table_name' => 'recruitment_requests',
        'record_id' => $requestId,
        'action' => 'insert',
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent
    ]);

    // Réponse succès
    echo "<div class='alert alert-success' role='alert'>
        <strong>Demande envoyée avec succès !</strong><br>
        Votre numéro de demande est <strong>#{$requestId}</strong>.<br>
        Nous vous contacterons dans les 48 heures.
    </div>";

} catch (Exception $e) {
    // Log l'erreur
    error_log("Recruitment Request Error: " . $e->getMessage());

    // Réponse erreur
    echo "<div class='alert alert-danger' role='alert'>
        <strong>Erreur:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}
