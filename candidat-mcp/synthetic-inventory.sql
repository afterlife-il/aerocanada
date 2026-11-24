CREATE DATABASE synthetic_inventory;
USE synthetic_inventory;

CREATE TABLE tb_company (
  Fld_Company_ID INT PRIMARY KEY,
  Fld_Company_Name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE tbl_Aircraft (Fld_AC_ID INT PRIMARY KEY, Fld_AC_Model VARCHAR(100)) ENGINE=InnoDB;
CREATE TABLE tbl_Currency (Fld_Currency_ID INT PRIMARY KEY, Fld_Currency_Text VARCHAR(10)) ENGINE=InnoDB;
CREATE TABLE tbl_Condition (Fld_Condition_ID INT PRIMARY KEY, Fld_Condition_Text VARCHAR(100)) ENGINE=InnoDB;
CREATE TABLE tbl_Release (Fld_Release_ID INT PRIMARY KEY, Fld_Release_Text VARCHAR(100)) ENGINE=InnoDB;
CREATE TABLE tbl_Status (Fld_Status_ID INT PRIMARY KEY, Fld_Status_Text VARCHAR(100)) ENGINE=InnoDB;
CREATE TABLE tbl_Stock_Location (Fld_Stock_Location_ID INT PRIMARY KEY, Fld_Stock_Location_Text VARCHAR(100)) ENGINE=InnoDB;
CREATE TABLE tbl_Warehouse_Location (Id INT PRIMARY KEY, Fld_Location VARCHAR(100)) ENGINE=InnoDB;

CREATE TABLE tbl_Parts (
  Fld_Part_ID INT AUTO_INCREMENT PRIMARY KEY,
  Fld_Part_Nbr VARCHAR(80) NOT NULL UNIQUE,
  Fld_Part_Desc VARCHAR(1000),
  Fld_Part_MFG INT,
  Fld_AC_ID INT,
  Fld_Part_List_Price DECIMAL(13,3),
  Fld_Part_Price_Currency_ID INT,
  Fld_Remark TEXT,
  alt_pn VARCHAR(500),
  ata_chapter VARCHAR(50),
  cage_code VARCHAR(50),
  moq INT,
  oem_lead_time VARCHAR(100),
  core_value DECIMAL(13,3),
  id_currency_core_value INT,
  Fld_Add_PN_Date DATETIME,
  aci_contact_entry INT,
  wanted TINYINT NOT NULL DEFAULT 0,
  status VARCHAR(10) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE tbl_docs_attachment_pn (
  id_docs_attachment_pn INT PRIMARY KEY,
  name VARCHAR(100),
  pn_id INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE tbl_RFQ_1 (Fld_RFQ_ID VARCHAR(80), pn_rfq VARCHAR(80), date DATE) ENGINE=InnoDB;
CREATE TABLE tbl_price_list (
  id INT PRIMARY KEY, CMM_Reference VARCHAR(100), pn VARCHAR(80), Description VARCHAR(100), brand VARCHAR(100),
  model VARCHAR(100), Sales_Unit VARCHAR(30), Lead_Time_Days INT, MOQ INT, Unit_price DECIMAL(13,3),
  Currency VARCHAR(10), id_company INT
) ENGINE=InnoDB;

CREATE TABLE tbl_Stock (
  Fld_Stock_ID INT AUTO_INCREMENT PRIMARY KEY,
  Fld_Part_ID INT NOT NULL,
  Fld_Part_SN VARCHAR(120),
  Fld_Qty INT,
  Fld_Physical_Stock VARCHAR(20),
  Fld_Condition_ID INT,
  Fld_Release_ID INT,
  Fld_Tag_Info_ID INT,
  Fld_Tag_Date DATE,
  Fld_Traceability_ID INT,
  Fld_Supplier_ID INT,
  Fld_Owner_ID INT,
  Fld_Warehouse_Location VARCHAR(200),
  Fld_Entry_Date DATE,
  Fld_Status_ID INT,
  Fld_Status_Date DATE,
  Fld_Stock_Location_ID INT,
  Fld_Warehouse_Location_ID INT,
  Fld_External_Location INT,
  Fld_Min_Qty INT,
  Fld_Shelf_Life_Limit VARCHAR(100),
  Fld_Part_Price DECIMAL(13,3),
  Fld_Price_Currency_ID INT,
  Fld_Stock_Remark TEXT,
  Fld_Sales_Remark TEXT
) ENGINE=InnoDB;
CREATE TABLE tbl_Stock_external (
  Fld_Stock_externe_ID INT AUTO_INCREMENT PRIMARY KEY,
  Fld_Part_ID INT NOT NULL,
  Fld_Part_SN VARCHAR(120),
  Fld_Qty INT,
  Fld_Physical_Stock VARCHAR(100),
  Fld_Condition_ID INT,
  Fld_Release_ID INT,
  Fld_Tag_Info_ID INT,
  Fld_Tag_Date DATE,
  Fld_Traceability_ID INT,
  Fld_Supplier_ID INT,
  Fld_Company_ID INT,
  Fld_Warehouse_Location VARCHAR(200),
  Fld_Entry_Date DATE,
  Fld_Part_Price DECIMAL(13,3),
  Fld_Price_Currency_ID INT,
  Fld_Stock_Remark TEXT,
  Fld_Sales_Remark TEXT,
  Fld_Publish VARCHAR(10),
  status VARCHAR(10)
) ENGINE=InnoDB;

INSERT INTO tb_company VALUES (1, 'Synthetic Company');
INSERT INTO tbl_Warehouse_Location VALUES (1, 'Synthetic shelf');
INSERT INTO tbl_Status VALUES (1, 'Synthetic status');
INSERT INTO tbl_Parts (Fld_Part_ID, Fld_Part_Nbr, alt_pn, status) VALUES (1,'SYNTH-PN','ALT_%!','Available');
INSERT INTO tbl_docs_attachment_pn VALUES (1,'Synthetic certificate',1);
INSERT INTO tbl_Stock (Fld_Stock_ID, Fld_Part_ID, Fld_Part_SN, Fld_Qty) VALUES (1,1,'PREEXISTING',1);
INSERT INTO tbl_RFQ_1 VALUES ('2026-11-23-120001','SYNTH-PN','2026-11-23'), ('2026-11-23-120002','SYNTH-PN','2026-11-23');
INSERT INTO tbl_price_list VALUES (1,'CMM-SYNTH','SYNTH-PN','Synthetic item','Synthetic brand','Synthetic model','EA',7,1,125.25,'CAD',1);
CREATE TABLE checks (ok BOOLEAN NOT NULL CHECK (ok = TRUE)) ENGINE=InnoDB;
