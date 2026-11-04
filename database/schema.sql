CREATE DATABASE IF NOT EXISTS mrm_technology_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mrm_technology_db;

-- 1. USERS
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    whatsapp_number VARCHAR(30) NOT NULL,
    country VARCHAR(100),
    role ENUM('customer','admin') DEFAULT 'customer',
    email_verified_at DATETIME NULL,
    account_status ENUM(
        'pending','active','suspended'
    ) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. PROJECT CATEGORIES
CREATE TABLE project_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

-- 3. PROJECTS
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_code VARCHAR(30) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    project_title VARCHAR(200) NOT NULL,
    project_description TEXT,
    budget DECIMAL(12,2),
    budget_currency VARCHAR(10),
    expected_delivery_date DATE,
    status ENUM(
        'pending',
        'under_review',
        'accepted',
        'rejected',
        'completed'
    ) DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id)
        REFERENCES project_categories(id),

    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- 4. PROJECT QUESTIONS
CREATE TABLE project_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    section_number TINYINT UNSIGNED NOT NULL,
    question_number TINYINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM(
        'text','textarea','radio',
        'checkbox','select','date','number'
    ) NOT NULL,
    options_json JSON NULL,
    parent_question_id BIGINT UNSIGNED NULL,
    condition_value VARCHAR(255) NULL,
    is_required BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,

    FOREIGN KEY (category_id)
        REFERENCES project_categories(id),

    FOREIGN KEY (parent_question_id)
        REFERENCES project_questions(id)
) ENGINE=InnoDB;

-- 5. PROJECT ANSWERS
CREATE TABLE project_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    answer_value LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (question_id)
        REFERENCES project_questions(id),

    UNIQUE KEY unique_answer (
        project_id, question_id
    )
) ENGINE=InnoDB;

-- 6. PROJECT STATUS HISTORY
CREATE TABLE project_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    changed_by BIGINT UNSIGNED NOT NULL,
    old_status VARCHAR(30),
    new_status VARCHAR(30) NOT NULL,
    note TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 7. EMAIL VERIFICATION
CREATE TABLE email_verification_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,

    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_token (token_hash)
) ENGINE=InnoDB;

-- 8. PASSWORD RESET
CREATE TABLE password_reset_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,

    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_token (token_hash)
) ENGINE=InnoDB;

-- 9. NOTIFICATIONS
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 10. ADMIN LOGS
CREATE TABLE admin_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(150) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- INITIAL PROJECT CATEGORIES
INSERT INTO project_categories (name, description)
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
);
