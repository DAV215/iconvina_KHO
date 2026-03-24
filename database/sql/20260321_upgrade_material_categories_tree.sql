SET @schema_name := DATABASE();

SET @sql := IF (
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @schema_name
          AND TABLE_NAME = 'material_categories'
          AND COLUMN_NAME = 'parent_id'
    ),
    'SELECT 1',
    'ALTER TABLE material_categories ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER name'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @schema_name
          AND TABLE_NAME = 'material_categories'
          AND INDEX_NAME = 'idx_material_categories_parent_id'
    ),
    'SELECT 1',
    'ALTER TABLE material_categories ADD INDEX idx_material_categories_parent_id (parent_id)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (
        SELECT 1
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = @schema_name
          AND TABLE_NAME = 'material_categories'
          AND CONSTRAINT_NAME = 'fk_material_categories_parent'
    ),
    'SELECT 1',
    'ALTER TABLE material_categories ADD CONSTRAINT fk_material_categories_parent FOREIGN KEY (parent_id) REFERENCES material_categories(id) ON UPDATE RESTRICT ON DELETE SET NULL'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
