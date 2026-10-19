-- Review this additive migration and take/verify a database backup before applying.
-- Intended for the existing KaffeePinoy Rentals schema; do not drop or recreate tables.
ALTER TABLE clients
    ADD COLUMN discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER discount_type,
    ADD COLUMN final_obligation_override DECIMAL(12,2) NULL AFTER payment_status,
    ADD COLUMN override_reason VARCHAR(500) NULL AFTER final_obligation_override,
    ADD COLUMN override_admin_id BIGINT UNSIGNED NULL AFTER override_reason,
    ADD COLUMN override_at TIMESTAMP NULL AFTER override_admin_id,
    ADD CONSTRAINT chk_clients_discount_percent CHECK (discount_percent >= 0 AND discount_percent <= 100),
    ADD CONSTRAINT chk_clients_override_nonnegative CHECK (final_obligation_override IS NULL OR final_obligation_override >= 0),
    ADD CONSTRAINT fk_clients_override_admin FOREIGN KEY (override_admin_id) REFERENCES admin_users(id) ON UPDATE CASCADE ON DELETE SET NULL;

CREATE TABLE payment_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payment_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    action ENUM('create', 'edit', 'delete') NOT NULL,
    old_amount DECIMAL(12,2) NULL,
    old_payment_date DATE NULL,
    new_amount DECIMAL(12,2) NULL,
    new_payment_date DATE NULL,
    admin_id BIGINT UNSIGNED NULL,
    reason VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_payment_audit_client (client_id, created_at),
    INDEX idx_payment_audit_payment (payment_id),
    CONSTRAINT fk_payment_audit_client FOREIGN KEY (client_id) REFERENCES clients(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payment_audit_admin FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
