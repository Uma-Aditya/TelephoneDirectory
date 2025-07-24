-- Update the original_records table to match Excel structure
ALTER TABLE original_records
ADD COLUMN IF NOT EXISTS date_join_ongc DATE,
ADD COLUMN IF NOT EXISTS date_join_post DATE,
ADD COLUMN IF NOT EXISTS eff_date_prom DATE,
ADD COLUMN IF NOT EXISTS date_join_area DATE; 