<?php session_start(); ?>
<style>
    body { font-family: 'Poppins', sans-serif; padding: 28px; background: #f4f7fb; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .card { background: white; padding: 22px; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .card h3 { color: #2a5298; margin-bottom: 8px; }
    .card p { font-size: 13px; color: #888; }
    h1 { margin-bottom: 20px; color: #333; font-size: 22px; }
</style>

<h1>Overview</h1>
<div class="cards">
    <div class="card"><h3>👤 Users</h3><p>Manage system users.</p></div>
    <div class="card"><h3>🧾 Patients</h3><p>View patient records.</p></div>
    <div class="card"><h3>📊 Reports</h3><p>Generate reports.</p></div>
</div>