CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_roles_code (code),
    UNIQUE KEY uk_roles_name (name),
    KEY idx_roles_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (code, name, description, is_active)
SELECT 'SUPER_ADMIN', 'Super Admin', 'Toan quyen he thong', 1
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'SUPER_ADMIN');

INSERT INTO roles (code, name, description, is_active)
SELECT 'ERP_USER', 'Nhan vien ERP', 'Tai khoan van hanh ERP mac dinh', 1
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'ERP_USER');

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL,
    username VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(190) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    avatar_url VARCHAR(255) NULL,
    company_id BIGINT UNSIGNED NULL,
    branch_id BIGINT UNSIGNED NULL,
    department_id BIGINT UNSIGNED NULL,
    position_id BIGINT UNSIGNED NULL,
    manager_id BIGINT UNSIGNED NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'active', 'suspended', 'resigned', 'deleted') NOT NULL DEFAULT 'draft',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    joined_at DATE NULL,
    terminated_at DATE NULL,
    language VARCHAR(12) NOT NULL DEFAULT 'vi',
    timezone VARCHAR(64) NOT NULL DEFAULT 'Asia/Saigon',
    theme VARCHAR(32) NOT NULL DEFAULT 'light',
    last_login_at DATETIME NULL,
    failed_login_count INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    email_verified_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    deleted_by BIGINT UNSIGNED NULL,
    meta_json JSON NULL,
    note TEXT NULL,
    UNIQUE KEY uk_users_code (code),
    UNIQUE KEY uk_users_username (username),
    UNIQUE KEY uk_users_email (email),
    KEY idx_users_role_id (role_id),
    KEY idx_users_manager_id (manager_id),
    KEY idx_users_company_id (company_id),
    KEY idx_users_branch_id (branch_id),
    KEY idx_users_department_id (department_id),
    KEY idx_users_position_id (position_id),
    KEY idx_users_status (status),
    KEY idx_users_is_active (is_active),
    KEY idx_users_joined_at (joined_at),
    KEY idx_users_deleted_at (deleted_at),
    KEY idx_users_created_by (created_by),
    KEY idx_users_updated_by (updated_by),
    KEY idx_users_deleted_by (deleted_by),
    CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_users_manager_id FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_users_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_users_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS add_users_fk_if_table_exists;
DELIMITER $$
CREATE PROCEDURE add_users_fk_if_table_exists(
    IN p_table_name VARCHAR(64),
    IN p_constraint_name VARCHAR(64),
    IN p_column_name VARCHAR(64)
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
    ) AND NOT EXISTS (
        SELECT 1
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND CONSTRAINT_NAME = p_constraint_name
    ) THEN
        SET @sql = CONCAT(
            'ALTER TABLE users ADD CONSTRAINT ', p_constraint_name,
            ' FOREIGN KEY (', p_column_name, ') REFERENCES ', p_table_name, '(id) ON DELETE SET NULL'
        );
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$
DELIMITER ;

CALL add_users_fk_if_table_exists('companies', 'fk_users_company_id', 'company_id');
CALL add_users_fk_if_table_exists('branches', 'fk_users_branch_id', 'branch_id');
CALL add_users_fk_if_table_exists('departments', 'fk_users_department_id', 'department_id');
CALL add_users_fk_if_table_exists('positions', 'fk_users_position_id', 'position_id');

DROP PROCEDURE IF EXISTS add_users_fk_if_table_exists;
