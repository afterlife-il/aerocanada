CREATE DATABASE synthetic_workflows;
USE synthetic_workflows;
CREATE TABLE tb_company (
    Fld_Company_ID INT PRIMARY KEY, Fld_Company_Name VARCHAR(160) NOT NULL, status VARCHAR(30)
);
CREATE TABLE tb_company_contact (
    id_company_contact INT PRIMARY KEY, Fld_Company_ID INT NOT NULL, status VARCHAR(30) NOT NULL
);
CREATE TABLE tbl_Parts (
    Fld_Part_ID INT PRIMARY KEY, Fld_Part_Nbr VARCHAR(80) NOT NULL, Fld_Part_Desc VARCHAR(200), Fld_Part_MFG INT
);
CREATE TABLE tbl_Priority (Fld_Priority_ID INT PRIMARY KEY);
CREATE TABLE tbl_RFQ_Type (Fld_RFQ_Type_ID INT PRIMARY KEY);
CREATE TABLE tbl_Payment (Fld_Payment_Term_ID INT PRIMARY KEY);
CREATE TABLE tbl_Condition (Fld_Condition_ID INT PRIMARY KEY);
CREATE TABLE tbl_Currency (Fld_Currency_ID INT PRIMARY KEY);
CREATE TABLE tbl_Shipper (Fld_Shipper_ID INT PRIMARY KEY);
CREATE TABLE tbl_Release (Fld_Release_ID INT PRIMARY KEY);
CREATE TABLE tbl_Stock (Fld_Stock_ID INT PRIMARY KEY);
CREATE TABLE tbl_RFQ (
    ID INT AUTO_INCREMENT PRIMARY KEY, Fld_RFQ_ID VARCHAR(19) UNIQUE NOT NULL, Fld_Date VARCHAR(9) NOT NULL,
    Fld_Step_1 VARCHAR(5), Fld_Step_2 VARCHAR(5), Fld_Step_3 VARCHAR(5), Fld_Priority_ID INT,
    Fld_RFQ_ACI_Employee_Id INT NOT NULL, Fld_Customer_ID INT NOT NULL
);
CREATE TABLE tbl_RFQ_1 (
    ID INT AUTO_INCREMENT PRIMARY KEY, Fld_RFQ_ID VARCHAR(30) NOT NULL, Fld_Customer_ID INT NOT NULL,
    `date` VARCHAR(15) NOT NULL, Fld_Priority_ID INT NOT NULL, Fld_Payment_Term_ID VARCHAR(200) NOT NULL,
    Employee_ID INT NOT NULL, id_company_contact INT NOT NULL, Fld_Part_ID INT NOT NULL,
    Fld_Qty INT NOT NULL, Fld_Condition_ID INT NOT NULL, Fld_RFQ_Type_ID INT NOT NULL,
    Fld_Observation TEXT, pn_rfq VARCHAR(80), description_rfq VARCHAR(200)
);
CREATE TABLE tbl_RFQ_2 (
    ID INT AUTO_INCREMENT PRIMARY KEY, Fld_RFQ_ID VARCHAR(40) NOT NULL, Fld_Part_ID INT NOT NULL,
    Fld_Supplier_ID INT NOT NULL, Fld_Supplier_Contact_ID INT, Fld_Current_Date DATE, Fld_Qty INT,
    Fld_Condition_ID INT, Fld_Payment_Term_ID INT, Fld_Price DECIMAL(12,2), Fld_Currency_ID INT,
    Fld_Part_SN VARCHAR(100), Fld_Remark TEXT, lead_time VARCHAR(100), Fld_Release_ID INT,
    Fld_Delivery VARCHAR(200), aci_contact INT NOT NULL, id_tbl_rfq1 INT
);
CREATE TABLE tbl_RFQ_3 (
    ID INT AUTO_INCREMENT PRIMARY KEY, Fld_RFQ_ID VARCHAR(40) NOT NULL, Fld_Quote_Date DATE NOT NULL,
    Fld_Part_Id INT NOT NULL, Fld_Part_SN VARCHAR(100), Fld_Qty INT, Fld_Condition INT,
    Fld_Price DECIMAL(12,2), Fld_Currency_ID INT, Fld_Remark TEXT, lead_time VARCHAR(100), Fld_Release_ID INT,
    moq VARCHAR(15) NOT NULL, Fld_Priority_ID INT, id_tbl_rfq1 INT NOT NULL,
    Fld_Send_Mail VARCHAR(8), source_type VARCHAR(40), source_id INT
);
CREATE TABLE tbl_PO_Draft (
    id INT AUTO_INCREMENT PRIMARY KEY, quotation_id INT NOT NULL UNIQUE, rfq_id VARCHAR(40), rfq_line_id INT,
    part_id INT, source_type VARCHAR(40), source_id INT, source_company_id INT,
    customer_company_id INT, customer_contact_id INT, qty VARCHAR(20), condition_id INT, price VARCHAR(30),
    currency_id INT, release_id INT, delivery VARCHAR(200), remarks TEXT, po_number VARCHAR(60),
    customer_po_number VARCHAR(100), customer_po_file VARCHAR(255), accepted_price VARCHAR(30),
    accepted_qty VARCHAR(20), accepted_condition_id INT, payment_terms VARCHAR(255), shipping_terms VARCHAR(255),
    shipping_address TEXT, acceptance_notes TEXT, status VARCHAR(30) DEFAULT 'DRAFT', created_by INT
);
CREATE TABLE tbl_Invoices (
    id INT AUTO_INCREMENT PRIMARY KEY, invoice_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_type ENUM('proforma','commercial','credit_note') NOT NULL DEFAULT 'proforma',
    rfq_id VARCHAR(100), company_id INT, contact_id INT, invoice_date DATE NOT NULL, due_date DATE,
    currency_id INT, subtotal DECIMAL(12,2) DEFAULT 0, tax_rate DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0, total DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft','sent','paid','overdue','cancelled') DEFAULT 'draft', notes TEXT, created_by INT
);
CREATE TABLE tbl_Invoice_Items (
    id INT AUTO_INCREMENT PRIMARY KEY, invoice_id INT NOT NULL, part_id INT, description VARCHAR(500),
    quantity INT DEFAULT 1, unit_price DECIMAL(12,2) DEFAULT 0, total DECIMAL(12,2) DEFAULT 0, condition_id INT
);
CREATE TABLE tbl_Shipping (
    id INT AUTO_INCREMENT PRIMARY KEY, shipping_number VARCHAR(50) NOT NULL UNIQUE, invoice_id INT,
    rfq_id VARCHAR(100), company_id INT, ship_from TEXT, ship_to TEXT, shipper_id INT, tracking_number VARCHAR(200),
    ship_date DATE, estimated_arrival DATE, actual_arrival DATE, weight VARCHAR(50), dimensions VARCHAR(100),
    status ENUM('preparing','shipped','in_transit','delivered','returned') DEFAULT 'preparing', notes TEXT, created_by INT
);
CREATE TABLE tbl_Shipping_Items (
    id INT AUTO_INCREMENT PRIMARY KEY, shipping_id INT NOT NULL, part_id INT, serial_number VARCHAR(100),
    quantity INT DEFAULT 1, condition_id INT, release_id INT
);
CREATE TABLE tbl_capa_list (
    id_capa_list INT AUTO_INCREMENT PRIMARY KEY, Fld_Part_ID INT NOT NULL, pn VARCHAR(100) NOT NULL,
    descriptioin VARCHAR(200) NOT NULL, aircraft VARCHAR(200) NOT NULL, manufacturer VARCHAR(300) NOT NULL,
    ata INT NOT NULL, capability VARCHAR(200) NOT NULL, pma VARCHAR(11) NOT NULL, doa VARCHAR(11) NOT NULL,
    der VARCHAR(11) NOT NULL, code_oem INT NOT NULL, design_oem VARCHAR(200) NOT NULL, id_company INT NOT NULL,
    status VARCHAR(250) NOT NULL, entry_date VARCHAR(20) NOT NULL, comments TEXT NOT NULL
);
CREATE TABLE tbl_Sales_Order (
    Fld_Row_ID INT PRIMARY KEY, Fld_PO VARCHAR(100), Fld_ACI_PO VARCHAR(60), Fld_Current_Date DATE,
    Fld_Company_Supplier_ID INT, Fld_RFQ_ID VARCHAR(40), Fld_PO_Still_Open VARCHAR(10)
);
CREATE TABLE tbl_PO_Customer (
    Fld_Row_ID INT PRIMARY KEY, Fld_PO VARCHAR(100), Fld_ACI_PO VARCHAR(60), Fld_Current_Date DATE,
    Fld_Company_Supplier_ID INT
);
INSERT INTO tbl_Sales_Order VALUES (1, 'PO-SYNTH', 'ACI-SYNTH', '2026-11-23', 2, '2026-11-23-120000', 'TRUE');
INSERT INTO tbl_PO_Customer VALUES (1, 'PO-SYNTH', 'ACI-SYNTH', '2026-11-23', 2);
INSERT INTO tb_company VALUES (1, 'Synthetic customer', 'Available'), (2, 'Synthetic supplier', 'Available');
INSERT INTO tb_company_contact VALUES (1, 1, 'Available'), (2, 2, 'Available');
INSERT INTO tbl_Parts (Fld_Part_ID, Fld_Part_Nbr, Fld_Part_Desc) VALUES (1, 'PART-1', 'Synthetic');
INSERT INTO tbl_Priority VALUES (1);
INSERT INTO tbl_RFQ_Type VALUES (1);
INSERT INTO tbl_Payment VALUES (1);
INSERT INTO tbl_Condition VALUES (1);
INSERT INTO tbl_Currency VALUES (1);
INSERT INTO tbl_Shipper VALUES (1);
INSERT INTO tbl_Release VALUES (1);
CREATE TABLE assertions (ok INT NOT NULL CHECK (ok = 1));
