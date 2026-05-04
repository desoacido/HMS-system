<?php
session_start();
include __DIR__ . '/db.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Immunization Screening</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: Poppins;
    background: #f4f7fb;
    padding: 30px;
}

.container {
    max-width: 800px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 12px;
}

h2 { margin-bottom: 20px; }

label {
    font-weight: 600;
    display: block;
    margin-top: 12px;
}

input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

button {
    margin-top: 20px;
    padding: 12px;
    width: 100%;
    border: none;
    background: #28a745;
    color: white;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    background: #218838;
}
</style>
</head>

<body>

<div class="container">

<h2>💉 Immunization / Patient Screening Form</h2>

<form method="POST" action="save_immunization.php">

<!-- BASIC INFO -->
<div class="row">
    <div>
        <label>Visit ID</label>
        <input type="number" name="visit_id" required>
    </div>

    <div>
        <label>Patient ID</label>
        <input type="number" name="patient_id" required>
    </div>
</div>

<!-- VITALS -->
<div class="row">
    <div>
        <label>Temperature</label>
        <input type="text" name="temperature">
    </div>

    <div>
        <label>Blood Pressure</label>
        <input type="text" name="blood_pressure">
    </div>

    <div>
        <label>Heart Rate</label>
        <input type="text" name="heart_rate">
    </div>

    <div>
        <label>Weight</label>
        <input type="text" name="weight">
    </div>
</div>

<!-- CONDITIONS -->
<label>Has Allergy?</label>
<select name="has_allergy">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<label>Allergy Notes</label>
<textarea name="allergy_notes"></textarea>

<label>Has Fever?</label>
<select name="has_fever">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<label>Has Acute Illness?</label>
<select name="has_acute_illness">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<label>Recent Vaccine</label>
<select name="recent_vaccine">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<label>Is Pregnant?</label>
<select name="is_pregnant">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<label>Has Autoimmune Disease?</label>
<select name="has_autoimmune">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<label>On Blood Thinners?</label>
<select name="on_blood_thinners">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<label>Is Immunocompromised?</label>
<select name="is_immunocompromised">
    <option value="No">No</option>
    <option value="Yes">Yes</option>
</select>

<!-- VACCINE -->
<div class="row">
    <div>
        <label>Vaccine Name</label>
        <input type="text" name="vaccine_name">
    </div>

    <div>
        <label>Dose Number</label>
        <select name="dose_number">
            <option value="1st Dose">1st Dose</option>
            <option value="2nd Dose">2nd Dose</option>
            <option value="Booster">Booster</option>
        </select>
    </div>
</div>

<button type="submit">💉 Save Immunization Record</button>

</form>

</div>

</body>
</html>