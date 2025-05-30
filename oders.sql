CREATE TABLE orders (
    id INT(11) NOT NULL AUTO_INCREMENT, -- Unique identifier for each order
    customer_id INT(11) NOT NULL,       -- Foreign key referencing the customer who placed the order
    part_id INT(11) NOT NULL,           -- Foreign key referencing the stock item (part) being ordered
    quantity INT(11) NOT NULL,          -- Quantity of the part being ordered
    order_date DATE NOT NULL,           -- Date the order was placed
    status ENUM('pending', 'fulfilled', 'canceled') NOT NULL DEFAULT 'pending', -- Order status
    flag ENUM('active', 'inactive') NOT NULL DEFAULT 'active', -- Flag to indicate if the record is active or inactive
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the order was created
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Timestamp when the order was last updated
    PRIMARY KEY (id),                   -- Primary key
    FOREIGN KEY (customer_id) REFERENCES customers(id), -- Foreign key constraint for customers
    FOREIGN KEY (part_id) REFERENCES stocks(id) -- Foreign key constraint for stocks
);