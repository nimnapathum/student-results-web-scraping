<!-- <?php
// /index.php

// Include necessary files
include 'includes/functions.php';

// Load student data from CSV
$students = loadStudentData('data/students.csv');

// Sort students by GPA in descending order
usort($students, function($a, $b) {
    return $b['gpa'] <=> $a['gpa'];
});

// Display student results in a table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <tr onclick="showStudentDetails(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['index']; ?></td>
                    <td><?php echo $student['gpa']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div id="student-details" style="display:none;">
        <h2>Student Details</h2>
        <div id="student-info"></div>
        <canvas id="student-chart" width="400" height="200"></canvas>
    </div>

    <script>
        // Function to display student details and GPA chart
        function showStudentDetails(student) {
            document.getElementById('student-details').style.display = 'block';
            document.getElementById('student-info').innerHTML = `
                <h3>${student.name}</h3>
                <p>Index: ${student.index}</p>
                <p>GPA: ${student.gpa}</p>
                <h4>Results:</h4>
                <ul>
                    ${student.results.map(result => 
                        `<li>${result.subject}: ${result.result} (${result.credits} credits)</li>`
                    ).join('')}
                </ul>
            `;

            // Prepare data for chart
            const chartData = {
                labels: student.results.map(result => result.subject),
                datasets: [{
                    label: 'Grade Points',
                    data: student.results.map(result => {
                        switch (result.result) {
                            case 'A+': return 4.0;
                            case 'A': return 4.0;
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
                    }),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            };

            // Create chart using Chart.js
            const ctx = document.getElementById('student-chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4.0
                        }
                    }
                }
            });
        }
    </script>
</body>
</html> -->


<?php
// /index.php

include 'includes/updateFunctions.php';

$students = loadStudentData('data/students.csv');

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
        body { font-family: Arial, sans-serif; }
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
    <button onclick="document.getElementById('student-panel').style.display='none'">Close</button>
    <div id="panel-content"></div>
</div>

<script>
    function showStudentPanel(student) {
        const panel = document.getElementById('student-panel');
        const content = document.getElementById('panel-content');

        // Group results by semester and calculate semester GPA
        const semesterMap = {};

        student.results.forEach(r => {
            const year = r.year.replace(/\[|\]/g, '');
            const sem = r.semester.replace(/\[|\]/g, '');
            const key = `Year ${year} Semester ${sem}`;

            if (!semesterMap[key]) semesterMap[key] = [];
            semesterMap[key].push(r);
        });

        let summaryHtml = `
            <h2>${student.name}</h2>
            <p><strong>Index:</strong> ${student.index}</p>
            <p><strong>Overall GPA:</strong> ${student.gpa}</p>
            <hr>
        `;

        Object.keys(semesterMap).forEach((semKey, idx) => {
            const subjects = semesterMap[semKey];
            let totalCredits = 0;
            let totalPoints = 0;

            subjects.forEach(sub => {
                const gradePoint = getGradePoint(sub.result);
                const credits = parseFloat(sub.credits);
                if (credits > 0) {
                    totalPoints += gradePoint * credits;
                    totalCredits += credits;
                }
            });

            const semGPA = totalCredits ? (totalPoints / totalCredits).toFixed(2) : 'N/A';

            summaryHtml += `
                <div class="semester-summary">
                    <h3>${semKey}</h3>
                    <p><strong>Semester GPA:</strong> ${semGPA}</p>
                    <ul>
                        ${subjects.map(s => `<li>${s.subject} (${s.credits} credits): ${s.result}</li>`).join('')}
                    </ul>
                </div>
            `;
        });

        content.innerHTML = summaryHtml;
        panel.style.display = 'block';
    }

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
</script>

</body>
</html>
