<!DOCTYPE html>
<html lang="en">
<head>
    <title>School Logger</title>
</head>
<body>
<?php

class Logger
{
    public static $studentLogFile = "studenti.json";
    public static $arrivalLogFile = "prichody.json";

    private static function getNextId(string $filename): int
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
        $encodedStudents = json_encode($students, JSON_PRETTY_PRINT);
        file_put_contents($studentLogFile, $encodedStudents);

        $arrivalId = self::getNextId($arrivalLogFile);
        $arrival = [
            'id' => $arrivalId,
            'arrival_time' => self::getCurrentDateTime()
        ];
        $arrivals[] = $arrival;
        $encodedArrivals = json_encode($arrivals, JSON_PRETTY_PRINT);
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
                echo "ID žiaka: " . $student['id'] . "<br>";
                echo "Meno žiaka: " . $student['name'] . "<br>";
                echo "Čas príchodu: " . $arrivals[$index]['arrival_time'] . "<br><br>";
            }
        } else {
            echo "Žiadni študenti neboli prihlásení.<br>";
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

                    echo "ID žiaka: " . $students[$index]['id'] . "<br>";
                    echo "Meno žiaka: " . $studentName . " - Neskorý príchod<br>";
                    echo "Čas príchodu: " . $arrival['arrival_time'] . "<br><br>";

                } elseif ($studentArrivalTime >= strtotime("20:00:00") &&
                    $studentArrivalTime <= strtotime("23:59:59")) {

                    echo "ID žiaka: " . $students[$index]['id'] . "<br>";
                    echo "Meno žiaka: " . $studentName . " - Záznam nie je povolený.<br><br>";
                    die(); // Terminate script

                }
            }
        } else {
            echo "Žiadne neskoré príchody.<br>";
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
}

echo "Ahoj, Aktuálny dátum a čas: " . Logger::getCurrentDateTime() . "<br>";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentName = $_POST["studentName"];
    if (!empty($studentName)) {
        Logger::saveStudent($studentName);
    }
}

Logger::processNameParameter();

echo "<h2>Prihlásení študenti:</h2>";
Logger::printStudents();

echo "<h2>studenti.json:</h2>";
echo nl2br(Logger::getLogs(Logger::$studentLogFile)); // Display student logs

echo "<h2>prichody.json:</h2>";
echo nl2br(Logger::getLogs(Logger::$arrivalLogFile)); // Display arrival logs

echo "<h2>Meskanie:</h2>";
Logger::checkLateArrivals();
?>
<h2>Form:</h2>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <label for="studentName">Meno žiaka:</label>
    <input type="text" name="studentName" id="studentName">
    <input type="submit" value="Submit">
</form>
</body>
</html>
