PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
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
INSERT INTO customers VALUES(1,'CUST1001','John','Smith',NULL,'individual','123-45-6789','active','Preferred customer','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(2,'CUST1002','Emily','Johnson',NULL,'individual','987-65-4321','active','Frequent shopper','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(3,'CUST1003','Michael','Williams',NULL,'individual','456-78-9123','inactive','Account on hold','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(4,'CUST1004','Sarah','Brown',NULL,'individual','789-12-3456','active',NULL,'2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(5,'CUST1005','David','Jones',NULL,'individual','321-54-6789','lead','Contacted last week','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(6,'CUST2001',NULL,NULL,'Acme Corporation','business','12-3456789','active','Wholesale client','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(7,'CUST2002',NULL,NULL,'Globex Inc','business','98-7654321','active','VIP customer','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(8,'CUST2003',NULL,NULL,'Initech LLC','business','45-6789123','prospect','Evaluation period','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(9,'CUST2004',NULL,NULL,'Umbrella Corp','business','78-9123456','active','Monthly billing','2025-06-23 19:24:07','2025-06-23 19:24:07');
INSERT INTO customers VALUES(10,'CUST2005',NULL,NULL,'Stark Industries','business','32-1546789','active','Technical support required','2025-06-23 19:24:07','2025-06-23 19:24:07');
CREATE TABLE customer_contacts (
    contact_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    contact_type TEXT CHECK(contact_type IN ('email', 'phone', 'mobile', 'fax', 'other')),
    contact_value TEXT NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
INSERT INTO customer_contacts VALUES(1,1,'email','john.smith@example.com',1,'Primary email');
INSERT INTO customer_contacts VALUES(2,1,'phone','555-0101',0,'Home phone');
INSERT INTO customer_contacts VALUES(3,1,'mobile','555-0102',1,'Cell phone');
INSERT INTO customer_contacts VALUES(4,2,'email','emily.j@example.com',1,NULL);
INSERT INTO customer_contacts VALUES(5,2,'mobile','555-0201',1,'Preferred contact');
INSERT INTO customer_contacts VALUES(6,3,'email','michael.w@example.com',1,NULL);
INSERT INTO customer_contacts VALUES(7,4,'email','sarah.b@example.com',1,NULL);
INSERT INTO customer_contacts VALUES(8,4,'phone','555-0401',0,'Work phone');
INSERT INTO customer_contacts VALUES(9,5,'email','david.j@example.com',1,NULL);
INSERT INTO customer_contacts VALUES(10,6,'email','sales@acme.com',1,'Main contact');
INSERT INTO customer_contacts VALUES(11,6,'phone','555-0601',1,'Main line');
INSERT INTO customer_contacts VALUES(12,6,'fax','555-0602',0,NULL);
INSERT INTO customer_contacts VALUES(13,7,'email','info@globex.com',1,NULL);
INSERT INTO customer_contacts VALUES(14,7,'phone','555-0701',1,NULL);
INSERT INTO customer_contacts VALUES(15,8,'email','contact@initech.com',1,NULL);
INSERT INTO customer_contacts VALUES(16,9,'email','sales@umbrella.com',1,NULL);
INSERT INTO customer_contacts VALUES(17,10,'email','tony@stark.com',1,'CEO email');
INSERT INTO customer_contacts VALUES(18,10,'mobile','555-1001',1,'Direct line');
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
INSERT INTO customer_addresses VALUES(1,1,'home','123 Main St','Apt 4B','Springfield','IL','62704','US',1,'Primary residence');
INSERT INTO customer_addresses VALUES(2,1,'billing','123 Main St','Apt 4B','Springfield','IL','62704','US',1,NULL);
INSERT INTO customer_addresses VALUES(3,2,'home','456 Oak Ave',NULL,'Shelbyville','IL','62705','US',1,NULL);
INSERT INTO customer_addresses VALUES(4,3,'home','789 Pine Rd',NULL,'Capital City','IL','62706','US',1,NULL);
INSERT INTO customer_addresses VALUES(5,4,'home','321 Elm St','Unit 12','Ogdenville','IL','62707','US',1,NULL);
INSERT INTO customer_addresses VALUES(6,5,'home','654 Maple Dr',NULL,'North Haverbrook','IL','62708','US',1,NULL);
INSERT INTO customer_addresses VALUES(7,6,'billing','100 Industrial Park','Building 5','Springfield','IL','62709','US',1,'Corporate HQ');
INSERT INTO customer_addresses VALUES(8,6,'shipping','200 Warehouse Ave',NULL,'Springfield','IL','62710','US',0,'Distribution center');
INSERT INTO customer_addresses VALUES(9,7,'billing','500 Tech Plaza','Suite 1000','Shelbyville','IL','62711','US',1,NULL);
INSERT INTO customer_addresses VALUES(10,8,'billing','700 Innovation Way',NULL,'Capital City','IL','62712','US',1,NULL);
INSERT INTO customer_addresses VALUES(11,9,'billing','900 Biomedical Park',NULL,'Ogdenville','IL','62713','US',1,NULL);
INSERT INTO customer_addresses VALUES(12,10,'billing','1 Stark Tower','Penthouse','New York','NY','10001','US',1,'Corporate headquarters');
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
INSERT INTO customer_payment_methods VALUES(1,1,'credit_card','{"last4":"4242","brand":"Visa"}',1,1,'2025-06-23 19:24:07','2025-12-31','Primary card');
INSERT INTO customer_payment_methods VALUES(2,1,'credit_card','{"last4":"5555","brand":"Mastercard"}',0,1,'2025-06-23 19:24:07','2024-10-31','Backup card');
INSERT INTO customer_payment_methods VALUES(3,2,'credit_card','{"last4":"1111","brand":"Amex"}',1,1,'2025-06-23 19:24:07','2026-06-30',NULL);
INSERT INTO customer_payment_methods VALUES(4,4,'bank_account','{"last4":"1234","bank":"Springfield Credit Union"}',1,1,'2025-06-23 19:24:07',NULL,'ACH payments');
INSERT INTO customer_payment_methods VALUES(5,6,'credit_card','{"last4":"4321","brand":"Visa"}',1,1,'2025-06-23 19:24:07','2025-09-30','Corporate card');
INSERT INTO customer_payment_methods VALUES(6,6,'bank_account','{"last4":"5678","bank":"First National"}',0,1,'2025-06-23 19:24:07',NULL,'Wire transfers');
INSERT INTO customer_payment_methods VALUES(7,7,'credit_card','{"last4":"8765","brand":"Mastercard"}',1,1,'2025-06-23 19:24:07','2026-03-31',NULL);
INSERT INTO customer_payment_methods VALUES(8,10,'credit_card','{"last4":"9999","brand":"Amex"}',1,1,'2025-06-23 19:24:07','2027-12-31','Black card');
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
INSERT INTO customer_demographics VALUES(1,1,'1980-05-15','male','married',4,85000.0,'bachelors','Software Engineer');
INSERT INTO customer_demographics VALUES(2,2,'1985-08-22','female','single',1,65000.0,'masters','Marketing Manager');
INSERT INTO customer_demographics VALUES(3,3,'1975-11-30','male','divorced',2,55000.0,'associates','Electrician');
INSERT INTO customer_demographics VALUES(4,4,'1990-03-10','female','married',3,75000.0,'bachelors','Teacher');
INSERT INTO customer_demographics VALUES(5,5,'1988-07-04','male','single',1,48000.0,'high_school','Sales Associate');
CREATE TABLE customer_preferences (
    preference_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    preference_type TEXT NOT NULL,  -- e.g., 'communication', 'product', 'service'
    preference_name TEXT NOT NULL,  -- e.g., 'email_newsletter', 'preferred_contact_method'
    preference_value TEXT,          -- e.g., 'opt_in', 'phone'
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
INSERT INTO customer_preferences VALUES(1,1,'communication','email_newsletter','opt_in');
INSERT INTO customer_preferences VALUES(2,1,'communication','promotional_offers','opt_in');
INSERT INTO customer_preferences VALUES(3,1,'communication','preferred_contact','email');
INSERT INTO customer_preferences VALUES(4,2,'communication','email_newsletter','opt_out');
INSERT INTO customer_preferences VALUES(5,2,'communication','promotional_offers','opt_in');
INSERT INTO customer_preferences VALUES(6,2,'communication','preferred_contact','mobile');
INSERT INTO customer_preferences VALUES(7,3,'communication','email_newsletter','opt_out');
INSERT INTO customer_preferences VALUES(8,4,'product','preferred_category','electronics');
INSERT INTO customer_preferences VALUES(9,5,'communication','email_newsletter','opt_in');
INSERT INTO customer_preferences VALUES(10,6,'service','preferred_shipping','overnight');
INSERT INTO customer_preferences VALUES(11,6,'communication','invoice_delivery','email');
INSERT INTO customer_preferences VALUES(12,10,'product','preferred_category','high_tech');
CREATE TABLE customer_tags (
    tag_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    tag_name TEXT NOT NULL,  -- e.g., 'VIP', 'wholesale', 'frequent_buyer'
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
INSERT INTO customer_tags VALUES(1,1,'VIP');
INSERT INTO customer_tags VALUES(2,1,'frequent_buyer');
INSERT INTO customer_tags VALUES(3,2,'frequent_buyer');
INSERT INTO customer_tags VALUES(4,4,'early_adopter');
INSERT INTO customer_tags VALUES(5,6,'wholesale');
INSERT INTO customer_tags VALUES(6,6,'VIP');
INSERT INTO customer_tags VALUES(7,7,'wholesale');
INSERT INTO customer_tags VALUES(8,9,'government');
INSERT INTO customer_tags VALUES(9,10,'VIP');
INSERT INTO customer_tags VALUES(10,10,'high_value');
CREATE TABLE customer_relationships (
    relationship_id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    related_customer_id INTEGER NOT NULL,
    relationship_type TEXT NOT NULL,  -- e.g., 'family', 'business_partner', 'subsidiary'
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (related_customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);
INSERT INTO customer_relationships VALUES(1,1,4,'family','Spouse');
INSERT INTO customer_relationships VALUES(2,6,7,'business_partner','Joint venture partners');
INSERT INTO customer_relationships VALUES(3,9,10,'supplier','Technology components');
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
INSERT INTO customer_activity VALUES(1,1,'login','Successful login','192.168.1.100','Mozilla/5.0 (Windows NT 10.0)','2025-06-23 19:24:08');
INSERT INTO customer_activity VALUES(2,1,'purchase','Order #1001 - $125.99','192.168.1.100','Mozilla/5.0 (Windows NT 10.0)','2025-06-23 19:24:08');
INSERT INTO customer_activity VALUES(3,2,'login','Successful login','203.0.113.42','Mozilla/5.0 (iPhone)','2025-06-23 19:24:08');
INSERT INTO customer_activity VALUES(4,6,'support_ticket','Ticket #4567 - Billing question','198.51.100.15','Mozilla/5.0 (Macintosh)','2025-06-23 19:24:08');
INSERT INTO customer_activity VALUES(5,6,'purchase','Order #1002 - $1,250.00','198.51.100.15','Mozilla/5.0 (Macintosh)','2025-06-23 19:24:08');
INSERT INTO customer_activity VALUES(6,10,'login','Successful login','192.0.2.8','Mozilla/5.0 (iPhone)','2025-06-23 19:24:08');
INSERT INTO customer_activity VALUES(7,10,'account_update','Changed payment method','192.0.2.8','Mozilla/5.0 (iPhone)','2025-06-23 19:24:08');
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
DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence VALUES('customers',10);
INSERT INTO sqlite_sequence VALUES('customer_contacts',18);
INSERT INTO sqlite_sequence VALUES('customer_addresses',12);
INSERT INTO sqlite_sequence VALUES('customer_payment_methods',8);
INSERT INTO sqlite_sequence VALUES('customer_demographics',5);
INSERT INTO sqlite_sequence VALUES('customer_preferences',12);
INSERT INTO sqlite_sequence VALUES('customer_tags',10);
INSERT INTO sqlite_sequence VALUES('customer_relationships',3);
INSERT INTO sqlite_sequence VALUES('customer_activity',7);
INSERT INTO sqlite_sequence VALUES('audit_logs',0);
CREATE TRIGGER update_customer_timestamp
AFTER UPDATE ON customers
FOR EACH ROW
BEGIN
    UPDATE customers SET updated_at = CURRENT_TIMESTAMP WHERE customer_id = OLD.customer_id;
END;
CREATE INDEX idx_audit_table_record ON audit_logs(table_name, record_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at);
COMMIT;
