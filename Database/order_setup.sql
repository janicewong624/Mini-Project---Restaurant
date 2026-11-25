-- Order-management tables for the current users/menuitem schema.
-- Import this file into restaurant_db after users and menuitem are available.

CREATE TABLE IF NOT EXISTS restaurant_table (
    tableID VARCHAR(10) NOT NULL,
    tableNumber VARCHAR(10) NOT NULL,
    seatingCapacity TINYINT UNSIGNED NOT NULL,
    tableStatus ENUM('Available', 'Occupied', 'Reserved') NOT NULL DEFAULT 'Available',
    PRIMARY KEY (tableID),
    UNIQUE KEY uq_restaurant_table_number (tableNumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    orderID VARCHAR(10) NOT NULL,
    userID INT(11) NOT NULL,
    tableID VARCHAR(10) NULL,
    orderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    orderType ENUM('Dine-in', 'Takeaway') NOT NULL,
    totalAmount DECIMAL(10,2) NOT NULL,
    orderStatus ENUM('Pending', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    PRIMARY KEY (orderID),
    KEY idx_orders_user_date (userID, orderDate),
    KEY idx_orders_table_status (tableID, orderStatus),
    CONSTRAINT fk_orders_user FOREIGN KEY (userID) REFERENCES users (id),
    CONSTRAINT fk_orders_table FOREIGN KEY (tableID) REFERENCES restaurant_table (tableID)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orderitem (
    orderItemID VARCHAR(10) NOT NULL,
    orderID VARCHAR(10) NOT NULL,
    menuItemID VARCHAR(10) NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    unitPrice DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (orderItemID),
    KEY idx_orderitem_order (orderID),
    KEY idx_orderitem_menu (menuItemID),
    CONSTRAINT fk_orderitem_order FOREIGN KEY (orderID) REFERENCES orders (orderID)
        ON DELETE CASCADE,
    CONSTRAINT fk_orderitem_menu FOREIGN KEY (menuItemID) REFERENCES menuitem (menuItemID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment (
    paymentID VARCHAR(10) NOT NULL,
    orderID VARCHAR(10) NOT NULL,
    paymentAmount DECIMAL(10,2) NOT NULL,
    paymentMethod VARCHAR(50) NULL,
    onlineType VARCHAR(50) NULL,
    paymentDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paymentStatus ENUM('Pending', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    PRIMARY KEY (paymentID),
    UNIQUE KEY uq_payment_order (orderID),
    CONSTRAINT fk_payment_order FOREIGN KEY (orderID) REFERENCES orders (orderID)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receipt (
    receiptID VARCHAR(10) NOT NULL,
    paymentID VARCHAR(10) NOT NULL,
    receiptType ENUM('Original', 'Duplicate') NOT NULL,
    PRIMARY KEY (receiptID),
    UNIQUE KEY uq_receipt_payment_type (paymentID, receiptType),
    CONSTRAINT fk_receipt_payment FOREIGN KEY (paymentID) REFERENCES payment (paymentID)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
