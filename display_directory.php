<?php
require_once 'config/config.php';

// PDF paths
$pdfs = [
  'kakinada' => 'uploads/kakinada.pdf',
  'ogt' => 'uploads/ogt.pdf',
  'plqp' => 'uploads/plqp.pdf'
];

// Fetch all records with the specified sorting order (using correct column names)
$stmt = $pdo->query("SELECT * FROM original_records
  ORDER BY
    level DESC,
    date_prom ASC,
    date_join_post ASC,
    date_join_ongc ASC,
    dob ASC,
    cpf ASC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Kakinada Directory</title>
  <link rel="stylesheet" href="assets/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      background: #f4f6fb;
      font-family: 'Inter', 'Poppins', Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .sticky-header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: #fff;
      box-shadow: 0 2px 8px rgba(30,30,47,0.07);
      padding: 0;
    }
    .directory-header {
      background: #fff;
      padding: 24px 32px 12px 32px;
      border-bottom: 1px solid #eaeaea;
      display: flex;
      flex-direction: column;
      gap: 10px;
      align-items: flex-start;
    }
    .directory-header-row {
      display: flex;
      align-items: center;
      width: 100%;
      gap: 18px;
    }
    .directory-logo {
      width: 54px;
      height: 54px;
      border-radius: 12px;
      background: #e3eafc;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      font-weight: 700;
      color: #1976d2;
      margin-right: 18px;
    }
    .directory-header h1 {
      font-size: 1.7rem;
      font-weight: 700;
      color: #1a3c6e;
      margin: 0;
      letter-spacing: 0.5px;
    }
    .directory-header .announcement {
      background: #b71c1c;
      color: #fff;
      padding: 8px 16px;
      border-radius: 4px;
      font-size: 1rem;
      margin-bottom: 0.5rem;
      display: inline-block;
      font-weight: 500;
    }
    .directory-header .directory-links {
      margin: 0.5rem 0 0.5rem 0;
      font-size: 1.1rem;
    }
    .directory-header .directory-links a {
      color: #1a3c6e;
      text-decoration: underline;
      margin-right: 18px;
      font-weight: 500;
    }
    .directory-header .directory-links a.pdf-link {
      color: #42a5f5;
      text-decoration: none;
      font-weight: 600;
      margin-right: 12px;
      transition: color 0.2s, background 0.2s;
      background: #e3eafc;
      border-radius: 6px;
      padding: 6px 14px;
      display: inline-block;
      font-size: 1rem;
    }
    .directory-header .directory-links a.pdf-link:hover {
      color: #1976d2;
      background: #bbdefb;
      text-decoration: underline;
    }
    .directory-header .directory-links a.pdf-link:active,
    .directory-header .directory-links a.pdf-link:focus {
      color: #0d47a1;
      background: #90caf9;
      outline: none;
    }
    .directory-header .edit-btn {
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 10px 22px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      float: right;
      margin-top: 8px;
      transition: background 0.2s;
      box-shadow: 0 2px 8px rgba(25,118,210,0.08);
    }
    .directory-header .edit-btn:hover {
      background: #0d47a1;
    }
    .main-content-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 6px 32px rgba(30,30,47,0.10);
      padding: 32px 24px 24px 24px;
      margin: 36px auto 0 auto;
      max-width: 1750px;
    }
    .directory-table-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .directory-table-controls .entries {
      font-size: 15px;
      color: #888;
    }
    .directory-table-controls .search {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .utility-search-bar {
      width: 220px;
      padding: 10px 14px;
      border: 1.5px solid #cfd8dc;
      border-radius: 8px;
      font-size: 15px;
      margin-bottom: 0;
      margin-top: 0;
      background: #f7fafd;
      transition: border-color 0.2s;
    }
    .utility-search-bar:focus {
      border-color: #1976d2;
      outline: none;
      background: #fff;
    }
    #sectionDropdown {
      padding: 10px 14px;
      border-radius: 8px;
      border: 1.5px solid #cfd8dc;
      font-size: 15px;
      background: #f7fafd;
      min-width: 140px;
      margin-left: 4px;
      transition: border-color 0.2s;
    }
    #sectionDropdown:focus {
      border-color: #1976d2;
      outline: none;
      background: #fff;
    }
    .directory-table-container {
      background: none;
      border-radius: 10px;
      box-shadow: none;
      padding: 0;
      margin: 0;
      max-width: 100%;
      overflow-x: auto;
    }
    .directory-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      min-width: 1100px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(30,30,47,0.06);
    }
    .directory-table th, .directory-table td {
      padding: 13px 10px;
      text-align: left;
      border-bottom: 1px solid #eaeaea;
      font-size: 15px;
    }
    .directory-table th {
      background: #1e1e2f;
      color: #fff;
      font-weight: 600;
      text-align: center;
      letter-spacing: 0.5px;
      position: sticky;
      top: 0;
      z-index: 2;
    }
    .directory-table td {
      text-align: center;
      background: #fff;
    }
    .directory-table tr:nth-child(even) td {
      background: #f7fafd;
    }
    .directory-table tr:hover td {
      background: #e3eafc;
      transition: background 0.2s;
    }
    .section-badge {
      display: inline-block;
      background: linear-gradient(90deg, #1976d2 60%, #42a5f5 100%);
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      border-radius: 8px;
      padding: 7px 18px;
      margin: 18px 0 8px 0;
      box-shadow: 0 2px 8px rgba(25,118,210,0.08);
      letter-spacing: 0.5px;
    }
    .directory-table-footer {
      margin-top: 18px;
      color: #888;
      font-size: 15px;
      text-align: right;
    }
    @media (max-width: 1200px) {
      .main-content-card { padding: 10px 2px; }
      .directory-header { padding: 18px 8px 8px 8px; }
      .directory-table th, .directory-table td { font-size: 13px; }
    }
    @media (max-width: 700px) {
      .directory-header-row { flex-direction: column; align-items: flex-start; gap: 8px; }
      .directory-header h1 { font-size: 1.1rem; }
      .main-content-card { padding: 6px 2px; }
      .directory-table th, .directory-table td { font-size: 11px; padding: 7px 4px; }
    }
  </style>
</head>
<body>
  <div class="sticky-header">
    <div class="directory-header">
      <div class="directory-header-row">
        <div class="directory-logo">
          <span class="material-symbols-outlined">account_box</span>
        </div>
        <h1 style="flex:1;">EASTERN OFFSHORE and HPHT ASSET INTRANET PORTAL</h1>
        <button class="edit-btn" style="margin-left:auto;" onclick="window.location.href='php_template/public/index.php'">EDIT/NEW ENTRY REQUEST</button>
      </div>
      <div class="announcement">Announcement</div>
      <div>
        <span class="directory-links">
          <a href="<?= file_exists($pdfs['kakinada']) ? $pdfs['kakinada'] : '#' ?>" class="pdf-link" target="_blank">Kakinada Directory</a> ||
          <a href="<?= file_exists($pdfs['ogt']) ? $pdfs['ogt'] : '#' ?>" class="pdf-link" target="_blank">OGT Directory</a> ||
          <a href="<?= file_exists($pdfs['plqp']) ? $pdfs['plqp'] : '#' ?>" class="pdf-link" target="_blank">PLQP Directory</a>
        </span>
      </div>
    </div>
  </div>
  <div class="main-content-card">
    <div class="directory-table-container">
      <div class="directory-table-controls" style="justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="flex:1; min-width:180px; display:flex; align-items:center; gap:8px;">
          <label style="font-weight:500;">Section:</label>
          <select id="sectionDropdown"></select>
        </div>
        <div style="flex:1; min-width:180px; text-align:center; display:flex; align-items:center; justify-content:center;">
          <span class="entries" style="font-size:15px; color:#888;">Show
            <select id="showEntries" style="padding: 4px 8px; border-radius: 4px; border: 1.5px solid #cfd8dc; background: #f7fafd; margin:0 6px;">
              <option value="100">100</option>
              <option value="50">50</option>
              <option value="25">25</option>
            </select>
            entries
          </span>
        </div>
        <div style="flex:1; min-width:220px; display:flex; align-items:center; justify-content:flex-end; gap:8px;">
          <label for="utilitySearch" style="font-weight:500;">Search</label>
          <input type="text" id="utilitySearch" class="utility-search-bar" placeholder="Utility No., Name, Section..." onkeyup="renderSectionTable()">
        </div>
      </div>
      <div id="directoryTableWrapper"></div>
      <div class="directory-table-footer" id="directoryTableFooter">
        Showing 0 to 0 of 0 entries (filtered from <?= count($records) ?> total entries)
      </div>
    </div>
  </div>
  <script>
    // Store records in JS for filtering/search
    const records = <?php echo json_encode($records); ?>;
    function getUniqueSections(records) {
      const sections = new Set();
      records.forEach(r => sections.add(r.section || 'No Section'));
      return Array.from(sections).sort();
    }
    function populateSectionDropdown() {
      const dropdown = document.getElementById('sectionDropdown');
      const sections = getUniqueSections(records);
      dropdown.innerHTML = '<option value="all">All Sections</option>' +
        sections.map(s => `<option value="${encodeURIComponent(s)}">${s}</option>`).join('');
    }
    function renderSectionTable() {
      const section = decodeURIComponent(document.getElementById('sectionDropdown').value);
      const search = document.getElementById('utilitySearch').value.toLowerCase();
      let html = '<table class="directory-table" id="utilityTable">';
      html += `<thead><tr><th>CPF</th><th>NAME</th><th>DESIGNATION</th><th>MOBILE</th><th>SECTION</th><th>SEATING LOCATION</th><th>DID NUMBER</th><th>EXT(O)</th><th>DIRECT(O)</th></tr></thead><tbody>`;
      let shown = 0;
      records.forEach(r => {
        if (section !== 'all' && (r.section || 'No Section') !== section) return;
        const rowText = Object.values(r).join(' ').toLowerCase();
        if (search && !rowText.includes(search)) return;
        html += `<tr><td>${r.cpf||''}</td><td>${r.name||''}</td><td>${r.designation||''}</td><td>${r.mobile||''}</td><td>${r.section||''}</td><td>${r.seating_location||''}</td><td>${r.did_number||''}</td><td>${r.ext||''}</td><td>${r.direct||''}</td></tr>`;
        shown++;
      });
      if (shown === 0) {
        html += `<tr><td colspan='9' style='text-align:center; color:#888;'>No matching records found</td></tr>`;
      }
      html += '</tbody></table>';
      document.getElementById('directoryTableWrapper').innerHTML = html;
      document.getElementById('directoryTableFooter').innerText = `Showing 1 to ${shown} of ${shown} entries (filtered from ${records.length} total entries)`;
    }
    document.getElementById('utilitySearch').addEventListener('input', renderSectionTable);
    document.getElementById('sectionDropdown').addEventListener('change', renderSectionTable);
    window.onload = function() { populateSectionDropdown(); renderSectionTable(); };
  </script>
</body>
</html> 