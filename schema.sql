CREATE DATABASE IF NOT EXISTS tabletalk
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tabletalk;

CREATE TABLE IF NOT EXISTS vestigingen (
    id INT NOT NULL AUTO_INCREMENT,
    naam VARCHAR(100) NOT NULL,
    actief TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    id INT NOT NULL AUTO_INCREMENT,
    vestiging_id INT NOT NULL,
    naam VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    wachtwoord_hash VARCHAR(255) NOT NULL,
    actief TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_email (email),
    KEY idx_admins_vestiging (vestiging_id),
    CONSTRAINT fk_admins_vestiging
        FOREIGN KEY (vestiging_id) REFERENCES vestigingen (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
    id INT NOT NULL AUTO_INCREMENT,
    vestiging_id INT NOT NULL,
    score TINYINT UNSIGNED NOT NULL,
    categorie ENUM('Eten', 'Bediening', 'Sfeer') NOT NULL,
    toelichting TEXT NOT NULL,
    status ENUM('Nieuw', 'Gelezen', 'Actie ondernomen') NOT NULL DEFAULT 'Nieuw',
    submission_token CHAR(64) NOT NULL,
    aangemaakt_op DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bijgewerkt_op DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_feedback_submission_token (submission_token),
    KEY idx_feedback_vestiging_datum (vestiging_id, aangemaakt_op),
    KEY idx_feedback_vestiging_score (vestiging_id, score),
    CONSTRAINT fk_feedback_vestiging
        FOREIGN KEY (vestiging_id) REFERENCES vestigingen (id)
        ON DELETE CASCADE,
    CONSTRAINT chk_feedback_score CHECK (score BETWEEN 1 AND 5)
) ENGINE=InnoDB;
