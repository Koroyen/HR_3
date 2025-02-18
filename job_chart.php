<?php
// Include your database connection
include('db.php');
session_start();

// Fetch data from the hiring table
$query = "SELECT lName, suitability_score FROM hiring";
$result = mysqli_query($conn, $query);

$data = [];
while($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Classify suitability scores for the pie chart (low, medium, high)
$low = 0;
$medium = 0;
$high = 0;

foreach ($data as $applicant) {
    $score = (float) $applicant['suitability_score'];
    if ($score < 2.0) {
        $low++;
    } elseif ($score >= 2.0 && $score < 4.0) {
        $medium++;
    } else {
        $high++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Job Suitability Prediction</title>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        /* Flexbox layout for the charts */
        .chart-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Adjust the bar chart size */
        .chart-item-bar {
            width: 58%; /* Adjust the width of the bar chart (bigger) */
        }

        /* Adjust the pie chart size */
        .chart-item-pie {
            width: 38%; /* Smaller width for pie chart */
        }

        /* Global canvas styling for responsiveness */
        canvas {
            max-width: 100%;
            height: auto;
        }

        /* Sidebar nav fix */
        .sb-sidenav-menu {
            overflow-y: auto; /* Ensure scroll if content is too large */
        }
    </style>
</head>

<body class="sb-nav-fixed bg-dark">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee_job.php">Microfinance</a>

        <!-- Navbar Toggle Button for collapsing navbar -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark " aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Analytics</div>
                        <a class="nav-link" href="job_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job Charts
                        </a>
                        <div class="sb-sidenav-menu-heading">Lists</div>
                        <a class="nav-link" href="job_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Application list
                        </a>
                        <a class="nav-link" href="hr_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applicants
                        </a>
                        <a class="nav-link" href="predict_suitability.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Suitability Score
                        </a>
                        <a class="nav-link" href="reports.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Reports
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content" class="bg-dark-low">
            <div class="container-fluid px-4 bg-dark-low">
                <h1 class="mt-4 text-light">Job Suitability Charts</h1>

                <div class="chart-container">
                    <!-- Bar Chart for lName and Suitability Score -->
                    <div class="chart-item-bar">
                        <canvas id="barChart"></canvas>
                    </div>

                    <!-- Pie Chart for Suitability Score Distribution -->
                    <div class="chart-item-pie">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include necessary scripts (Bootstrap, Chart.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/scripts.js"></script>

    <!-- JavaScript to handle charts -->
    <script>
        var chartData = <?php echo json_encode($data); ?>;
        
        // Bar Chart Data (lName and Suitability Score)
        var barCtx = document.getElementById('barChart').getContext('2d');
        var barLabels = chartData.map(data => data.lName);
        var barScores = chartData.map(data => parseFloat(data.suitability_score));

        var barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    label: 'Suitability Score',
                    data: barScores,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5
                    }
                }
            }
        });

        // Pie Chart Data (Low, Medium, High Suitability Score)
        var pieCtx = document.getElementById('pieChart').getContext('2d');
        var pieLabels = ['Low', 'Medium', 'High'];
        var pieData = [<?php echo $low; ?>, <?php echo $medium; ?>, <?php echo $high; ?>];

        var pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                    hoverOffset: 4
                }]
            }
        });

        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.body.classList.toggle('sb-sidenav-toggled');
        });
    </script>
</body>

</html>
