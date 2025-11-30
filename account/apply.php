<?php 
    include("../functions/account.php");
    $account = new account();

    $showSelection = isset($_POST['agree']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacdal Portal - Consent Agreement</title>
    <link rel="stylesheet" href="apply.css">
</head>
<body>
    <div class="container"<?php if($showSelection) echo ' style="filter: blur(2px);"'; ?>>
        <div class="consent-card">
            <div class="consent-header">
                <span class="consent-title">Consent Agreement</span>
            </div>
            <div class="consent-warning">
                <span class="warning-icon">&#9888;</span>
                <span class="warning-text">Applicants are advised to read the consent agreement briefly before agreeing.</span>
            </div>
            <div class="consent-body">
                <p>
                    I have read and understood the Privacy Policy of Saint Louis School of Pacdal, Inc.<br><br>
                    By clicking the Agree button, I give my consent for the processing of my personal data by SLU Pacdal for the purposes stated.<br>
                    I am aware of my right to withdraw consent at any time or object to the processing as provided under applicable data protection laws and school policies.<br>
                </p>
            </div>
            <div class="consent-action">
                <form method="POST">
                    <button type="submit" name="disagree" class="consent-btn disagree">Disagree</button>
                    <button type="submit" name="agree" class="consent-btn agree">Agree</button>
                </form>
            </div>
        </div>
    </div>

    <?php if($showSelection): ?>
    <div class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <span class="modal-title">Student Directory</span>
        </div>
        <div class="modal-body">
          <form method="POST" action="process_selection.php">
            <div class="modal-row">
              <div class="modal-group">
                <label class="modal-label" for="grade_track">Select Track:</label>
                <select name="grade_track" id="grade_track" required>
                  <option value="Kinder">Kindergarten</option>
                  <option value="Highschool">High School</option>
                  <option value="SeniorHigh">Senior High School</option>
                </select>
              </div>
            </div>
            <div class="modal-row">
              <div class="modal-group">
                <label for="department" class="modal-label">Department:</label>
                <select name="department" id="department" required>
                  <option value="Kinder">Kinder</option>
                </select>
              </div>
            </div>
            <div class="modal-row modal-actions">
              <button type="submit" class="modal-btn agree">Continue</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src="tracks.js"></script>
    <?php endif; ?>
</body>
</html>
