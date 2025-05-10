<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="graduating-student.png" type="image/png">
    <link rel="stylesheet" href="styles/style.css">
</head>

<body>
    <?php
    include 'includes/updateFunctions.php';
    $students = loadStudentData('data/students.csv');
    usort($students, function ($a, $b) {
        return $b['gpa'] <=> $a['gpa'];
    });
    ?>

    <div class="container">
        <div class="main-content">

            <div class="content">
                <h1 class="page-title">Student Results</h1>

                <hr>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-content">
                            <div class="label">Total Students</div>
                            <div class="value"><?= count($students) ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="label">Passed Students</div>
                            <div class="value"><?= count(array_filter($students, function ($s) {
                                                    return $s['gpa'] >= 2.0;
                                                })) ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon red">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="label">Failed Students</div>
                            <div class="value"><?= count(array_filter($students, function ($s) {
                                                    return $s['gpa'] < 2.0;
                                                })) ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stat-content">
                            <div class="label">Average GPA</div>
                            <div class="value">
                                <?php
                                $total = array_sum(array_column($students, 'gpa'));
                                $count = count($students);
                                echo number_format($count > 0 ? $total / $count : 0, 2);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-section">
                    <div class="table-header">
                        <div class="table-title">Student Rankings</div>

                        <div class="search-bar">
                            <input type="text" placeholder="Search students..." id="studentSearch">
                            <button><i class="fas fa-search" style="margin-right: 10px;"></i></button>
                        </div>

                        <div class="filter-buttons">
                            <button class="filter-button active">
                                <i class="fas fa-list"></i> All
                            </button>
                            <button class="filter-button green">
                                <i class="fas fa-check"></i> Passed
                            </button>
                            <button class="filter-button red">
                                <i class="fas fa-times"></i> Failed
                            </button>
                        </div>
                    </div>
                    <div class="table-content">
                        <?php
                        $rank = 1;
                        $previousGpa = null;
                        $sameRankCount = 0;
                        ?>

                        <table>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Index Number</th>
                                    <th>GPA</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <?php
                                    if ($previousGpa !== null && $student['gpa'] == $previousGpa) {
                                        $displayRank = $rank;
                                        $sameRankCount++;
                                    } else {
                                        $rank = $rank + $sameRankCount;
                                        $displayRank = $rank;
                                        $sameRankCount = 1;
                                    }
                                    $previousGpa = $student['gpa'];
                                    ?>
                                    <tr class="student-row" data-student='<?= json_encode($student) ?>'>
                                        <td><?= $displayRank ?></td>
                                        <td>
                                            <div class="student-name">
                                                <div class="student-avatar">
                                                    <?= substr(getName($student['name']), 0, 1) ?>
                                                </div>
                                                <?= getName($student['name']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($student['index']) ?></td>
                                        <td><?= $student['gpa'] ?></td>
                                        <td>
                                            <span class="status-badge <?= $student['gpa'] >= 2.0 ? 'passed' : 'failed' ?>">
                                                <?= $student['gpa'] >= 2.0 ? 'Passed' : 'Failed' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="view-button" onclick="window.location.href='http://localhost/resultsScraper/student-results-web-scraping/student.php?student=<?= urlencode($student['index']); ?>&rank=<?= $displayRank; ?>'">
                                                <i class="fas fa-arrow-right"></i>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>

    <div class="student-panel" id="studentPanel">
        <div class="panel-header">
            <div class="panel-title">Student Details</div>
            <button class="close-button" onclick="closeStudentPanel()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="panel-content"></div>
    </div>

    <script>
        function getGradePoint(grade) {
            switch (grade) {
                case 'A+':
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
        }

        function getGradeClass(gpa) {
            if (gpa >= 3.5) return "grade-excellent";
            if (gpa >= 2.0) return "grade-average";
            return "grade-poor";
        }

        function showStudentDetails(element) {
            const student = JSON.parse(element.closest('.student-row').dataset.student);
            const panel = document.getElementById('studentPanel');
            const content = document.getElementById('panel-content');
            const overlay = document.getElementById('overlay');

            const semesterMap = {};

            student.results.forEach(result => {
                const year = result.year.replace(/\[|\]/g, '');
                const semester = result.semester.replace(/\[|\]/g, '');
                const key = `Year ${year} Semester ${semester}`;

                if (!semesterMap[key]) semesterMap[key] = [];
                semesterMap[key].push(result);
            });

            let html = `
                <div class="student-details">
                    <div class="student-info">
                        <div class="large-avatar">${student.name.charAt(0)}</div>
                        <div class="student-meta">
                            <h3>${student.name}</h3>
                            <p>Index: ${student.index}</p>
                        </div>
                    </div>
                    
                    <div class="gpa-indicator">
                        <div class="gpa-label">Overall GPA</div>
                        <div class="gpa-value ${getGradeClass(student.gpa)}">${student.gpa}</div>
                    </div>
                </div>
            `;

            for (const [semKey, subjects] of Object.entries(semesterMap)) {
                let totalCredits = 0,
                    totalPoints = 0;

                subjects.forEach(sub => {
                    const gp = getGradePoint(sub.result);
                    const cr = parseFloat(sub.credits);
                    if (cr > 0) {
                        totalPoints += gp * cr;
                        totalCredits += cr;
                    }
                });

                const semGPA = totalCredits > 0 ? (totalPoints / totalCredits).toFixed(2) : 'N/A';
                const gradeClass = getGradeClass(semGPA);

                html += `
                    <div class="semester-block">
                        <div class="semester-header">
                            <div>${semKey}</div>
                            <div class="semester-gpa ${gradeClass}">${semGPA}</div>
                        </div>
                        <div class="course-list">
                            ${subjects.map(s => `
                                <div class="course-item">
                                    <div class="course-name">${s.subject}</div>
                                    <div class="course-credit">${s.credits} credits</div>
                                    <div class="course-grade ${getGradeClass(getGradePoint(s.result))}">${s.result}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            content.innerHTML = html;
            panel.classList.add('open');
            overlay.style.display = 'block';
        }

        function closeStudentPanel() {
            const panel = document.getElementById('studentPanel');
            const overlay = document.getElementById('overlay');
            panel.classList.remove('open');
            overlay.style.display = 'none';
        }

        document.getElementById('overlay').addEventListener('click', closeStudentPanel);

        // Filter functionality
        document.getElementById('studentSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.student-row');

            rows.forEach(row => {
                const studentName = row.querySelector('.student-name').textContent.toLowerCase();
                const studentIndex = row.cells[2].textContent.toLowerCase();

                if (studentName.includes(searchTerm) || studentIndex.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter buttons
        const filterButtons = document.querySelectorAll('.filter-button');
        filterButtons.forEach((button, index) => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');

                const rows = document.querySelectorAll('.student-row');
                rows.forEach(row => {
                    const studentGPA = parseFloat(row.cells[3].textContent);

                    if (index === 0) { // All
                        row.style.display = '';
                    } else if (index === 1) { // Passed
                        row.style.display = studentGPA >= 2.0 ? '' : 'none';
                    } else if (index === 2) { // Failed
                        row.style.display = studentGPA < 2.0 ? '' : 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>