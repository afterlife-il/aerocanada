-- Synthetic schema for test_erp_companies.php --emit-sql only.
CREATE DATABASE mcp_company_fixture CHARACTER SET utf8mb4;
USE mcp_company_fixture;
CREATE TABLE tb_company (
    Fld_Company_ID INT PRIMARY KEY AUTO_INCREMENT,
    Company_Old_Id INT NOT NULL DEFAULT 0,
    Fld_Company_Name VARCHAR(160) NOT NULL,
    Fld_Company_Rating_ID INT NOT NULL DEFAULT 0,
    `delete` VARCHAR(10) NOT NULL DEFAULT '',
    internet VARCHAR(250) NOT NULL DEFAULT '',
    cage_code VARCHAR(32) NOT NULL DEFAULT '',
    status VARCHAR(16) NOT NULL DEFAULT 'Available',
    aci_contact INT NOT NULL DEFAULT 1,
    companyrating VARCHAR(32) NOT NULL DEFAULT '',
    logocompany VARCHAR(250) NOT NULL DEFAULT '',
    customer_payment_term_id INT,
    customer_payment_term_amount DECIMAL(14,2),
    customer_payment_term_currencyid INT,
    aci_payment_term_id INT,
    aci_payment_term_amount DECIMAL(14,2),
    aci_payment_term_currencyid INT
) ENGINE=InnoDB AUTO_INCREMENT=12;
CREATE TABLE tbl_Company_Type (Fld_Company_Type_ID INT PRIMARY KEY) ENGINE=InnoDB;
CREATE TABLE tbl_Payment (Fld_Payment_Term_ID INT PRIMARY KEY) ENGINE=InnoDB;
CREATE TABLE tbl_Currency (Fld_Currency_ID INT PRIMARY KEY) ENGINE=InnoDB;
CREATE TABLE tbl_Division (Fld_Division_ID INT PRIMARY KEY) ENGINE=InnoDB;
CREATE TABLE tbl_Company_Details (
    id_tbl_company_Details INT AUTO_INCREMENT PRIMARY KEY,
    Fld_Company_ID INT NOT NULL,
    Fld_Company_Type_ID INT NOT NULL DEFAULT 2,
    Fld_Company_Address_Type INT NOT NULL DEFAULT 3,
    title_address VARCHAR(100),
    Fld_Company_Country VARCHAR(100),
    Fld_Company_City VARCHAR(100),
    Fld_Company_State VARCHAR(100),
    Fld_Company_Street VARCHAR(200),
    Fld_Company_ZipCode VARCHAR(32),
    Fld_Company_Phone VARCHAR(50),
    Fld_Company_Email VARCHAR(254),
    Fld_VAT_Nbr VARCHAR(64),
    UTC_timezone VARCHAR(64),
    Fld_Company_BAX_Contact INT,
    FOREIGN KEY (Fld_Company_ID) REFERENCES tb_company(Fld_Company_ID)
) ENGINE=InnoDB AUTO_INCREMENT=12;
CREATE TABLE tb_company_contact (
    id_company_contact INT AUTO_INCREMENT PRIMARY KEY,
    Fld_Linked_ID INT NOT NULL DEFAULT 0,
    Fld_Company_ID INT NOT NULL,
    Company_Old_Id INT NOT NULL DEFAULT 0,
    Fld_Contact_Name VARCHAR(160) NOT NULL,
    Fld_Contact_Email VARCHAR(254),
    Fld_Contact_Phone VARCHAR(50),
    Fld_Contact_Phone2 VARCHAR(50),
    Fld_Contact_Fax VARCHAR(50) NOT NULL DEFAULT '',
    Fld_Company_Mobile VARCHAR(50),
    Fld_Contact_Division_ID INT NOT NULL DEFAULT 0,
    Fld_Contact_Title VARCHAR(128),
    Fld_Contact_Remark TEXT NOT NULL DEFAULT '',
    status VARCHAR(16) NOT NULL DEFAULT 'Available',
    aci_contact INT,
    entry_date DATE,
    modified_date DATETIME,
    FOREIGN KEY (Fld_Company_ID) REFERENCES tb_company(Fld_Company_ID)
) ENGINE=InnoDB AUTO_INCREMENT=12;
INSERT INTO tb_company (Fld_Company_ID, Fld_Company_Name, cage_code) VALUES (7, 'Company A', 'AB123'), (9, 'Company B', 'AB123');
INSERT INTO tbl_Company_Type VALUES (2);
INSERT INTO tbl_Payment VALUES (2);
INSERT INTO tbl_Currency VALUES (3);
INSERT INTO tbl_Division VALUES (3);
INSERT INTO tbl_Company_Details (id_tbl_company_Details, Fld_Company_ID, Fld_Company_Country) VALUES (4, 7, 'Canada');
INSERT INTO tb_company_contact (id_company_contact, Fld_Company_ID, Fld_Contact_Name) VALUES (5, 7, 'Contact A');
CREATE TABLE tbl_Fleet (
    id_Fleet INT PRIMARY KEY AUTO_INCREMENT,
    Fld_Company_ID INT NOT NULL,
    Fld_Region INT,
    Fld_Engine VARCHAR(100),
    Fld_Unit VARCHAR(40),
    Fld_AC_ID INT,
    msn VARCHAR(80),
    immat VARCHAR(80)
) ENGINE=InnoDB AUTO_INCREMENT=12;
CREATE TABLE tbl_Region (Region_ID INT PRIMARY KEY) ENGINE=InnoDB;
CREATE TABLE tbl_Aircraft (Fld_AC_ID INT PRIMARY KEY) ENGINE=InnoDB;
CREATE TABLE tbl_Shipper (Fld_Shipper_ID INT PRIMARY KEY, Fld_Shipper_Text VARCHAR(100)) ENGINE=InnoDB;
CREATE TABLE tbl_Forwarder (
    Fld_Linked_ID INT PRIMARY KEY AUTO_INCREMENT,
    Fld_Company_ID INT NOT NULL,
    Fld_Shipper_ID INT NOT NULL
) ENGINE=InnoDB;
CREATE TABLE tbl_Competitor (
    Fld_Linked_ID INT PRIMARY KEY AUTO_INCREMENT,
    Fld_Company_ID INT NOT NULL,
    Fld_Competitor_ID INT NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=12;
INSERT INTO tbl_Region VALUES (1);
INSERT INTO tbl_Aircraft VALUES (2);
INSERT INTO tbl_Fleet (id_Fleet, Fld_Company_ID, Fld_Region, Fld_AC_ID) VALUES (4, 7, 1, 2);
