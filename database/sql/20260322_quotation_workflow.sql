CREATE TABLE IF NOT EXISTS quotation_logs (
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
    KEY idx_quotation_logs_entity (module, entity_id),
    KEY idx_quotation_logs_action (action),
    KEY idx_quotation_logs_acted_at (acted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (module, action)
SELECT 'quotation', 'view' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'view');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'create' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'create');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'update' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'update');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'delete' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'delete');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'submit' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'submit');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'approve' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'approve');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'reject' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'reject');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'cancel' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'cancel');
INSERT INTO permissions (module, action)
SELECT 'quotation', 'view_log' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE module = 'quotation' AND action = 'view_log');
