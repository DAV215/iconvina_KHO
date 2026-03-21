<?php

declare(strict_types=1);

use App\Core\Database\Migrations\Migration;
use PDO;

return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $queries = [
            'CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NULL,
                phone VARCHAR(30) NULL,
                role_code VARCHAR(50) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT "active",
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS customers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                name VARCHAR(190) NOT NULL,
                contact_name VARCHAR(150) NULL,
                phone VARCHAR(30) NULL,
                email VARCHAR(150) NULL,
                tax_code VARCHAR(50) NULL,
                address TEXT NULL,
                customer_group VARCHAR(50) NULL,
                note TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS quotations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                customer_id BIGINT UNSIGNED NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT "draft",
                quoted_at DATETIME NULL,
                expired_at DATETIME NULL,
                subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
                discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                note TEXT NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_quotations_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
                CONSTRAINT fk_quotations_user FOREIGN KEY (created_by) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS quotation_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                quotation_id BIGINT UNSIGNED NOT NULL,
                line_no INT UNSIGNED NOT NULL,
                item_type VARCHAR(30) NOT NULL DEFAULT "service",
                description VARCHAR(255) NOT NULL,
                unit VARCHAR(30) NULL,
                quantity DECIMAL(18,2) NOT NULL DEFAULT 0,
                unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                CONSTRAINT fk_quotation_items_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS sales_orders (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                customer_id BIGINT UNSIGNED NOT NULL,
                quotation_id BIGINT UNSIGNED NULL,
                order_date DATETIME NOT NULL,
                due_date DATETIME NULL,
                status VARCHAR(30) NOT NULL DEFAULT "draft",
                subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
                discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                note TEXT NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_sales_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
                CONSTRAINT fk_sales_orders_quotation FOREIGN KEY (quotation_id) REFERENCES quotations(id),
                CONSTRAINT fk_sales_orders_user FOREIGN KEY (created_by) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS sales_order_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sales_order_id BIGINT UNSIGNED NOT NULL,
                line_no INT UNSIGNED NOT NULL,
                item_type VARCHAR(30) NOT NULL DEFAULT "service",
                description VARCHAR(255) NOT NULL,
                unit VARCHAR(30) NULL,
                quantity DECIMAL(18,2) NOT NULL DEFAULT 0,
                unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                CONSTRAINT fk_sales_order_items_order FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS production_orders (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                sales_order_id BIGINT UNSIGNED NULL,
                customer_id BIGINT UNSIGNED NULL,
                title VARCHAR(190) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT "draft",
                priority VARCHAR(20) NOT NULL DEFAULT "normal",
                planned_start_at DATETIME NULL,
                planned_end_at DATETIME NULL,
                actual_start_at DATETIME NULL,
                actual_end_at DATETIME NULL,
                progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
                note TEXT NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_production_orders_sales_order FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id),
                CONSTRAINT fk_production_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
                CONSTRAINT fk_production_orders_user FOREIGN KEY (created_by) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS materials (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                name VARCHAR(190) NOT NULL,
                unit VARCHAR(30) NOT NULL,
                category VARCHAR(50) NULL,
                standard_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                on_hand DECIMAL(18,2) NOT NULL DEFAULT 0,
                min_stock DECIMAL(18,2) NOT NULL DEFAULT 0,
                note TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS stock_moves (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                material_id BIGINT UNSIGNED NOT NULL,
                move_type VARCHAR(30) NOT NULL,
                ref_type VARCHAR(30) NULL,
                ref_id BIGINT UNSIGNED NULL,
                quantity DECIMAL(18,2) NOT NULL,
                unit_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                moved_at DATETIME NOT NULL,
                note TEXT NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_stock_moves_material FOREIGN KEY (material_id) REFERENCES materials(id),
                CONSTRAINT fk_stock_moves_user FOREIGN KEY (created_by) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS receivables (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT UNSIGNED NOT NULL,
                sales_order_id BIGINT UNSIGNED NULL,
                due_date DATE NULL,
                amount DECIMAL(18,2) NOT NULL,
                paid_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                status VARCHAR(30) NOT NULL DEFAULT "open",
                note TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_receivables_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
                CONSTRAINT fk_receivables_order FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'CREATE TABLE IF NOT EXISTS payables (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_name VARCHAR(190) NOT NULL,
                production_order_id BIGINT UNSIGNED NULL,
                due_date DATE NULL,
                amount DECIMAL(18,2) NOT NULL,
                paid_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                status VARCHAR(30) NOT NULL DEFAULT "open",
                note TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_payables_production FOREIGN KEY (production_order_id) REFERENCES production_orders(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        ];

        foreach ($queries as $query) {
            $pdo->exec($query);
        }
    }

    public function down(PDO $pdo): void
    {
        $tables = [
            'payables',
            'receivables',
            'stock_moves',
            'materials',
            'production_orders',
            'sales_order_items',
            'sales_orders',
            'quotation_items',
            'quotations',
            'customers',
            'users',
        ];

        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS {$table}");
        }
    }
};
