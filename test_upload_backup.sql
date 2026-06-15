-- Test Backup File
-- Created for testing purposes
-- Date: 2026-06-15 11:15:08

CREATE TABLE IF NOT EXISTS test_backup_table (
    id INT(11) NOT NULL AUTO_INCREMENT,
    test_data VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO test_backup_table (test_data) VALUES 
('Test data 1'),
('Test data 2'),
('Test data 3');

SELECT * FROM test_backup_table;

