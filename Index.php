<?php
// /index.php

include 'includes/updateFunctions.php'; // make sure the loadStudentData() is defined here

$students = loadStudentData('data/students.csv');

// Sort students by GPA in descending order
usort($students, function ($a, $b) {
    return $b['gpa'] <=> $a['gpa'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Results</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 70%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }

        #student-panel {
            position: fixed;
            right: 0;
            top: 0;
            width: 30%;
            height: 100%;
            background-color: #f9f9f9;
            border-left: 1px solid #ccc;
            padding: 20px;
            overflow-y: auto;
            display: none;
        }

        #student-panel h2, #student-panel h3 {
            margin-top: 0;
        }

        .semester-summary {
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        button.close-btn {
            float: right;
            margin-bottom: 10px;
            padding: 5px 10px;
        }
    </style>
</head>
<body>

<h1>Student Results</h1>

<table>
    <thead>
        <tr>
            <th>Rank</th>
            <th>Student Name</th>
            <th>Index Number</th>
            <th>GPA</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $index => $student): ?>
            <tr onclick='showStudentPanel(<?php echo json_encode($student); ?>)'>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td><?= htmlspecialchars($student['index']) ?></td>
                <td><?= $student['gpa'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="student-panel">
    <button class="close-btn" onclick="document.getElementById('student-panel').style.display='none'">Close</button>
    <div id="panel-content"></div>
</div>

<script>
    function getGradePoint(grade) {
        switch (grade) {
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

    function showStudentPanel(student) {
        const panel = document.getElementById('student-panel');
        const content = document.getElementById('panel-content');

        const semesterMap = {};

        student.results.forEach(result => {
            const year = result.year.replace(/\[|\]/g, '');
            const semester = result.semester.replace(/\[|\]/g, '');
            const key = `Year ${year} Semester ${semester}`;

            if (!semesterMap[key]) semesterMap[key] = [];
            semesterMap[key].push(result);
        });

        let html = `
            <h2>${student.name}</h2>
            <p><strong>Index:</strong> ${student.index}</p>
            <p><strong>Overall GPA:</strong> ${student.gpa}</p>
            <hr>
        `;

        for (const [semKey, subjects] of Object.entries(semesterMap)) {
            let totalCredits = 0, totalPoints = 0;

            subjects.forEach(sub => {
                const gp = getGradePoint(sub.result);
                const cr = parseFloat(sub.credits);
                if (cr > 0) {
                    totalPoints += gp * cr;
                    totalCredits += cr;
                }
            });

            const semGPA = totalCredits > 0 ? (totalPoints / totalCredits).toFixed(2) : 'N/A';

            html += `
                <div class="semester-summary">
                    <h3>${semKey}</h3>
                    <p><strong>Semester GPA:</strong> ${semGPA}</p>
                    <ul>
                        ${subjects.map(s => `<li>${s.subject} (${s.credits} credits): ${s.result}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        content.innerHTML = html;
        panel.style.display = 'block';
    }
</script>

</body>
</html>
