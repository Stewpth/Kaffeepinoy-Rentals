-- Retain every manual final-obligation decision; the clients columns hold the current override.
CREATE TABLE obligation_override_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id BIGINT UNSIGNED NOT NULL,
    override_amount DECIMAL(12,2) NOT NULL,
    reason VARCHAR(500) NOT NULL,
    admin_id BIGINT UNSIGNED NULL,
    admin_username VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_override_audit_client (client_id, created_at),
    CONSTRAINT fk_override_audit_client FOREIGN KEY (client_id) REFERENCES clients(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_override_audit_admin FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_override_audit_amount CHECK (override_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
