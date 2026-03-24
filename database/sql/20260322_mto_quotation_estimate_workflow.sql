ALTER TABLE quotation_items
    ADD COLUMN IF NOT EXISTS item_mode VARCHAR(20) NOT NULL DEFAULT 'estimate' AFTER line_no,
    ADD COLUMN IF NOT EXISTS component_id BIGINT UNSIGNED NULL AFTER item_type,
    ADD COLUMN IF NOT EXISTS material_id BIGINT UNSIGNED NULL AFTER component_id,
    ADD COLUMN IF NOT EXISTS temp_code VARCHAR(50) NULL AFTER material_id,
    ADD COLUMN IF NOT EXISTS spec_summary TEXT NULL AFTER temp_code;

CREATE INDEX IF NOT EXISTS idx_quotation_items_component_id ON quotation_items (component_id);
CREATE INDEX IF NOT EXISTS idx_quotation_items_material_id ON quotation_items (material_id);
CREATE INDEX IF NOT EXISTS idx_quotation_items_item_mode ON quotation_items (item_mode);

CREATE TABLE IF NOT EXISTS sales_order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sales_order_id BIGINT UNSIGNED NOT NULL,
    quotation_item_id BIGINT UNSIGNED NULL,
    line_no INT NOT NULL DEFAULT 1,
    item_mode VARCHAR(20) NOT NULL DEFAULT 'estimate',
    item_type VARCHAR(20) NOT NULL DEFAULT 'estimate',
    component_id BIGINT UNSIGNED NULL,
    material_id BIGINT UNSIGNED NULL,
    temp_code VARCHAR(50) NULL,
    spec_summary TEXT NULL,
    description VARCHAR(255) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    quantity DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    unit_price DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_sales_order_items_order_id (sales_order_id),
    KEY idx_sales_order_items_quote_item_id (quotation_item_id),
    KEY idx_sales_order_items_component_id (component_id),
    KEY idx_sales_order_items_material_id (material_id),
    KEY idx_sales_order_items_item_mode (item_mode),
    CONSTRAINT fk_sales_order_items_order_id FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_sales_order_items_quote_item_id FOREIGN KEY (quotation_item_id) REFERENCES quotation_items(id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_order_items_component_id FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_order_items_material_id FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
