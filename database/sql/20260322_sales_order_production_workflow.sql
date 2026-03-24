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
    fulfillment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_sales_order_items_order_id (sales_order_id),
    KEY idx_sales_order_items_quote_item_id (quotation_item_id),
    KEY idx_sales_order_items_component_id (component_id),
    KEY idx_sales_order_items_material_id (material_id),
    KEY idx_sales_order_items_item_mode (item_mode),
    KEY idx_sales_order_items_fulfillment_status (fulfillment_status),
    CONSTRAINT fk_sales_order_items_order_id FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_sales_order_items_quote_item_id FOREIGN KEY (quotation_item_id) REFERENCES quotation_items(id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_order_items_component_id FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_order_items_material_id FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE production_orders
    ADD COLUMN IF NOT EXISTS sales_order_item_id BIGINT UNSIGNED NULL AFTER sales_order_id,
    ADD COLUMN IF NOT EXISTS bom_id BIGINT UNSIGNED NULL AFTER component_id,
    ADD COLUMN IF NOT EXISTS stock_shortage_qty DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER planned_qty;

ALTER TABLE production_tasks
    ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER assigned_to,
    ADD COLUMN IF NOT EXISTS actual_start_at DATETIME NULL AFTER planned_end_at,
    ADD COLUMN IF NOT EXISTS actual_end_at DATETIME NULL AFTER actual_start_at,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER note,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE INDEX idx_production_orders_sales_order_item_id ON production_orders (sales_order_item_id);
CREATE INDEX idx_production_orders_status ON production_orders (status);
CREATE INDEX idx_production_tasks_status ON production_tasks (status);
CREATE INDEX idx_production_tasks_assigned_to ON production_tasks (assigned_to);

INSERT INTO permissions (module, action)
SELECT 'sales_order', 'confirm' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'sales_order' AND action = 'confirm');

INSERT INTO permissions (module, action)
SELECT 'production', 'view' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'view');

INSERT INTO permissions (module, action)
SELECT 'production', 'create' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'create');

INSERT INTO permissions (module, action)
SELECT 'production', 'update' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'update');

INSERT INTO permissions (module, action)
SELECT 'production', 'assign' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'assign');

INSERT INTO permissions (module, action)
SELECT 'production', 'complete' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'complete');
