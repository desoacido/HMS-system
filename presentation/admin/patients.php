<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

// fetch patients
$stmt = $conn->query("SELECT * FROM patients ORDER BY id ASC");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($patients);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patients List</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }
    body {
        background: #f4f7fb;
        padding: 30px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    h2 {
        color: #333;
    }
    .total-count {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
    }
    .total-count span {
        font-weight: 600;
        color: #4facfe;
    }
    .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }
    .search-bar {
        padding: 8px 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        width: 280px;
        font-family: 'Poppins', sans-serif;
        outline: none;
    }
    .search-bar:focus {
        border-color: #4facfe;
    }
    .btn-add {
        background: #4facfe;
        color: white;
        padding: 8px 18px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-add:hover {
        background: #3a8fdd;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px;
        text-align: left;
        font-size: 14px;
    }
    th {
        background: #4facfe;
        color: white;
    }
    tr:nth-child(even) {
        background: #f9f9f9;
    }
    tr:hover {
        background: #eef6ff;
    }
    .btn-edit {
        background: #f0ad4e;
        color: white;
        padding: 5px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-edit:hover {
        background: #d99035;
    }
    .btn-view {
        background: #5cb85c;
        color: white;
        padding: 5px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-view:hover {
        background: #449d44;
    }
    .no-results {
        text-align: center;
        padding: 20px;
        color: #999;
        display: none;
    }
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin-top: 20px;
    }
    .pagination button {
        padding: 6px 12px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
    }
    .pagination button.active {
        background: #4facfe;
        color: white;
        border-color: #4facfe;
    }
    .pagination button:hover:not(.active) {
        background: #eef6ff;
    }
    .back {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #555;
    }
</style>
</head>
<body>

<div class="header">
    <h2>Patients List</h2>
   <a href="../admin/dashboard.php" class="back">⬅ Back to Dashboard</a>
</div>

<p class="total-count">Total Patients: <span id="displayCount"><?= $total ?></span></p>

<div class="controls">
    <input type="text" class="search-bar" id="searchInput" placeholder="🔍 Search by name, address, contact...">
    
</div>

<table id="patientsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Birthdate</th>
            <th>Age</th>
            <th>Address</th>
            <th>Contact</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="tableBody">
        <?php foreach ($patients as $p): ?>
        <?php
            $age = 'N/A';
            if (!empty($p['birthdate'])) {
                $birth = new DateTime($p['birthdate']);
                $today = new DateTime();
                $age = $birth->diff($today)->y;
            }
        ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['first_name'] ?></td>
            <td><?= $p['last_name'] ?></td>
            <td><?= $p['birthdate'] ?></td>
            <td><?= $age ?></td>
            <td><?= $p['address'] ?></td>
            <td><?= $p['contact_number'] ?></td>
            <td>
               
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="no-results" id="noResults">No patients found.</p>

<!-- Pagination -->
<div class="pagination" id="pagination"></div>



<script>
    const rowsPerPage = 10;
    let currentPage = 1;
    let filteredRows = [];

    const tableBody = document.getElementById('tableBody');
    const allRows = Array.from(tableBody.querySelectorAll('tr'));
    const searchInput = document.getElementById('searchInput');
    const noResults = document.getElementById('noResults');
    const displayCount = document.getElementById('displayCount');

    function renderTable() {
        allRows.forEach(row => row.style.display = 'none');

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const pageRows = filteredRows.slice(start, end);

        pageRows.forEach(row => row.style.display = '');

        noResults.style.display = filteredRows.length === 0 ? 'block' : 'none';
        displayCount.textContent = filteredRows.length;

        renderPagination();
    }

    function renderPagination() {
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        if (totalPages <= 1) return;

        // Prev button
        const prev = document.createElement('button');
        prev.textContent = '← Prev';
        prev.disabled = currentPage === 1;
        prev.onclick = () => { currentPage--; renderTable(); };
        pagination.appendChild(prev);

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            if (i === currentPage) btn.classList.add('active');
            btn.onclick = () => { currentPage = i; renderTable(); };
            pagination.appendChild(btn);
        }

        // Next button
        const next = document.createElement('button');
        next.textContent = 'Next →';
        next.disabled = currentPage === totalPages;
        next.onclick = () => { currentPage++; renderTable(); };
        pagination.appendChild(next);
    }

    // Search
    searchInput.addEventListener('input', function () {
        const keyword = this.value.toLowerCase();
        filteredRows = allRows.filter(row =>
            row.textContent.toLowerCase().includes(keyword)
        );
        currentPage = 1;
        renderTable();
    });

    // Init
    filteredRows = [...allRows];
    renderTable();
</script>

</body>
</html>
