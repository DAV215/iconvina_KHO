CREATE TABLE material_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    note VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE materials
    ADD COLUMN category_id BIGINT UNSIGNED NULL AFTER name,
    ADD COLUMN specification VARCHAR(255) NULL AFTER unit,
    ADD COLUMN color VARCHAR(100) NULL AFTER specification,
    ADD COLUMN image_path VARCHAR(255) NULL AFTER color,
    ADD COLUMN description TEXT NULL AFTER image_path;

ALTER TABLE materials
    ADD INDEX idx_materials_category_id (category_id),
    ADD CONSTRAINT fk_materials_category
        FOREIGN KEY (category_id) REFERENCES material_categories(id)
        ON UPDATE RESTRICT
        ON DELETE SET NULL;