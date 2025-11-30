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

<h2 class="schedule-title">My Schedule</h2>

<div class="schedule-wrapper">
    <table class="schedule-table">
        <thead>
        <tr>
            <th class="schedule-time">Time</th>
            <?php foreach ($week as $day): ?>
                <th><?php echo $day; ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($times as $time_val): ?>
            <tr>
                <td class="schedule-time">
                    <?php echo date("H:i", $time_val); ?>
                </td>

                <?php foreach ($week as $day): ?>
                    <?php
                    $cell_class = "class-empty";
                    $content = "";

                    if (!empty($map[$day])) {
                        foreach ($map[$day] as $cls) {
                            if ($time_val == $cls['start']) {
                                $cell_class = "class-start";
                                $content = '<span class="class-label">' . htmlspecialchars($cls['name']) . '</span>'
                                         . '<span class="class-room">' . htmlspecialchars($cls['room']) . '</span>';
                                break;
                            } elseif ($time_val > $cls['start'] && $time_val < $cls['end']) {
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