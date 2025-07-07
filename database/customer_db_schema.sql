CREATE TABLE customers (
    customer_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_code TEXT UNIQUE,  -- Business-generated customer number/ID
    first_name TEXT,
    last_name TEXT,
    company_name TEXT,
    customer_type TEXT CHECK(customer_type IN ('individual', 'business')), -- Individual or business
    tax_id TEXT,  -- SSN, EIN, VAT, etc.
    status TEXT CHECK(status IN ('active', 'inactive', 'prospect', 'lead', 'banned')) DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE customer_contacts (
    contact_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    contact_type TEXT CHECK(contact_type IN ('email', 'phone', 'mobile', 'fax', 'other')),
    contact_value TEXT NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TABLE customer_addresses (
    address_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    address_type TEXT CHECK(address_type IN ('billing', 'shipping', 'home', 'work', 'other')),
    line1 TEXT NOT NULL,
    line2 TEXT,
    city TEXT NOT NULL,
    state_province TEXT,
    postal_code TEXT,
    country TEXT DEFAULT 'US',
    is_primary BOOLEAN DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TABLE customer_payment_methods (
    payment_method_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    payment_type TEXT CHECK(payment_type IN ('credit_card', 'debit_card', 'bank_account', 'paypal', 'other')),
    payment_details TEXT,  -- Could be JSON or store encrypted data
    is_default BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TABLE customer_demographics (
    demographics_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL UNIQUE,
    birth_date DATE,
    gender TEXT,
    marital_status TEXT,
    household_size INTEGER,
    annual_income REAL,
    education_level TEXT,
    occupation TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TABLE customer_preferences (
    preference_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    preference_type TEXT NOT NULL,  -- e.g., 'communication', 'product', 'service'
    preference_name TEXT NOT NULL,  -- e.g., 'email_newsletter', 'preferred_contact_method'
    preference_value TEXT,          -- e.g., 'opt_in', 'phone'
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TABLE customer_tags (
    tag_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    tag_name TEXT NOT NULL,  -- e.g., 'VIP', 'wholesale', 'frequent_buyer'
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TABLE customer_relationships (
    relationship_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    related_customer_id INTEGER NOT NULL,
    relationship_type TEXT NOT NULL,  -- e.g., 'family', 'business_partner', 'subsidiary'
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (related_customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TABLE customer_activity (
    activity_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    activity_type TEXT NOT NULL,  -- e.g., 'login', 'purchase', 'support_ticket'
    activity_details TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
CREATE TRIGGER update_customer_timestamp
AFTER UPDATE ON customers
FOR EACH ROW
BEGIN
    UPDATE customers SET updated_at = CURRENT_TIMESTAMP WHERE customer_id = OLD.customer_id;
END;
CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_name TEXT NOT NULL,
    record_id INTEGER NOT NULL,
    action TEXT NOT NULL CHECK (action IN ('CREATE', 'UPDATE', 'DELETE', 'READ')),
    old_values TEXT,
    new_values TEXT,
    user_ip TEXT,
    created_at TEXT
);
CREATE INDEX idx_audit_table_record ON audit_logs(table_name, record_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at);
