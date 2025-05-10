<?php

function loadStudentData($csvFile) {
    // $rows = array_map('str_getcsv', file($csvFile));
    // $header = array_shift($rows);

    // $students = [];

    // foreach ($rows as $row) {
    //     // Extract values using headers
    //     $assoc = array_combine($header, $row);

    //     // Clean values
    //     $name = trim(str_replace('Name: ', '', $assoc['Name']));
    //     $index = trim(str_replace('Index No.: ', '', $assoc['Index']));

    //     // Create unique key
    //     $key = $index;

    //     if (!isset($students[$key])) {
    //         $students[$key] = [
    //             'name' => $name,
    //             'index' => $index,
    //             'results' => [],
    //             'gpa' => 0
    //         ];
    //     }

    //     $subject = $assoc['Subject'];
    //     $year = $assoc['Year'];
    //     $semester = $assoc['Semester'];
    //     $credits = $assoc['Credits'];
    //     $result = $assoc['Result'];

    //     $students[$key]['results'][] = [
    //         'subject' => $subject,
    //         'year' => $year,
    //         'semester' => $semester,
    //         'credits' => $credits,
    //         'result' => $result
    //     ];
    // }

    // // Calculate GPA per student
    // foreach ($students as &$student) {
    //     $totalPoints = 0;
    //     $totalCredits = 0;
    //     foreach ($student['results'] as $r) {
    //         $grade = $r['result'];
    //         $credits = floatval($r['credits']);
    //         $points = getGradePoint($grade);
    //         if ($credits > 0) {
    //             $totalPoints += $points * $credits;
    //             $totalCredits += $credits;
    //         }
    //     }
    //     $student['gpa'] = $totalCredits > 0 ? round($totalPoints / $totalCredits, 6) : 0.0;
    // }

    $rows = array_map('str_getcsv', file($csvFile));
    $students = [];

    foreach ($rows as $row) {
        // Handle individual columns
        $nameRaw = trim($row[0]);
        $indexRaw = trim($row[1]);
        $subject = trim($row[2]);
        $year = trim($row[3], "[]");
        $semester = trim($row[4], "[]");
        $credits = (float) trim($row[5]);
        $result = trim($row[6]);

        $name = str_replace('Name: ', '', $nameRaw);
        $index = str_replace('Index No.: ', '', $indexRaw);
        $studentKey = $index;

        if (!isset($students[$studentKey])) {
            $students[$studentKey] = [
                'name' => $name,
                'index' => $index,
                'results' => [],
                'gpa' => 0.0
            ];
        }

        // Group by subject: only one record per subject (handle repeats)
        if (!isset($students[$studentKey]['results'][$subject])) {
            $students[$studentKey]['results'][$subject] = [
                'subject' => $subject,
                'year' => $year,
                'semester' => $semester,
                'credits' => $credits,
                'result' => $result
            ];
        } else {
            // Handle repeat logic
            $prev = $students[$studentKey]['results'][$subject]['result'];
            $new = $result;

            if ($prev == 'MC') {
                // If previous result is MC, accept new result
                $students[$studentKey]['results'][$subject]['result'] = $new;
            }
            else {
                $prevPoint = getGradePoint($prev);
                $newPoint = getGradePoint($new);

                if ($newPoint >= $prevPoint) {
                    // Accept new result, but if it's worse than C, cap it
                    $students[$studentKey]['results'][$subject]['result'] = $newPoint >= getGradePoint("C") ? 'C' : $prev;
                }
            }
            // Else: keep original (first) result
        }
    }

    // Convert results to indexed array
    foreach ($students as &$student) {
        $results = array_values($student['results']);
        $student['results'] = $results;

        // GPA Calculation
        $totalPoints = 0;
        $totalCredits = 0;
        foreach ($results as $res) {
            $grade = $res['result'];
            $credit = (float)$res['credits'];
            $point = getGradePoint($grade);
            if ($credit > 0) {
                $totalPoints += $point * $credit;
                $totalCredits += $credit;
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


function studentDataByIndex($csvFile, $targetIndex) {
    $rows = array_map('str_getcsv', file($csvFile));
    $student = null;

    foreach ($rows as $row) {
        $nameRaw = trim($row[0]);
        $indexRaw = trim($row[1]);
        $subject = trim($row[2]);
        $year = trim($row[3], "[]");
        $semester = trim($row[4], "[]");
        $credits = (float) trim($row[5]);
        $result = trim($row[6]);

        $name = str_replace('Name: ', '', $nameRaw);
        $index = str_replace('Index No.: ', '', $indexRaw);

        if ($index !== $targetIndex) {
            continue; // skip other students
        }

        if (!$student) {
            $student = [
                'name' => $name,
                'index' => $index,
                'results' => [],
                'gpa' => 0.0
            ];
        }

        // Handle repeated subject logic
        if (!isset($student['results'][$subject])) {
            $student['results'][$subject] = [
                'subject' => $subject,
                'year' => $year,
                'semester' => $semester,
                'credits' => $credits,
                'result' => $result
            ];
        } else {
            $prev = $student['results'][$subject]['result'];
            $new = $result;

            $prevPoint = getGradePoint($prev);
            $newPoint = getGradePoint($new);

            if ($newPoint >= $prevPoint) {
                // Use new result, but cap if worse than C
                $student['results'][$subject]['result'] = $newPoint >= getGradePoint("C") ? 'C' : $prev;
            }
        }
    }

    if ($student) {
        $results = array_values($student['results']);
        $student['results'] = $results;

        // GPA Calculation
        $totalPoints = 0;
        $totalCredits = 0;
        foreach ($results as $res) {
            $grade = $res['result'];
            $credit = (float)$res['credits'];
            $point = getGradePoint($grade);
            if ($credit > 0) {
                $totalPoints += $point * $credit;
                $totalCredits += $credit;
            }
        }
        $student['gpa'] = $totalCredits > 0 ? round($totalPoints / $totalCredits, 10) : 0.0;
    }

    return $student;
}
