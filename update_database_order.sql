-- Reorder columns in original_records table to match the new order
ALTER TABLE original_records
MODIFY COLUMN cpf VARCHAR(20) NOT NULL AFTER id,
MODIFY COLUMN name VARCHAR(100) NOT NULL AFTER cpf,
MODIFY COLUMN designation VARCHAR(100) NOT NULL AFTER name,
MODIFY COLUMN mobile VARCHAR(20) NOT NULL AFTER designation,
MODIFY COLUMN section VARCHAR(100) NOT NULL AFTER mobile,
MODIFY COLUMN subsection VARCHAR(100) NULL AFTER section,
MODIFY COLUMN ext VARCHAR(20) NULL AFTER subsection,
MODIFY COLUMN direct VARCHAR(20) NULL AFTER ext,
MODIFY COLUMN dob DATE NULL AFTER direct,
MODIFY COLUMN dor DATE NULL AFTER dob,
MODIFY COLUMN level VARCHAR(50) NULL AFTER dor,
MODIFY COLUMN seating_location VARCHAR(200) NULL AFTER level,
MODIFY COLUMN did_number VARCHAR(20) NULL AFTER seating_location,
MODIFY COLUMN class VARCHAR(50) NULL AFTER did_number,
MODIFY COLUMN date_join_ongc DATE NULL AFTER class,
MODIFY COLUMN date_join_post DATE NULL AFTER date_join_ongc,
MODIFY COLUMN eff_date_prom DATE NULL AFTER date_join_post,
MODIFY COLUMN date_join_area DATE NULL AFTER eff_date_prom,
MODIFY COLUMN date_prom DATE NULL AFTER date_join_area;
-- entry_date and last_modified remain at the end 