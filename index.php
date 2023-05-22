<?php

function greet()
{
    echo "Ahoj<br>";
}

function getCurrentDateTime()
{
    return date("Y-m-d H:i:s");
}

function appendToLogFile($logData)
{
    $filename = "log.txt";
    $existingLogs = file_get_contents($filename);

    if (!empty($existingLogs)) {
        $logData = "\n" . $logData;
    }

    file_put_contents($filename, $logData, FILE_APPEND);
}

function getLogs()
{
    $filename = "log.txt";
    return file_get_contents($filename);
}

function processStudentArrival($studentArrivalTime)
{
    $currentTime = date("H:i:s");

    if ($studentArrivalTime > "08:00:00") {
        $logData = getCurrentDateTime() . " - meskanie\n";
        appendToLogFile($logData);
    } elseif ($studentArrivalTime >= "20:00:00" && $studentArrivalTime <= "23:59:59") {
        die("Nemoze sa zapisat dany prichod do logu.");
    } else {
        $logData = getCurrentDateTime() . "\n";
        appendToLogFile($logData);
    }
}

greet();
echo "Aktuálny dátum a čas: " . getCurrentDateTime() . "<br>";

processStudentArrival("08:05:00"); // Test meskanie
processStudentArrival("19:00:00"); // Test valid arrival time
processStudentArrival("20:30:00"); // Test error

echo "Obsah log súboru:<br>";
echo nl2br(getLogs()); // Display logs w/ line breaks
?>
