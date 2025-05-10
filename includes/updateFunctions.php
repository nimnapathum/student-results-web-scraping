<?php

function loadStudentData($csvFile) {
    $rows = array_map('str_getcsv', file($csvFile));
    $header = array_shift($rows);

    $students = [];

    foreach ($rows as $row) {
        // Extract values using headers
        $assoc = array_combine($header, $row);

        // Clean values
        $name = trim(str_replace('Name: ', '', $assoc['Name']));
        $index = trim(str_replace('Index No.: ', '', $assoc['Index']));

        // Create unique key
        $key = $index;

        if (!isset($students[$key])) {
            $students[$key] = [
                'name' => $name,
                'index' => $index,
                'results' => [],
                'gpa' => 0
            ];
        }

        $subject = $assoc['Subject'];
        $year = $assoc['Year'];
        $semester = $assoc['Semester'];
        $credits = $assoc['Credits'];
        $result = $assoc['Result'];

        $students[$key]['results'][] = [
            'subject' => $subject,
            'year' => $year,
            'semester' => $semester,
            'credits' => $credits,
            'result' => $result
        ];
    }

    // Calculate GPA per student
    foreach ($students as &$student) {
        $totalPoints = 0;
        $totalCredits = 0;
        foreach ($student['results'] as $r) {
            $grade = $r['result'];
            $credits = floatval($r['credits']);
            $points = getGradePoint($grade);
            if ($credits > 0) {
                $totalPoints += $points * $credits;
                $totalCredits += $credits;
            }
        }
        $student['gpa'] = $totalCredits > 0 ? round($totalPoints / $totalCredits, 6) : 0.0;
    }

    return array_values($students); // reindex for display
}

function getGradePoint($grade) {
    switch ($grade) {
        case 'A+': case 'A': return 4.0;
        case 'A-': return 3.7;
        case 'B+': return 3.3;
        case 'B': return 3.0;
        case 'B-': return 2.7;
        case 'C+': return 2.3;
        case 'C': return 2.0;
        case 'C-': return 1.7;
        case 'D+': return 1.3;
        case 'D': return 1.0;
        case 'D-': return 0.7;
        case 'F': return 0.0;
        default: return 0.0;
    }
}
