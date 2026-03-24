CREATE TABLE IF NOT EXISTS service_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL,
    sales_order_id INT UNSIGNED NOT NULL,
    sales_order_item_id INT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    service_name VARCHAR(190) NOT NULL,
    work_description TEXT NULL,
    quantity DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    assigned_to INT UNSIGNED NULL,
    priority TINYINT UNSIGNED NOT NULL DEFAULT 2,
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    planned_start_at DATETIME NULL,
    planned_end_at DATETIME NULL,
    actual_start_at DATETIME NULL,
    actual_end_at DATETIME NULL,
    internal_note TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL,
    KEY idx_service_orders_sales_order_id (sales_order_id),
    KEY idx_service_orders_sales_order_item_id (sales_order_item_id),
    KEY idx_service_orders_status (status),
    KEY idx_service_orders_assigned_to (assigned_to),
    UNIQUE KEY uq_service_orders_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_order_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NULL,
    changed_fields_json LONGTEXT NULL,
    remark TEXT NULL,
    acted_by INT UNSIGNED NULL,
    acted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    KEY idx_service_order_logs_service_order_id (service_order_id),
    KEY idx_service_order_logs_acted_at (acted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (module, action)
SELECT 'service_order', 'view'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'view'
);

INSERT INTO permissions (module, action)
SELECT 'service_order', 'create'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'create'
);

INSERT INTO permissions (module, action)
SELECT 'service_order', 'update'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'update'
);

INSERT INTO permissions (module, action)
SELECT 'service_order', 'assign'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'assign'
);

INSERT INTO permissions (module, action)
SELECT 'service_order', 'start'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'start'
);

INSERT INTO permissions (module, action)
SELECT 'service_order', 'complete'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'complete'
);

INSERT INTO permissions (module, action)
SELECT 'service_order', 'cancel'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'cancel'
);

INSERT INTO permissions (module, action)
SELECT 'service_order', 'view_log'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'service_order' AND action = 'view_log'
);
