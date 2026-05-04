<?php
session_start();
include __DIR__ . '/db.php';

// Inihahanda ang query
$stmt = $conn->prepare("
    SELECT p.id, p.firstname, p.lastname, p.qr_code, COUNT(v.id) as visit_count
    FROM patients p
    LEFT JOIN visits v ON v.patient_id = p.id
    GROUP BY p.id
    ORDER BY p.lastname ASC
");

$stmt->execute();

// Para sa MySQLi, ganito ang pagkuha ng resulta:
$result = $stmt->get_result();
$patients = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#f4f7fb; padding:40px; }

        .container { max-width: 1000px; margin: auto; }

        .page-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }
        h2 { color:#1a7a4a; letter-spacing: -0.5px; }

        .search-box {
            display:flex;
            align-items:center;
            background:white;
            border-radius:12px;
            padding: 5px 15px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            width:300px;
        }
        .search-box input {
            border:none; outline:none;
            padding:10px;
            width:100%;
            font-size:14px;
        }

        /* Grid Layout */
        .list {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
            gap:20px;
        }

        /* Minimalist Card */
        .card {
            background:white; 
            padding:20px;
            border-radius:16px;
            box-shadow:0 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 1px solid #eee;
        }
        .card:hover { transform:translateY(-5px); box-shadow:0 10px 25px rgba(0,0,0,0.1); }

        .name { 
            font-size:18px; 
            font-weight:600; 
            color:#2c3e50; 
            margin-bottom: 8px;
        }

        .badge {
            font-size:10px;
            text-transform: uppercase;
            padding:4px 12px;
            border-radius:50px;
            font-weight:700;
            margin-bottom: 15px;
        }
        .badge-new { background:#e8f5e9; color:#2e7d32; }
        .badge-returning { background:#e3f2fd; color:#1565c0; }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 10px;
            width: 100%;
            margin-top: 10px;
        }
        .btn-view {
            flex: 2;
            background: #1a7a4a;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-view:hover { background: #145f39; }

        .btn-qr {
            flex: 1;
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-qr:hover { background: #e9ecef; }

        .no-result { grid-column: 1/-1; text-align: center; padding: 50px; color: #999; }

        /* Modal styling nanatili pero nilinis */
        .modal-bg {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.7);
            justify-content:center; align-items:center; z-index:999;
        }
        .modal-bg.show { display:flex; }
        .modal {
            background:white; padding:30px; border-radius:20px;
            text-align:center; max-width:350px; width:90%;
        }
        .qr-big { width:220px; height:220px; margin: 20px 0; border: 1px solid #eee; }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <div>
            <h2>Patients</h2>
            <p style="color:#888; font-size:14px;">Total: <?= count($patients) ?> registered</p>
        </div>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search by name..." oninput="filterCards()">
            <span>🔍</span>
        </div>
    </div>

    <div class="list" id="cardList">
        <?php foreach ($patients as $p): 
            $fullName = htmlspecialchars($p['firstname'] . ' ' . $p['lastname']);
        ?>
        <div class="card" data-name="<?= strtolower($fullName) ?>">
            
            <div class="badge <?= $p['visit_count'] <= 1 ? 'badge-new' : 'badge-returning' ?>">
                <?= $p['visit_count'] <= 1 ? '● New Patient' : '● Returning' ?>
            </div>

            <div class="name"><?= $fullName ?></div>
            
            <div class="actions">
                <!-- VIEW PROFILE BUTTON -->
                <a href="patientprofile.php?id=<?= $p['id'] ?>" class="btn-view">View Profile</a>
                
                <!-- QR BUTTON -->
                <?php if (!empty($p['qr_code'])): ?>
                    <button class="btn-qr" onclick="viewQR('<?= $p['id'] ?>', '<?= $fullName ?>', '<?= $p['id'] ?>')">🔲</button>
                <?php else: ?>
                    <button class="btn-qr" onclick="window.location='generate_qr.php?id=<?= $p['id'] ?>'" title="Generate QR">➕</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="no-result" id="noResult" style="display:none;">
            <p>No patients found with that name.</p>
        </div>
    </div>
</div>

<!-- QR MODAL -->
<div class="modal-bg" id="qrModal">
    <div class="modal">
        <h3 id="modalName" style="margin-bottom:5px;"></h3>
        <p id="modalId" style="font-size:12px; color:#888;"></p>
        <img id="modalQR" src="" class="qr-big">
        <div style="display:flex; gap:10px;">
            <button onclick="closeModal()" style="flex:1; padding:10px; border:none; border-radius:8px; cursor:pointer;">Close</button>
            <a id="downloadBtn" href="" download style="flex:1; background:#1a7a4a; color:white; text-decoration:none; padding:10px; border-radius:8px; font-size:13px; display:inline-block;">Download</a>
        </div>
    </div>
</div>

<script>
function filterCards() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(input)) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('noResult').style.display = visibleCount === 0 ? 'block' : 'none';
}

function viewQR(patientId, name, id) {
    // Gamitin ang API para gumawa ng QR — walang file saving
    const baseUrl = "https://hms-system-1-l6jn.onrender.com";
    const qrData = encodeURIComponent(baseUrl + "/patientprofile.php?id=" + patientId);
    const qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + qrData;

    document.getElementById('modalQR').src = qrUrl;
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalId').textContent = 'ID: #' + id;
    document.getElementById('downloadBtn').href = qrUrl;
    document.getElementById('qrModal').classList.add('show');
}
function closeModal() {
    document.getElementById('qrModal').classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == document.getElementById('qrModal')) {
        closeModal();
    }
}
</script>

</body>
</html>
