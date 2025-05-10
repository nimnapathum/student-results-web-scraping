<?php
// /includes/functions.php

// Function to calculate GPA based on student results
function calculateGPA($studentResults) {
    $totalCredits = 0;
    $totalPoints = 0;

    foreach ($studentResults as $result) {
        $credits = (int)$result['credits'];
        $grade = $result['result'];

        // Assign points for each grade (based on standard grading system)
        switch ($grade) {
            case 'A+': $points = 4.0; break;
            case 'A': $points = 4.0; break;
            case 'A-': $points = 3.7; break;
            case 'B+': $points = 3.3; break;
            case 'B': $points = 3.0; break;
            case 'B-': $points = 2.7; break;
            case 'C+': $points = 2.3; break;
            case 'C': $points = 2.0; break;
            case 'C-': $points = 1.7; break;
            case 'D+': $points = 1.3; break;
            case 'D': $points = 1.0; break;
            case 'D-': $points = 0.7; break;
            case 'F': $points = 0.0; break;
            default: $points = 0.0; break;
        }

        $totalCredits += $credits;
        $totalPoints += $credits * $points;
    }

    // Return GPA, ensuring no division by zero occurs
    return $totalCredits > 0 ? round($totalPoints / $totalCredits, 6) : 0;
}

// Function to load student data from CSV
function loadStudentData($filename) {
    $students = [];
    $studentResults = [];

    if (($handle = fopen($filename, "r")) !== FALSE) {
        // Skip the header row
        fgetcsv($handle);

        // Loop through each row in CSV
        while (($data = fgetcsv($handle)) !== FALSE) {
            $name = trim(explode(':', $data[0])[1]);
            $index = trim(explode(':', $data[1])[1]);
            $subject = $data[2];
            $credits = $data[5];//(int)trim(explode(':', $data[5])[1]);
            $result = trim($data[6]);

            // Group results by student index
            if (!isset($studentResults[$index])) {
                $studentResults[$index] = [
                    'name' => $name,
                    'results' => []
                ];
            }

            $studentResults[$index]['results'][] = [
                'subject' => $subject,
                'credits' => $credits,
                'result' => $result
            ];
        }

        fclose($handle);
    } else {
        // Log error if file cannot be opened
        error_log("Error opening file: $filename");
    }

    // Prepare final student data with GPA calculation
    foreach ($studentResults as $index => $data) {
        $students[] = [
            'name' => $data['name'],
            'index' => $index,
            'gpa' => calculateGPA($data['results']),
            'results' => $data['results']
        ];
    }

    return $students;
}
?>


