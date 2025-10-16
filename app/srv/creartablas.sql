CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- ID interno de la orden
    order_code VARCHAR(50) NOT NULL UNIQUE,   -- Código de orden (ej. ORD-2025-001)
    full_name VARCHAR(255) NOT NULL,         -- Nombre completo del cliente
    email VARCHAR(255) NOT NULL,             -- Correo electrónico
    address VARCHAR(255) NOT NULL,           -- Dirección física
    phone_prefix VARCHAR(10) NOT NULL,       -- Prefijo telefónico
    phone_number VARCHAR(20) NOT NULL,       -- Número de teléfono
    subtotal DECIMAL(10,2) NOT NULL,         -- Subtotal sin IVA
    total_with_vat DECIMAL(10,2) NOT NULL,   -- Total con IVA 21%
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,                    -- Relación con la tabla orders
    product_name VARCHAR(100) NOT NULL,       -- Nombre del producto (Cinema, Theater, etc.)
    unit_price DECIMAL(10,2) NOT NULL,       -- Precio unitario
    quantity INT NOT NULL,                    -- Cantidad seleccionada
    total_price DECIMAL(10,2) NOT NULL,      -- total = unit_price * quantity
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
