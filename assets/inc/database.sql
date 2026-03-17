-- =====================================================
-- MHTECH Consulting - Database Schema
-- Base de données pour tous les formulaires du site
-- =====================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS mhtech_consulting
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mhtech_consulting;

-- =====================================================
-- Table 1: Contacts généraux (chat, sidebar, contact principal)
-- =====================================================
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    subject VARCHAR(255) NULL,
    request_type VARCHAR(100) NULL COMMENT 'consulting, staffing, candidature, autre',
    message TEXT NOT NULL,
    source VARCHAR(50) NOT NULL COMMENT 'chat_popup, sidebar, contact_page, staffing_page',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    INDEX idx_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table 2: Newsletter subscriptions
-- =====================================================
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    source VARCHAR(50) NULL COMMENT 'contact_page, staffing_page, footer',
    ip_address VARCHAR(45) NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_subscribed_at (subscribed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table 3: CVs déposés (staffing IT - candidats)
-- =====================================================
CREATE TABLE IF NOT EXISTS cv_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    position VARCHAR(255) NOT NULL COMMENT 'Poste recherché',
    cv_filename VARCHAR(255) NOT NULL COMMENT 'Nom du fichier CV',
    cv_original_name VARCHAR(255) NOT NULL COMMENT 'Nom original du fichier',
    cv_file_size INT NOT NULL COMMENT 'Taille en octets',
    cv_mime_type VARCHAR(100) NOT NULL,
    message TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    status ENUM('new', 'reviewed', 'contacted', 'archived') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table 4: Demandes de recrutement (staffing IT - entreprises)
-- =====================================================
CREATE TABLE IF NOT EXISTS recruitment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    profile VARCHAR(255) NOT NULL COMMENT 'Profil recherché',
    duration VARCHAR(50) NOT NULL COMMENT '1-3, 3-6, 6-12, 12+, permanent',
    message TEXT NOT NULL COMMENT 'Description du besoin',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    status ENUM('new', 'in_progress', 'proposal_sent', 'closed', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_company (company),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table 5: Logs d'activité (optionnel - pour audit)
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL COMMENT 'contacts, cv_submissions, etc.',
    record_id INT NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'insert, update, delete',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Vues utiles pour les statistiques
-- =====================================================

-- Vue: Statistiques des contacts par source
CREATE OR REPLACE VIEW v_contacts_stats AS
SELECT
    source,
    COUNT(*) as total_contacts,
    COUNT(DISTINCT email) as unique_emails,
    DATE(created_at) as contact_date
FROM contacts
GROUP BY source, DATE(created_at);

-- Vue: Statistiques CVs par statut
CREATE OR REPLACE VIEW v_cv_stats AS
SELECT
    status,
    COUNT(*) as total_cvs,
    DATE(created_at) as submission_date
FROM cv_submissions
GROUP BY status, DATE(created_at);

-- Vue: Statistiques recrutement par durée
CREATE OR REPLACE VIEW v_recruitment_stats AS
SELECT
    duration,
    status,
    COUNT(*) as total_requests,
    DATE(created_at) as request_date
FROM recruitment_requests
GROUP BY duration, status, DATE(created_at);

-- =====================================================
-- Insertion de données de test (optionnel)
-- =====================================================

-- Exemple de contact
INSERT INTO contacts (name, email, phone, subject, message, source) VALUES
('John Doe', 'john.doe@example.com', '+1 (248) 555-0100', 'Test Contact', 'Ceci est un message de test.', 'contact_page');

-- Exemple d'abonnement newsletter
INSERT INTO newsletter_subscriptions (email, source) VALUES
('newsletter@example.com', 'contact_page');

-- =====================================================
-- Fin du script
-- =====================================================
