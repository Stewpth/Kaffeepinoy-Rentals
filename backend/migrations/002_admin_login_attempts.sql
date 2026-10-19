-- Lightweight temporary login-attempt buckets keyed by a hash of the remote IP.
CREATE TABLE admin_login_attempts (
    bucket_hash CHAR(64) NOT NULL,
    failed_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at DATETIME NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (bucket_hash),
    INDEX idx_login_attempts_window (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
