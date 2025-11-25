<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    echo "Session Expired."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$account_id = $_SESSION['ACCOUNTID'];

$stmt = $pdo->prepare("SELECT id FROM account WHERE account_id = :aid");
$stmt->execute([':aid' => $account_id]);
$inst_id = $stmt->fetchColumn();

$sql = "SELECT * FROM sections WHERE instructor_id = ? ORDER BY schedule_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$inst_id]);
$my_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getClassData($str) {
    $str = strtoupper($str);
    $days = [];
    if (strpos($str, 'M') !== false) $days[] = 'Mon';
    if (strpos($str, 'T') !== false && strpos($str, 'TH') === false) $days[] = 'Tue';
    if (strpos($str, 'W') !== false) $days[] = 'Wed';
    if (strpos($str, 'TH') !== false) $days[] = 'Thu';
    if (strpos($str, 'F') !== false) $days[] = 'Fri';
    if (strpos($str, 'S') !== false) $days[] = 'Sat';

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
foreach($my_classes as $c) {
    $info = getClassData($c['schedule_time']);
    if($info) {
        foreach($info['days'] as $d) {
            $map[$d][] = [
                'start' => $info['start'],
                'end'   => $info['end'],
                'name'  => $c['code'],
                'room'  => $c['room']
            ];
        }
    }
}
$times = [];
$t = strtotime("07:00");
while($t <= strtotime("18:00")) {
    $times[] = $t;
    $t = strtotime("+30 minutes", $t);
}
$week = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>

<style>
    .sched-table { width: 100%; border-collapse: collapse; background: #fff; }
    .sched-table th { background: #198754; color: white; padding: 10px; text-align: center; border: 1px solid #146c43; }
    .sched-table td { border: 1px solid #eee; padding: 0; height: 40px; text-align: center; vertical-align: middle; }
    
    .time-col { background: #f8f9fa; font-weight: bold; color: #555; width: 100px; border-right: 2px solid #ddd !important; }
    .class-start {
        background-color: #d1e7dd; 
        border-left: 4px solid #198754;
        color: #0f5132;
        font-weight: bold;
        font-size: 0.85rem;
        padding: 5px;
        border-bottom: none;
    }
    .class-mid {
        background-color: #d1e7dd;
        border-left: 4px solid #198754;
        border-top: none;
        border-bottom: none;
    }
    
    .room-text { display: block; font-size: 0.75rem; font-weight: normal; color: #146c43; }
</style>

<h2 style="color:#198754; border-bottom:2px solid #eee; padding-bottom:10px; margin-top:0;">My Weekly Schedule</h2>

<div style="overflow-x:auto; box-shadow:0 2px 5px rgba(0,0,0,0.05); border-radius:8px;">
    <table class="sched-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($times as $time_val): ?>
            <tr>
                <td class="time-col"><?php echo date("g:i A", $time_val); ?></td>

                <?php foreach($week as $day): ?>
                    <?php 
                        $cell_class = "";
                        $content = "";

                        if(isset($map[$day])) {
                            foreach($map[$day] as $cls) {
                                if($time_val == $cls['start']) {
                                    $cell_class = "class-start";
                                    $content = $cls['name'] . "<span class='room-text'>" . $cls['room'] . "</span>";
                                    break;
                                }
                                elseif($time_val > $cls['start'] && $time_val < $cls['end']) {
                                    $cell_class = "class-mid";
                                    $content = "";
                                    break;
                                }
                            }
                        }
                    ?>
                    
                    <td class="<?php echo $cell_class; ?>">
                        <?php echo $content; ?>
                    </td>

                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>