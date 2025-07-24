USE admin_panel;

-- Add new columns one by one
ALTER TABLE original_records ADD COLUMN IF NOT EXISTS class VARCHAR(50);
ALTER TABLE original_records ADD COLUMN IF NOT EXISTS date_join_ongc DATE;
ALTER TABLE original_records ADD COLUMN IF NOT EXISTS date_join_post DATE;
ALTER TABLE original_records ADD COLUMN IF NOT EXISTS eff_date_prom DATE;
ALTER TABLE original_records ADD COLUMN IF NOT EXISTS date_join_area DATE;

-- Modify existing columns one by one
ALTER TABLE original_records MODIFY COLUMN cpf VARCHAR(20) NOT NULL;
ALTER TABLE original_records MODIFY COLUMN name VARCHAR(100) NOT NULL;
ALTER TABLE original_records MODIFY COLUMN designation VARCHAR(100) NOT NULL;
ALTER TABLE original_records MODIFY COLUMN mobile VARCHAR(20) NOT NULL;
ALTER TABLE original_records MODIFY COLUMN section VARCHAR(100) NOT NULL;
ALTER TABLE original_records MODIFY COLUMN subsection VARCHAR(100);
ALTER TABLE original_records MODIFY COLUMN level VARCHAR(50);
ALTER TABLE original_records MODIFY COLUMN dob DATE;
ALTER TABLE original_records MODIFY COLUMN dor DATE; 