<!DOCTYPE html>
<html lang="en">
<head>
    <title>Logger Website</title>
</head>
<body>
<?php

class Logger
{
    public static $studentLogFile = "studenti.json";
    public static $arrivalLogFile = "prichody.json";

    public static function getCurrentDateTime()
    {
        return date("Y-m-d H:i:s");
    }

    public static function appendToLogFile(string $logData, string $filename)
    {
        $existingLogs = file_get_contents($filename);

        if (!empty($existingLogs)) {
            $logData = "\n" . $logData;
        }

        file_put_contents($filename, $logData, FILE_APPEND);
    }

    public static function getLogs(string $filename)
    {
        return file_get_contents($filename);
    }

    public static function processStudentArrival(string $studentArrivalTime, string $studentName = '')
    {

        if ($studentArrivalTime > "08:00:00") {
            $logData = self::getCurrentDateTime() . " - meskanie";
        } elseif ($studentArrivalTime >= "20:00:00" && $studentArrivalTime <= "23:59:59") {
            die("Nemoze sa zapisat dany prichod do logu.");
        } else {
            $logData = self::getCurrentDateTime();
        }

        if (!empty($studentName)) {
            self::appendToLogFile($studentName, self::$studentLogFile);
            $logData .= " - $studentName";
        }

        self::appendToLogFile($logData, self::$arrivalLogFile);
    }

    public static function saveStudent(string $studentName)
    {
        $student = [
            'name' => $studentName
        ];

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

        $students[] = $student;
        $encodedStudents = json_encode($students, JSON_PRETTY_PRINT);
        file_put_contents($studentLogFile, $encodedStudents);

        $arrival = [
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
                if (strtotime($arrival['arrival_time']) > strtotime("08:00:00")) {
                    $studentName = $students[$index]['name'] ?? "Unknown";
                    echo "Meno žiaka: " . $studentName . " - Neskorý príchod<br>";
                    echo "Čas príchodu: " . $arrival['arrival_time'] . "<br><br>";
                }
            }
        } else {
            echo "Žiadne neskoré príchody.<br>";
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

if (isset($_GET['meno'])) {
    $studentName = $_GET['meno'];
    Logger::processStudentArrival(Logger::getCurrentDateTime(), $studentName);
}

echo "<h2>Prihlásení študenti:</h2>";
Logger::printStudents();

echo "<h2>studenti.json:</h2>";
echo nl2br(Logger::getLogs(Logger::$studentLogFile)); // Display logs with line breaks

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
