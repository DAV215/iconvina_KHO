CREATE TABLE IF NOT EXISTS sales_deliveries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sales_order_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(30) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    delivery_date DATE NOT NULL,
    shipping_cost DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    note TEXT NULL,
    stock_transaction_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    confirmed_by BIGINT UNSIGNED NULL,
    confirmed_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_sales_deliveries_code (code),
    KEY idx_sales_deliveries_order (sales_order_id),
    KEY idx_sales_deliveries_status (status),
    KEY idx_sales_deliveries_stock_txn (stock_transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sales_delivery_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sales_delivery_id BIGINT UNSIGNED NOT NULL,
    sales_order_item_id BIGINT UNSIGNED NOT NULL,
    item_kind VARCHAR(20) NOT NULL,
    component_id BIGINT UNSIGNED NULL,
    material_id BIGINT UNSIGNED NULL,
    ordered_qty DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    ready_qty DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    delivery_qty DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    remaining_qty DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_sales_delivery_items_delivery (sales_delivery_id),
    KEY idx_sales_delivery_items_order_item (sales_order_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sales_order_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    module VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NULL,
    changed_fields_json LONGTEXT NULL,
    remark TEXT NULL,
    acted_by BIGINT UNSIGNED NULL,
    acted_at DATETIME NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    KEY idx_sales_order_logs_entity (module, entity_id),
    KEY idx_sales_order_logs_acted_at (acted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (module, action)
SELECT 'sales_order', 'deliver'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'sales_order' AND action = 'deliver'
);

INSERT INTO permissions (module, action)
SELECT 'sales_order', 'cancel_delivery'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'sales_order' AND action = 'cancel_delivery'
);

INSERT INTO permissions (module, action)
SELECT 'sales_order', 'view_log'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'sales_order' AND action = 'view_log'
);

ALTER TABLE sales_deliveries
    ADD COLUMN IF NOT EXISTS shipping_cost DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER delivery_date;
