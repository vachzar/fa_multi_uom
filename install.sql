CREATE TABLE item_uom_conversion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id VARCHAR(20) NOT NULL,
    base_uom VARCHAR(10) NOT NULL,
    alt_uom VARCHAR(10) NOT NULL,
    conversion_rate DECIMAL(20,6) NOT NULL
);
