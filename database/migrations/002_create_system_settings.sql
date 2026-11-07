USE mrm_technology_team;

CREATE TABLE IF NOT EXISTS system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    setting_key VARCHAR(150) NOT NULL,
    setting_value TEXT NULL,

    setting_type ENUM(
        'text',
        'textarea',
        'boolean',
        'number',
        'email',
        'url',
        'json'
    ) NOT NULL DEFAULT 'text',

    setting_group VARCHAR(100) NOT NULL DEFAULT 'general',

    setting_label VARCHAR(190) NOT NULL,

    description TEXT NULL,

    is_public TINYINT(1) NOT NULL DEFAULT 0,

    updated_by BIGINT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_system_settings_key
        UNIQUE (setting_key),

    CONSTRAINT fk_system_settings_updated_by
        FOREIGN KEY (updated_by)
        REFERENCES users(id)
        ON DELETE SET NULL,

    INDEX idx_system_settings_group (setting_group),

    INDEX idx_system_settings_public (is_public),

    INDEX idx_system_settings_updated_by (updated_by)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
