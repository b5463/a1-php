<!DOCTYPE html>
<html lang="en">
<head>
    <title>School Logger</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h2 {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="text"] {
            width: 200px;
            padding: 5px;
        }

        input[type="submit"] {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .log-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .log-item span {
            font-weight: bold;
        }

        .no-logs {
            font-style: italic;
            color: #999;
        }
    </style>
</head>
<body>
<?php

class Logger
{
    public static $studentLogFile = "studenti.json";
    public static $arrivalLogFile = "prichody.json";

    public static function getNextId(string $filename): int
    {
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
            if (is_array($data)) {
                $ids = array_column($data, 'id');
                return max($ids) + 1;
            }
        }
        return 1;
    }

    private static function encodeJson($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getCurrentDateTime()
    {
        return date("Y-m-d H:i:s");
    }

    public static function getLogs(string $filename)
    {
        return file_get_contents($filename);
    }

    public static function saveStudent(string $studentName)
    {
        $studentLogFile = self::$studentLogFile;
        $arrivalLogFile = self::$arrivalLogFile;
        $students = [];
        $arrivals = [];

        if (file_exists($studentLogFile)) {
            $students = json_decode(file_get_contents($studentLogFile), true);
        }

        if (file_exists($arrivalLogFile)) {
            $arrivals = json_decode(file_get_contents($arrivalLogFile), true);
        }

        $studentId = self::getNextId($studentLogFile);
        $student = [
            'id' => $studentId,
            'name' => $studentName
        ];
        $students[] = $student;
        $encodedStudents = self::encodeJson($students);
        file_put_contents($studentLogFile, $encodedStudents);

        $arrivalId = self::getNextId($arrivalLogFile);
        $arrival = new Arrival($arrivalId);
        $arrivals[] = $arrival->toArray();
        $encodedArrivals = self::encodeJson($arrivals);
        file_put_contents($arrivalLogFile, $encodedArrivals);
    }

    public static function loadStudents()
    {
        if (file_exists(self::$studentLogFile)) {
            $students = json_decode(file_get_contents(self::$studentLogFile), true);
        } else {
            $students = [];
        }

        return $students;
    }

    public static function loadArrivals()
    {
        if (file_exists(self::$arrivalLogFile)) {
            $arrivals = json_decode(file_get_contents(self::$arrivalLogFile), true);
        } else {
            $arrivals = [];
        }

        return $arrivals;
    }

    public static function printStudents()
    {
        $students = self::loadStudents();
        $arrivals = self::loadArrivals();

        if (!empty($students) && !empty($arrivals)) {
            foreach ($students as $index => $student) {
                echo '<div class="log-item">';
                echo '<span>ID žiaka:</span> ' . $student['id'] . '<br>';
                echo '<span>Meno žiaka:</span> ' . $student['name'] . '<br>';
                echo '<span>Čas príchodu:</span> ' . $arrivals[$index]['arrival_time'] . '<br>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-logs">Žiadni študenti neboli prihlásení.</p>';
        }
    }

    public static function checkLateArrivals()
    {
        $students = self::loadStudents();
        $arrivals = self::loadArrivals();

        if (!empty($students) && !empty($arrivals)) {
            foreach ($arrivals as $index => $arrival) {
                $studentName = $students[$index]['name'] ?? "Unknown";
                $studentArrivalTime = strtotime($arrival['arrival_time']);

                if ($studentArrivalTime > strtotime("08:00:00")) {
                    echo '<div class="log-item">';
                    echo '<span>ID žiaka:</span> ' . $students[$index]['id'] . '<br>';
                    echo '<span>Meno žiaka:</span> ' . $studentName . ' - Neskorý príchod<br>';
                    echo '<span>Čas príchodu:</span> ' . $arrival['arrival_time'] . '<br>';
                    echo '</div>';
                } elseif ($studentArrivalTime >= strtotime("20:00:00") &&
                    $studentArrivalTime <= strtotime("23:59:59")) {
                    echo '<div class="log-item">';
                    echo '<span>ID žiaka:</span> ' . $students[$index]['id'] . '<br>';
                    echo '<span>Meno žiaka:</span> ' . $studentName . ' - Záznam nie je povolený.<br>';
                    echo '</div>';
                    die(); // Terminate script
                }
            }
        } else {
            echo '<p class="no-logs">Žiadne neskoré príchody.</p>';
        }
    }

    public static function processNameParameter()
    {
        if (isset($_GET['name'])) {
            $studentName = $_GET['name'];
            $_POST["studentName"] = $studentName;
            Logger::saveStudent($studentName);
        }
    }

    public static function processPostRequest()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $studentName = $_POST["studentName"];
            if (!empty($studentName)) {
                self::saveStudent($studentName);
            }
        }
    }

    public static function printStudentLogs()
    {
        $studentLogs = self::getLogs(self::$studentLogFile);
        $students = json_decode($studentLogs, true);

        if (!empty($students)) {
            echo '<h2>Contents of studenti.json:</h2>';
            foreach ($students as $student) {
                echo '<div class="log-item">';
                echo '<span>ID žiaka:</span> ' . $student['id'] . '<br>';
                echo '<span>Meno žiaka:</span> ' . $student['name'] . '<br>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-logs">No student logs found.</p>';
        }
    }

    public static function printArrivalLogs()
    {
        $arrivalLogs = self::getLogs(self::$arrivalLogFile);
        $arrivals = json_decode($arrivalLogs, true);

        if (!empty($arrivals)) {
            echo '<h2>Contents of prichody.json:</h2>';
            foreach ($arrivals as $arrival) {
                echo '<div class="log-item">';
                echo '<span>ID žiaka:</span> ' . $arrival['id'] . '<br>';
                echo '<span>Čas príchodu:</span> ' . $arrival['arrival_time'] . '<br>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-logs">No arrival logs found.</p>';
        }
    }
}

class Arrival
{
    private $id;
    private $arrivalTime;

    public function __construct($id)
    {
        $this->id = $id;
        $this->arrivalTime = Logger::getCurrentDateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'arrival_time' => $this->arrivalTime
        ];
    }
}

Logger::processNameParameter();
Logger::processPostRequest();

?>
<h2>Prihlasovací formulár</h2>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="name">Meno študenta:</label>
    <input type="text" id="name" name="studentName" required>

    <input type="submit" value="Prihlásiť">
</form>

<h2>Zoznam prihlásených študentov</h2>
<?php
Logger::printStudents();
?>

<h2>Zoznam neskorých príchodov</h2>
<?php
Logger::checkLateArrivals();
?>

<h2>Logy</h2>
<?php
Logger::printStudentLogs();
Logger::printArrivalLogs();
?>

</body>
</html>
