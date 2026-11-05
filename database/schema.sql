-- =====================================================
-- MRM TECHNOLOGY TEAM
-- DATABASE SCHEMA
-- Version: 1.0
-- =====================================================

CREATE DATABASE IF NOT EXISTS mrm_technology_team
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mrm_technology_team;


-- =====================================================
-- 1. USERS
-- =====================================================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    whatsapp_number VARCHAR(30) NOT NULL,

    country VARCHAR(100),

    role ENUM('customer','admin')
        NOT NULL DEFAULT 'customer',

    email_verified_at DATETIME NULL,

    account_status ENUM(
        'pending',
        'active',
        'suspended'
    ) NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB;


-- =====================================================
-- 2. PROJECT CATEGORIES
-- =====================================================

CREATE TABLE project_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,

    is_active BOOLEAN NOT NULL DEFAULT TRUE

) ENGINE=InnoDB;


-- =====================================================
-- 3. PROJECTS
-- =====================================================

CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    project_code VARCHAR(30) NOT NULL UNIQUE,

    user_id BIGINT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,

    project_title VARCHAR(200) NOT NULL,
    project_description TEXT,

    budget DECIMAL(12,2) NULL,
    budget_currency VARCHAR(10) NULL,

    expected_delivery_date DATE NULL,

    status ENUM(
        'pending',
        'under_review',
        'accepted',
        'rejected',
        'completed'
    ) NOT NULL DEFAULT 'pending',

    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_projects_user
        FOREIGN KEY (user_id)
        REFERENCES users(id),

    CONSTRAINT fk_projects_category
        FOREIGN KEY (category_id)
        REFERENCES project_categories(id),

    INDEX idx_projects_user (user_id),
    INDEX idx_projects_category (category_id),
    INDEX idx_projects_status (status),
    INDEX idx_projects_submitted (submitted_at)

) ENGINE=InnoDB;


-- =====================================================
-- 4. PROJECT QUESTIONS
-- =====================================================

CREATE TABLE project_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    category_id INT UNSIGNED NULL,

    section_number TINYINT UNSIGNED NOT NULL,
    question_number TINYINT UNSIGNED NOT NULL,

    question_text TEXT NOT NULL,

    question_type ENUM(
        'text',
        'textarea',
        'radio',
        'checkbox',
        'select',
        'date',
        'number'
    ) NOT NULL,

    options_json JSON NULL,

    parent_question_id BIGINT UNSIGNED NULL,
    condition_value VARCHAR(255) NULL,

    is_required BOOLEAN NOT NULL DEFAULT TRUE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT fk_questions_category
        FOREIGN KEY (category_id)
        REFERENCES project_categories(id),

    CONSTRAINT fk_questions_parent
        FOREIGN KEY (parent_question_id)
        REFERENCES project_questions(id),

    UNIQUE KEY unique_category_question (
        category_id,
        section_number,
        question_number
    ),

    INDEX idx_questions_parent (parent_question_id),
    INDEX idx_questions_active (is_active)

) ENGINE=InnoDB;


-- =====================================================
-- 5. PROJECT ANSWERS
-- =====================================================

CREATE TABLE project_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    project_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,

    answer_value LONGTEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_answers_project
        FOREIGN KEY (project_id)
        REFERENCES projects(id),

    CONSTRAINT fk_answers_question
        FOREIGN KEY (question_id)
        REFERENCES project_questions(id),

    UNIQUE KEY unique_answer (
        project_id,
        question_id
    ),

    INDEX idx_answers_question (question_id)

) ENGINE=InnoDB;


-- =====================================================
-- 6. PROJECT STATUS HISTORY
-- =====================================================

CREATE TABLE project_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    project_id BIGINT UNSIGNED NOT NULL,
    changed_by BIGINT UNSIGNED NOT NULL,

    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NOT NULL,

    note TEXT NULL,

    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_status_project
        FOREIGN KEY (project_id)
        REFERENCES projects(id),

    CONSTRAINT fk_status_user
        FOREIGN KEY (changed_by)
        REFERENCES users(id),

    INDEX idx_history_project (project_id),
    INDEX idx_history_user (changed_by),
    INDEX idx_history_date (changed_at)

) ENGINE=InnoDB;


-- =====================================================
-- 7. EMAIL VERIFICATION TOKENS
-- =====================================================

CREATE TABLE email_verification_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    token_hash VARCHAR(255) NOT NULL,

    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,

    CONSTRAINT fk_email_tokens_user
        FOREIGN KEY (user_id)
        REFERENCES users(id),

    UNIQUE KEY unique_email_token_hash (
        token_hash
    ),

    INDEX idx_email_tokens_user (user_id),
    INDEX idx_email_tokens_expires (expires_at)

) ENGINE=InnoDB;


-- =====================================================
-- 8. PASSWORD RESET TOKENS
-- =====================================================

CREATE TABLE password_reset_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    token_hash VARCHAR(255) NOT NULL,

    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,

    CONSTRAINT fk_password_tokens_user
        FOREIGN KEY (user_id)
        REFERENCES users(id),

    UNIQUE KEY unique_password_reset_token (
        token_hash
    ),

    INDEX idx_password_reset_user (user_id),
    INDEX idx_password_reset_expires (expires_at)

) ENGINE=InnoDB;


-- =====================================================
-- 9. NOTIFICATIONS
-- =====================================================

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,

    is_read BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(id),

    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (is_read),
    INDEX idx_notifications_date (created_at)

) ENGINE=InnoDB;


-- =====================================================
-- 10. ADMIN LOGS
-- =====================================================

CREATE TABLE admin_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    admin_id BIGINT UNSIGNED NOT NULL,

    action VARCHAR(150) NOT NULL,
    description TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_admin_logs_user
        FOREIGN KEY (admin_id)
        REFERENCES users(id),

    INDEX idx_admin_logs_admin (admin_id),
    INDEX idx_admin_logs_date (created_at)

) ENGINE=InnoDB;


-- =====================================================
-- INITIAL PROJECT CATEGORIES
-- =====================================================

INSERT INTO project_categories (
    name,
    description
)
VALUES
(
    'Application Development',
    'Android, iOS and Web Applications'
),
(
    'Database Development',
    'Database Design and Management Systems'
),
(
    'Website Development',
    'Business and Custom Websites'
)

ON DUPLICATE KEY UPDATE
    description = VALUES(description);


-- =====================================================
-- END OF DATABASE SCHEMA
-- MRM TECHNOLOGY TEAM
-- =====================================================
