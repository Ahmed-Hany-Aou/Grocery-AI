-- Create migrations table
CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- Create categories table
CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NULL,
    parent_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT categories_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create products table 
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NULL,
    barcode VARCHAR(255) NULL UNIQUE,
    category_id BIGINT NULL,
    price NUMERIC(10, 2) DEFAULT 0,
    stock_quantity INTEGER DEFAULT 0,
    image_url VARCHAR(255) NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT products_category_id_foreign FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Create invoices table
CREATE TABLE invoices (
    id BIGSERIAL PRIMARY KEY,
    invoice_number VARCHAR(255) NOT NULL UNIQUE,
    type VARCHAR(255) CHECK (type IN ('purchase', 'sale')) DEFAULT 'purchase',
    supplier_name VARCHAR(255) NULL,
    total_amount NUMERIC(10, 2) DEFAULT 0,
    status VARCHAR(255) CHECK (status IN ('pending', 'processing', 'completed', 'failed')) DEFAULT 'pending',
    image_url VARCHAR(255) NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Create invoice_items table
CREATE TABLE invoice_items (
    id BIGSERIAL PRIMARY KEY,
    invoice_id BIGINT NOT NULL,
    product_id BIGINT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INTEGER DEFAULT 1,
    unit_price NUMERIC(10, 2) DEFAULT 0,
    total_price NUMERIC(10, 2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT invoice_items_invoice_id_foreign FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT invoice_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert migration records
INSERT INTO migrations (migration, batch) VALUES
('2025_12_10_083750_create_categories_table', 1),
('2025_12_10_083759_create_products_table', 1),
('2025_12_10_083803_create_invoices_table', 1),
('2025_12_10_083808_create_invoice_items_table', 1);
