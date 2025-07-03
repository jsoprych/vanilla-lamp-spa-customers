-- Main tables (SQLite3)
-- CREATE TABLE customers (
--    customer_id INTEGER PRIMARY KEY AUTOINCREMENT,
--    first_name TEXT NOT NULL,
--    last_name TEXT NOT NULL,
--    email TEXT UNIQUE,
--    phone TEXT,
--    status TEXT DEFAULT 'active',
--    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- Audit table used by PHP server/core
CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_name TEXT NOT NULL,
    record_id INTEGER NOT NULL,
    action TEXT NOT NULL CHECK(action IN ('CREATE','UPDATE','DELETE')),
    old_values TEXT,
    new_values TEXT,
    user_ip TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_audit_table_record ON audit_logs(table_name, record_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at);