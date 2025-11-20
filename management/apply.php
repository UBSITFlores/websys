<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['consent']) && $_POST['consent'] === 'agree') {
        header("Refresh:3; url=process.php");
        $consent_given = true;
    } else {
        $consent_given = false;
        $error_message = "You must agree to the privacy policy to continue.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Consent Agreement</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}
.consent-container {
    background-color: #ffffff;
    border: 1px solid #c8c9c7;
    padding: 30px 40px;
    max-width: 600px;
    width: 90%;
    box-shadow: 0 4px 12px rgba(0, 61, 165, 0.2);
    border-radius: 8px;
    text-align: center;
}
h2 {
    color: #003DA5;
    margin-bottom: 25px;
    font-weight: bold;
    font-size: 28px;
}
.consent-text {
    font-size: 15px;
    line-height: 1.6;
    color: #444444;
    margin-bottom: 25px;
    text-align: left;
    user-select: text;
}
label {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    margin-bottom: 25px;
    cursor: pointer;
    user-select: none;
    gap: 10px;
}
input[type="checkbox"] {
    width: 18px;
    height: 18px;
}
.button-container {
    text-align: center;
}
button {
    background-color: #003DA5;
    color: white;
    border: none;
    padding: 13px 30px;
    font-size: 17px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
button:hover {
    background-color: #002a7a;
}
.error-message {
    color: #b00020;
    font-weight: 600;
    margin-bottom: 20px;
}
.success-message {
    color: #003DA5;
    font-weight: 600;
    margin-bottom: 20px;
}
</style>
</head>
<body>
<div class="consent-container">
    <h2>Consent Agreement</h2>
    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php elseif (!empty($consent_given) && $consent_given): ?>
        <div class="success-message">Thank you for your consent. You will be redirected shortly...</div>
    <?php endif; ?>
    <?php if (empty($consent_given) || !$consent_given): ?>
    <form method="post" action="">
        <div class="consent-text">
            I have read and understood the Privacy Policy of Saint Louis School of Pacdal, Inc.<br><br>
            By clicking the Agree button, I give my consent for the processing of my personal data by SLU Pacdal for the purposes stated.<br><br>
            I am aware of my right to withdraw consent at any time or object to the processing as provided under applicable data protection laws and school policies.
        </div>
        <label>
            <input type="checkbox" name="consent" value="agree" <?= (isset($_POST['consent']) && $_POST['consent'] === 'agree') ? 'checked' : '' ?> />
            I Agree
        </label>
        <div class="button-container">
            <button type="submit">Agree</button>
        </div>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
