<?php
require_once __DIR__ . '/app/settings.php';
require_once __DIR__ . '/app/Database.php';

try {

	//Content
	$name = isset($_POST['name']) ? preg_replace("/[^\.\-\' a-zA-Z0-9]/", "", $_POST['name']) : "";
	$senderEmail = isset($_POST['email']) ? preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $_POST['email']) : "";
	$phone = isset($_POST['phone']) ? preg_replace("/[^\+\.\-\(\) 0-9]/", "", $_POST['phone']) : "";
	$services = isset($_POST['services']) ? preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $_POST['services']) : "";
	$subject = isset($_POST['subject']) ? preg_replace("/[^\.\-\_\@a-zA-Z0-9\s]/", "", $_POST['subject']) : "";
	$address = isset($_POST['address']) ? preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $_POST['address']) : "";
	$website = isset($_POST['website']) ? preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $_POST['website']) : "";
	$message = isset($_POST['message']) ? preg_replace("/(From:|To:|BCC:|CC:|Subject:|Content-Type:)/", "", $_POST['message']) : "";
	$requestType = isset($_POST['request_type']) ? preg_replace("/[^a-zA-Z0-9_]/", "", $_POST['request_type']) : "";

	// Récupérer IP et User Agent
	$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
	$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

	// Déterminer la source du formulaire
	$source = 'unknown';
	if (!empty($name) && !empty($message)) {
		if (!empty($requestType)) {
			$source = 'contact_page';
		} elseif (!empty($subject)) {
			$source = 'staffing_page';
		} else {
			$source = 'chat_popup';
		}
	}

	if (!empty($senderEmail)) {
		if (empty($name) || empty($message)) {
			// ========================================
			// NEWSLETTER SUBSCRIPTION
			// ========================================

			// 1. ENREGISTRER EN BASE D'ABORD
			try {
				$db = Database::getInstance();
				$db->insert('newsletter_subscriptions', [
					'email' => $senderEmail,
					'source' => empty($source) ? 'newsletter' : $source,
					'ip_address' => $ipAddress
				]);
			} catch (Exception $dbError) {
				// Si l'email existe déjà (UNIQUE constraint), ignorer l'erreur
				error_log("Newsletter DB Error: " . $dbError->getMessage());
			}

			// 2. ENVOYER LES EMAILS (ne pas bloquer si ça échoue)
			try {
				// Load the HTML template and replace placeholders
				$template = file_get_contents('template/newsletter.html');
				// To mail
				$mail->addAddress($senderEmail);
				// Content
				$mail->Subject = 'Newsletter Subscription';
				$mail->Body = $template;
				$mail->AltBody = 'Thanks for subscribing to our newsletter.';
				$mail->send();

				// Send to admin
				$mail->clearAddresses(); // Clear previous addresses
				$template = file_get_contents('template/admin-newsletter.html');
				$template = str_replace('{{email}}', $senderEmail, $template);

				$mail->addAddress(Env::get('ADMIN_EMAIL')); // Add admin email
				$mail->Subject = 'New Subscriber Added';
				$mail->Body = $template;
				$mail->AltBody = 'New Newsletter Subscription';
				$mail->send();
			} catch (Exception $mailError) {
				// Log l'erreur mais continue
				error_log("Newsletter Email Error: " . $mailError->getMessage());
			}

			echo "<div class='alert alert-success' role='alert'>
				Merci pour votre abonnement ! Vous recevrez nos actualités prochainement.
			</div>";

		} else {
			// ========================================
			// CONTACT FORM
			// ========================================

			// 1. ENREGISTRER EN BASE D'ABORD
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

				// Supprimer les clés vides
				$contactData = array_filter($contactData, function($value) {
					return !empty($value);
				});

				$contactId = $db->insert('contacts', $contactData);

				// Log l'activité
				$db->insert('activity_logs', [
					'table_name' => 'contacts',
					'record_id' => $contactId,
					'action' => 'insert',
					'ip_address' => $ipAddress,
					'user_agent' => $userAgent
				]);

			} catch (Exception $dbError) {
				error_log("Contact DB Error: " . $dbError->getMessage());
				throw new Exception("Erreur lors de l'enregistrement de votre demande");
			}

			// 2. ENVOYER LES EMAILS (ne pas bloquer si ça échoue)
			try {
				// sent user email
				$template = file_get_contents('template/user-template.html');
				$template = str_replace('{{name}}', $name, $template);
				$template = str_replace('{{message}}', $message, $template);

				$mail->addAddress($senderEmail, $name);
				$mail->Subject = 'Thanks for contacting us.';
				$mail->Body = $template;
				$mail->AltBody = 'We have received your message and will get back to you as soon as possible';
				$mail->send();

				// Send to admin
				$mail->clearAddresses(); // Clear previous addresses
				$template = file_get_contents('template/admin-template.html');
				$template = str_replace('{{name}}', $name, $template);
				$template = str_replace('{{message}}', $message, $template);
				$template = str_replace('{{email}}', $senderEmail, $template);

				if (!empty($phone)) {
					$template = str_replace('{{phone}}', $phone, $template);
					$template = str_replace('class="phone-hide"', 'class=""', $template);
				}

				if (!empty($subject)) {
					$template = str_replace('{{subject}}', $subject, $template);
					$template = str_replace('class="subject-hide"', 'class=""', $template);
				}

				$mail->addAddress(Env::get('ADMIN_EMAIL')); // Add admin email
				$mail->Subject = 'New Contact Form Submission';
				$mail->Body = $template;
				$mail->AltBody = 'A new contact form submission has been received.';
				$mail->send();

			} catch (Exception $mailError) {
				// Log l'erreur mais continue
				error_log("Contact Email Error: " . $mailError->getMessage());
			}

			echo "<div class='alert alert-success' role='alert'>
				Merci de nous avoir contacté ! Nous vous répondrons dans les plus brefs délais.
			</div>";

		}

	} else {
		echo "<div class='alert alert-danger' role='alert'>
			Veuillez remplir tous les champs obligatoires.
		</div>";
	}

} catch (Exception $e) {
	error_log("Sendemail Error: " . $e->getMessage());
	echo "<div class='alert alert-danger' role='alert'>
		Une erreur est survenue : " . htmlspecialchars($e->getMessage()) . "
	</div>";
}
