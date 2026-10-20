CREATE DATABASE IF NOT EXISTS tabletalk
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tabletalk;

CREATE TABLE IF NOT EXISTS vestigingen (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    naam VARCHAR(150) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    aangemaakt_op TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vestigingen_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    vestiging_id INT UNSIGNED NOT NULL,
    gebruikersnaam VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    aangemaakt_op TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_gebruikersnaam (gebruikersnaam),
    KEY idx_admins_vestiging (vestiging_id),
    CONSTRAINT fk_admins_vestiging
        FOREIGN KEY (vestiging_id) REFERENCES vestigingen (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    vestiging_id INT UNSIGNED NOT NULL,
    score TINYINT UNSIGNED NOT NULL,
    categorie VARCHAR(60) NOT NULL,
    beschrijving TEXT NOT NULL,
    status ENUM('Nieuw', 'Gelezen', 'Actie ondernomen') NOT NULL DEFAULT 'Nieuw',
    aangemaakt_op TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_feedback_vestiging_datum (vestiging_id, aangemaakt_op),
    KEY idx_feedback_vestiging_score (vestiging_id, score),
    CONSTRAINT fk_feedback_vestiging
        FOREIGN KEY (vestiging_id) REFERENCES vestigingen (id)
        ON DELETE CASCADE,
    CONSTRAINT chk_feedback_score CHECK (score BETWEEN 1 AND 5)
) ENGINE=InnoDB;
