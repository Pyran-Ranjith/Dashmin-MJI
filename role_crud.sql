CREATE TABLE role_crud (
    id INT(11) NOT NULL AUTO_INCREMENT, -- Unique identifier for each 
    role_id INT(11) NOT NULL,       -- Foreign key referencing the roles table 
    flag_create ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive', -- Flag to indicate if the record is active or inactive for create operation
    flag_read ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive', -- Flag to indicate if the record is active or inactive for read operation
    flag_update ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive', -- Flag to indicate if the record is active or inactive for update operation
    flag_delete ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive', -- Flag to indicate if the record is active or inactive for delete operation
    flag ENUM('active', 'inactive') NOT NULL DEFAULT 'active', -- Flag to indicate if the record is active or inactive
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the order was created
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Timestamp when the order was last updated
    PRIMARY KEY (id),                   -- Primary key
    FOREIGN KEY (role_id) REFERENCES roles(id), -- Foreign key constraint for roles
);