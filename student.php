<?php

include 'includes/updateFunctions.php';

$studentIndex = $_GET['student'] ?? null;
$rank = $_GET['rank'] ?? null;
$student = studentDataByIndex("data/students.csv", $studentIndex);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $student['name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="shortcut icon" href="graduating-student.png" type="image/png">
    <link rel="stylesheet" href="styles/styleStudents.css">
</head>

<body>
    <?php

    // Function to get grade class for styling
    function getGradeClass($grade)
    {
        if (in_array($grade, ['A+', 'A', 'A-', 'CM'])) return 'a';
        if (in_array($grade, ['B+', 'B', 'B-'])) return 'b';
        if (in_array($grade, ['C+', 'C', 'C-'])) return 'c';
        if (in_array($grade, ['D+', 'D', 'D-'])) return 'd';
        if (in_array($grade, ['MC'])) return 'e';
        return 'f';
    }


    // Organize results by semester
    $semesters = [];
    foreach ($student['results'] as $result) {
        $semKey = "Year {$result['year']} - Semester {$result['semester']}";
        if (!isset($semesters[$semKey])) {
            $semesters[$semKey] = [];
        }
        $semesters[$semKey][] = $result;
    }

    // Calculate semester GPAs
    $semesterGPAs = [];
    $gradeDistribution = [
        'A+' => 0,
        'A' => 0,
        'A-' => 0,
        'B+' => 0,
        'B' => 0,
        'B-' => 0,
        'C+' => 0,
        'C' => 0,
        'C-' => 0,
        'D+' => 0,
        'D' => 0,
        'D-' => 0,
        'F' => 0
    ];

    $creditsByYear = [];
    $totalCredits = 0;

    foreach ($semesters as $semKey => $courses) {
        $totalPoints = 0;
        $semCredits = 0;

        foreach ($courses as $course) {
            if ($course['credits'] > 0) {
                $totalPoints += getGradePoint($course['result']) * $course['credits'];
                $semCredits += $course['credits'];
                $totalCredits += $course['credits'];

                $year = "Year {$course['year']}";
                if (!isset($creditsByYear[$year])) {
                    $creditsByYear[$year] = 0;
                }
                $creditsByYear[$year] += $course['credits'];

                // Count for grade distribution
                if (isset($gradeDistribution[$course['result']])) {
                    $gradeDistribution[$course['result']]++;
                }
            }
        }

        $semesterGPAs[$semKey] = $semCredits > 0 ? $totalPoints / $semCredits : 0;
    }

    // Sort semesters by year and semester
    ksort($semesters);
    ksort($semesterGPAs);
    ?>

    <button style="position: fixed; top: 20px; left: 20px; border: none; background-color: transparent; cursor: pointer;" onclick="window.history.back()">
        <i class="fas fa-arrow-left" style="font-size: 36px; color: var(--primary);"></i>
    </button>

    <div class="container">
        <div class="student-profile">
            <div class="student-header">
                <div class="avatar"><?= substr(getName($student['name']), 0, 1) ?></div>
                <div class="student-info">
                    <h2 class="student-name"><?= getName($student['name']) ?></h2>
                    <p class="student-id">Index Number: <?= $student['index'] ?></p>
                    <div class="rank-badge">
                        <i class="fas fa-trophy"></i>
                        <span>Rank <?= $rank ?></span>
                    </div>
                    <div class="grade-points">
                        <div class="grade-item">
                            <span class="grade-label">Overall GPA:</span>
                            <span class="grade-value"><?= number_format($student['gpa'], 2) ?></span>
                        </div>
                        <div class="grade-item">
                            <span class="grade-label">Credits:</span>
                            <span class="grade-value"><?= $totalCredits ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-label">Avg Performance</div>
                    <div class="stat-value">
                        <?php
                        $avgPerformance = 0;
                        $count = count($semesterGPAs);
                        if ($count > 0) {
                            $avgPerformance = array_sum($semesterGPAs) / $count;
                        }
                        echo number_format($avgPerformance * 100 / 4, 1) . '%';
                        ?>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>2.3%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="stat-label">A+ Grades</div>
                    <div class="stat-value">
                        <?php
                        $aPlusCount = 0;
                        foreach ($student['results'] as $result) {
                            if ($result['result'] === 'A+' && $result['credits'] > 0) {
                                $aPlusCount++;
                            }
                        }
                        echo $aPlusCount;
                        ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-label">Completed Courses</div>
                    <div class="stat-value">
                        <?php
                        $completedCourses = 0;
                        foreach ($student['results'] as $result) {
                            if ($result['credits'] > 0) {
                                $completedCourses++;
                            }
                        }
                        echo $completedCourses;
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-container">
            <div class="tab-nav">
                <?php $tabIndex = 0; ?>
                <?php foreach ($semesters as $semKey => $courses): ?>
                    <button class="tab-button <?= $tabIndex === 0 ? 'active' : '' ?>"
                        onclick="openTab(event, 'tab-<?= $tabIndex ?>')">
                        <?= $semKey ?>
                    </button>
                    <?php $tabIndex++; ?>
                <?php endforeach; ?>
                <button class="tab-button" onclick="openTab(event, 'tab-performance')">
                    Performance Analysis
                </button>
                <button class="tab-button" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    <span>Print Report</span>
                </button>
            </div>

            <?php $tabIndex = 0; ?>
            <?php foreach ($semesters as $semKey => $courses): ?>
                <div id="tab-<?= $tabIndex ?>" class="tab-content <?= $tabIndex === 0 ? 'active' : '' ?>">
                    <div class="semester-header">
                        <h3 class="semester-title"><?= $semKey ?> Results</h3>
                        <div class="semester-gpa">
                            <span class="label">Semester GPA:</span>
                            <span class="value"><?= number_format($semesterGPAs[$semKey], 2) ?></span>
                        </div>
                    </div>

                    <table class="course-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Subject Name</th>
                                <th class="credits">Credits</th>
                                <th>Grade</th>
                                <th>Grade Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td>
                                        <?= explode(' ', $course['subject'])[0] ?>
                                    </td>
                                    <td>
                                        <?= substr($course['subject'], strpos($course['subject'], ' ') + 1) ?>
                                    </td>
                                    <td class="credits"><?= $course['credits'] ?></td>
                                    <td>
                                        <span class="grade <?= getGradeClass($course['result']) ?>">
                                            <?= $course['result'] ?>
                                        </span>
                                    </td>
                                    <td><?= getGradePoint($course['result']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php $tabIndex++; ?>
            <?php endforeach; ?>

            <div id="tab-performance" class="tab-content">
                <div class="gpa-trend">
                    <h3>GPA Trend Across Semesters</h3>
                    <div class="gpa-chart-container">
                        <canvas id="gpa-trend-chart"></canvas>
                    </div>
                </div>

                <div class="subject-distribution">
                    <h3>Performance Analysis</h3>
                    <div class="charts-grid">
                        <div class="chart-wrapper">
                            <canvas id="grade-distribution-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="creditsModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h3 class="modal-title">Grade Point System</h3>
            </div>
            <table class="course-table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Grade Point</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A+</td>
                        <td>4.0</td>
                        <td>Outstanding</td>
                    </tr>
                    <tr>
                        <td>A</td>
                        <td>4.0</td>
                        <td>Excellent</td>
                    </tr>
                    <tr>
                        <td>A-</td>
                        <td>3.7</td>
                        <td>Very Good</td>
                    </tr>
                    <tr>
                        <td>B+</td>
                        <td>3.3</td>
                        <td>Good</td>
                    </tr>
                    <tr>
                        <td>B</td>
                        <td>3.0</td>
                        <td>Above Average</td>
                    </tr>
                    <tr>
                        <td>B-</td>
                        <td>2.7</td>
                        <td>Average</td>
                    </tr>
                    <tr>
                        <td>C+</td>
                        <td>2.3</td>
                        <td>Satisfactory</td>
                    </tr>
                    <tr>
                        <td>C</td>
                        <td>2.0</td>
                        <td>Acceptable</td>
                    </tr>
                    <tr>
                        <td>C-</td>
                        <td>1.7</td>
                        <td>Passing</td>
                    </tr>
                    <tr>
                        <td>D+</td>
                        <td>1.3</td>
                        <td>Low Pass</td>
                    </tr>
                    <tr>
                        <td>D</td>
                        <td>1.0</td>
                        <td>Poor</td>
                    </tr>
                    <tr>
                        <td>D-</td>
                        <td>0.7</td>
                        <td>Minimal Pass</td>
                    </tr>
                    <tr>
                        <td>F</td>
                        <td>0.0</td>
                        <td>Fail</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript for tab navigation -->
    <script>
        function openTab(evt, tabId) {
            // Hide all tab content
            var tabContents = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove("active");
            }

            // Remove active class from all tab buttons
            var tabButtons = document.getElementsByClassName("tab-button");
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove("active");
            }

            // Show the selected tab content and mark button as active
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        function openModal() {
            document.getElementById("creditsModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("creditsModal").style.display = "none";
        }

        // Initialize charts once DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // GPA trend chart
            const gpaLabels = <?= json_encode(array_keys($semesterGPAs)); ?>;
            const gpaData = <?= json_encode(array_values($semesterGPAs)); ?>;

            new Chart(document.getElementById('gpa-trend-chart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: gpaLabels,
                    datasets: [{
                        label: 'GPA',
                        data: gpaData,
                        borderColor: '#4a6feb',
                        backgroundColor: 'rgba(74, 111, 235, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 2,
                            max: 4,
                            ticks: {
                                stepSize: 0.5
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Grade distribution chart
            const gradeLabels = Object.keys(<?= json_encode($gradeDistribution); ?>);
            const gradeData = Object.values(<?= json_encode($gradeDistribution); ?>);

            new Chart(document.getElementById('grade-distribution-chart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: gradeLabels,
                    datasets: [{
                        data: gradeData,
                        backgroundColor: [
                            '#4CAF50', '#8BC34A', '#CDDC39',
                            '#FFC107', '#FF9800', '#FF5722',
                            '#F44336', '#E91E63', '#9C27B0',
                            '#673AB7', '#3F51B5', '#2196F3',
                            '#03A9F4'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        title: {
                            display: true,
                            text: 'Grade Distribution'
                        }
                    }
                }
            });

            // Credits by year chart
            const yearLabels = Object.keys(<?= json_encode($creditsByYear); ?>);
            const yearData = Object.values(<?= json_encode($creditsByYear); ?>);

            new Chart(document.getElementById('credits-by-year-chart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: yearLabels,
                    datasets: [{
                        label: 'Credits',
                        data: yearData,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Credits by Year'
                        }
                    }
                }
            });

            // Create semester-specific charts
            <?php $tabIndex = 0; ?>
            <?php foreach ($semesters as $semKey => $courses): ?>
                // Initialize data arrays for this semester
                const semesterGrades<?= $tabIndex ?> = [];
                const semesterSubjects<?= $tabIndex ?> = [];
                const semesterCredits<?= $tabIndex ?> = [];

                <?php foreach ($courses as $course): ?>
                    semesterGrades<?= $tabIndex ?>.push('<?= $course['result'] ?>');
                    semesterSubjects<?= $tabIndex ?>.push('<?= explode(' ', $course['subject'])[0] ?>');
                    semesterCredits<?= $tabIndex ?>.push(<?= $course['credits'] ?>);
                <?php endforeach; ?>

                // Grade distribution chart for this semester
                new Chart(document.getElementById('grades-chart-<?= $tabIndex ?>').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: semesterSubjects<?= $tabIndex ?>,
                        datasets: [{
                            label: 'Grade Points',
                            data: semesterGrades<?= $tabIndex ?>.map(grade => {
                                switch (grade) {
                                    case 'A+':
                                        return 4.0;
                                    case 'A':
                                        return 4.0;
                                    case 'A-':
                                        return 3.7;
                                    case 'B+':
                                        return 3.3;
                                    case 'B':
                                        return 3.0;
                                    case 'B-':
                                        return 2.7;
                                    case 'C+':
                                        return 2.3;
                                    case 'C':
                                        return 2.0;
                                    case 'C-':
                                        return 1.7;
                                    case 'D+':
                                        return 1.3;
                                    case 'D':
                                        return 1.0;
                                    case 'D-':
                                        return 0.7;
                                    case 'F':
                                        return 0.0;
                                    default:
                                        return 0.0;
                                }
                            }),
                            backgroundColor: semesterGrades<?= $tabIndex ?>.map(grade => {
                                if (['A+', 'A', 'A-'].includes(grade)) return 'rgba(76, 175, 80, 0.7)';
                                if (['B+', 'B', 'B-'].includes(grade)) return 'rgba(255, 193, 7, 0.7)';
                                if (['C+', 'C', 'C-'].includes(grade)) return 'rgba(255, 152, 0, 0.7)';
                                if (['D+', 'D', 'D-'].includes(grade)) return 'rgba(244, 67, 54, 0.7)';
                                return 'rgba(97, 97, 97, 0.7)';
                            }),
                            borderColor: semesterGrades<?= $tabIndex ?>.map(grade => {
                                if (['A+', 'A', 'A-'].includes(grade)) return 'rgba(76, 175, 80, 1)';
                                if (['B+', 'B', 'B-'].includes(grade)) return 'rgba(255, 193, 7, 1)';
                                if (['C+', 'C', 'C-'].includes(grade)) return 'rgba(255, 152, 0, 1)';
                                if (['D+', 'D', 'D-'].includes(grade)) return 'rgba(244, 67, 54, 1)';
                                return 'rgba(97, 97, 97, 1)';
                            }),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 4,
                                title: {
                                    display: true,
                                    text: 'Grade Points'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Courses'
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Grade Performance'
                            }
                        }
                    }
                });

                // Credits chart for this semester
                new Chart(document.getElementById('credits-chart-<?= $tabIndex ?>').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: semesterSubjects<?= $tabIndex ?>,
                        datasets: [{
                            data: semesterCredits<?= $tabIndex ?>,
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                                '#FF9F40', '#C9CBCF', '#7CFC00', '#00BFFF', '#FF00FF'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Credit Distribution'
                            }
                        }
                    }
                });

                <?php $tabIndex++; ?>
            <?php endforeach; ?>
        });

        // Show info modal when clicking on credits label
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('grade-label') && e.target.textContent.includes('GPA')) {
                openModal();
            }
        });

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById("creditsModal");
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>