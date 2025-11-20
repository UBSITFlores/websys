
<?php
$tracks = ['kinder', 'junior high school', 'senior high school'];
$currentYear = date('Y');
?>
<h2>Enroll New Student</h2>
<form id="enrollForm" method="POST" autocomplete="off">
    <label>First Name <input type="text" name="fname" required></label><br>
    <label>Middle Name <input type="text" name="mname" required></label><br>
    <label>Last Name <input type="text" name="lname" required></label><br>
    <label>Date Enrolled <input type="date" name="date_enrolled" value="<?php echo date('Y-m-d'); ?>" required></label><br>
    <label>Track
        <select name="track" required>
            <option value="">Select track</option>
            <?php foreach ($tracks as $trk): ?>
                <option value="<?php echo $trk; ?>"><?php echo ucfirst($trk); ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <button type="submit">Register Student</button>
    <div id="enroll-msg" style="margin-top:8px;color:#d35400;font-weight:600"></div>
</form>
<script>
document.getElementById('enrollForm').onsubmit = async function(e) {
    e.preventDefault();
    let f = e.target;
    let data = new FormData(f);
    let r = await fetch('/portal/management/register_student.php', { method: 'POST', body: data });
    let msg = await r.text();
    document.getElementById('enroll-msg').innerHTML = msg;
    if (msg.includes('successfully')) f.reset();
};
</script>
