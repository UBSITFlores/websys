<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php'); exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);

// Fetch Raw Schedule Data
$raw_sched = $studentFunc->getScheduleData($student_pk);

// --- LOGIC: PARSE "MWF 8:00-9:30" STRINGS ---
function parseTime($str) {
    $str = strtoupper($str);
    $days = [];
    // Regex to find days
    if (strpos($str, 'M') !== false) $days[] = 'Mon';
    if (strpos($str, 'T') !== false && strpos($str, 'TH') === false) $days[] = 'Tue'; // T but not TH
    if (strpos($str, 'W') !== false) $days[] = 'Wed';
    if (strpos($str, 'TH') !== false) $days[] = 'Thu';
    if (strpos($str, 'F') !== false) $days[] = 'Fri';
    if (strpos($str, 'S') !== false) $days[] = 'Sat';

    // Regex to find time range
    preg_match_all('/\d{1,2}:\d{2}/', $str, $matches);
    
    if(count($matches[0]) >= 2) {
        return [
            'days'  => $days, 
            'start' => strtotime($matches[0][0]), 
            'end'   => strtotime($matches[0][1])
        ];
    }
    return null;
}

$map = [];
foreach($raw_sched as $c) {
    $info = parseTime($c['schedule_time']);
    if($info) {
        foreach($info['days'] as $d) {
            $map[$d][] = [
                'start' => $info['start'],
                'end'   => $info['end'],
                'code'  => $c['code'],
                'room'  => $c['room']
            ];
        }
    }
}

// Generate Time Slots (7AM to 6PM)
$times = [];
$t = strtotime("07:00");
$end_time = strtotime("18:00");
while($t <= $end_time) {
    $times[] = $t;
    $t = strtotime("+30 minutes", $t); // 30 min intervals
}
$week_days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>

<style>
    .sched-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; max-width: 1100px; margin: 0 auto; }
    .sched-header { background: #002D72; color: white; padding: 20px; }
    .sched-header h2 { margin: 0; font-size: 1.5rem; }
    
    .sched-grid { width: 100%; border-collapse: collapse; }
    .sched-grid th { background: #f8f9fa; color: #555; padding: 10px; border: 1px solid #ddd; width: 14%; }
    .sched-grid td { border: 1px solid #eee; height: 35px; padding: 0; position: relative; }
    
    .time-col { background: #f8f9fa; font-size: 0.8rem; font-weight: bold; color: #888; text-align: center; vertical-align: middle; width: 80px; }
    
    .class-block {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        color: #0d47a1;
        font-size: 0.8rem;
        padding: 5px;
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 10;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .class-room { font-size: 0.7rem; color: #666; margin-top: 2px; }
    
    /* Fallback List for Mobile */
    .mobile-list { display: none; padding: 20px; }
    .m-item { border-bottom: 1px solid #eee; padding: 10px 0; }
    @media (max-width: 768px) {
        .sched-grid { display: none; }
        .mobile-list { display: block; }
    }
</style>

<div class="sched-card">
    <div class="sched-header">
        <h2>My Class Schedule</h2>
        <p style="margin:5px 0 0; opacity:0.8;">Visual timetable for your current enrollment.</p>
    </div>

    <table class="sched-grid">
        <thead>
            <tr>
                <th>Time</th>
                <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($times as $slot): ?>
            <tr>
                <td class="time-col"><?php echo date("g:i A", $slot); ?></td>
                
                <?php foreach($week_days as $day): ?>
                    <td style="position:relative;">
                        <?php 
                        if(isset($map[$day])) {
                            foreach($map[$day] as $cls) {
                                // Check if this slot falls within the class time
                                if($slot >= $cls['start'] && $slot < $cls['end']) {
                                    // Only render content on the FIRST slot of the class to avoid stacking text
                                    if($slot == $cls['start']) {
                                        // Calculate height: (Duration / 30mins) * 100%
                                        $duration = ($cls['end'] - $cls['start']) / 60; 
                                        $row_span = $duration / 30;
                                        $h_percent = $row_span * 100; // Rough height calc
                                        
                                        echo "<div class='class-block' style='height:{$h_percent}%; z-index:20;'>";
                                        echo "<strong>" . htmlspecialchars($cls['code']) . "</strong>";
                                        echo "<span class='class-room'>" . htmlspecialchars($cls['room']) . "</span>";
                                        echo "</div>";
                                    }
                                }
                            }
                        }
                        ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mobile-list">
        <?php foreach($raw_sched as $s): ?>
            <div class="m-item">
                <strong style="color:#002D72;"><?php echo htmlspecialchars($s['code']); ?></strong>
                <br>
                <?php echo htmlspecialchars($s['description']); ?>
                <br>
                <small>🕒 <?php echo htmlspecialchars($s['schedule_time']); ?> | 📍 <?php echo htmlspecialchars($s['room']); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>