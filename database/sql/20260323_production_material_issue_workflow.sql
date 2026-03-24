CREATE TABLE IF NOT EXISTS production_order_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    production_order_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NULL,
    changed_fields_json JSON NULL,
    remark VARCHAR(255) NULL,
    acted_by BIGINT UNSIGNED NULL,
    acted_at DATETIME NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    KEY idx_production_order_logs_order_id (production_order_id),
    KEY idx_production_order_logs_action (action),
    KEY idx_production_order_logs_acted_at (acted_at),
    CONSTRAINT fk_production_order_logs_order_id FOREIGN KEY (production_order_id) REFERENCES production_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_production_order_logs_acted_by FOREIGN KEY (acted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (module, action)
SELECT 'production', 'issue' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'issue');

INSERT INTO permissions (module, action)
SELECT 'production', 'start' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'start');

INSERT INTO permissions (module, action)
SELECT 'production', 'view_log' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'production' AND action = 'view_log');
