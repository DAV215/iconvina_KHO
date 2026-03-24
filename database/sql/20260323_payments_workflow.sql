ALTER TABLE sales_orders
    ADD COLUMN paid_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER total_amount,
    ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid' AFTER paid_amount;

ALTER TABLE purchase_orders
    ADD COLUMN paid_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER total_amount,
    ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid' AFTER paid_amount;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    payment_type VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    customer_id BIGINT UNSIGNED NULL,
    supplier_id BIGINT UNSIGNED NULL,
    sales_order_id BIGINT UNSIGNED NULL,
    purchase_order_id BIGINT UNSIGNED NULL,
    amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    reference_no VARCHAR(80) NULL,
    note TEXT NULL,
    confirmed_by BIGINT UNSIGNED NULL,
    confirmed_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL,
    KEY idx_payments_sales_order_id (sales_order_id),
    KEY idx_payments_purchase_order_id (purchase_order_id),
    KEY idx_payments_status (status),
    KEY idx_payments_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (module, action)
SELECT 'payment', 'view'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'payment' AND action = 'view'
);

INSERT INTO permissions (module, action)
SELECT 'payment', 'create'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'payment' AND action = 'create'
);

INSERT INTO permissions (module, action)
SELECT 'payment', 'confirm'
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE module = 'payment' AND action = 'confirm'
);
