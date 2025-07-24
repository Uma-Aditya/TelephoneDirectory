<?php
// Set headers for CSV file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="bulk_update_template.csv"');
header('Cache-Control: max-age=0');

// Only include the header row with new column order
$data = array(
    array(
        'cpf',
        'name',
        'designation',
        'mobile',
        'section',
        'subsection',
        'ext',
        'direct',
        'dob',
        'dor',
        'level',
        'seating_location',
        'did_number',
        'class',
        'date_join_ongc',
        'date_join_post',
        'eff_date_prom',
        'date_join_area',
        'date_prom'
    )
);

// Create output stream
$output = fopen('php://output', 'w');

// Write data
foreach ($data as $row) {
    fputcsv($output, $row);
}

// Close the output stream
fclose($output); 