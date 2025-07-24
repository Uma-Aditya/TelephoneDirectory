-- Add seating_location column to original_records table
ALTER TABLE original_records ADD COLUMN IF NOT EXISTS seating_location VARCHAR(200);

-- Add seating_location column to requests table
ALTER TABLE requests ADD COLUMN IF NOT EXISTS seating_location VARCHAR(200); 