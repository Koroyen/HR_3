<?php
session_start();
require 'db.php'; // Ensure your database connection is successful

// Check if user is logged in and is an admin
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 1) {
    header("Location: login.php");
    exit();
}

// Age Chart for Scholarship Applicants
$query = "SELECT Age FROM hiring"; // Fetch ages for scholarship applications
$result = mysqli_query($conn, $query);

$ages = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ages[] = $row['Age'];
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}

// Bar Chart: Fetch city data for job applicants (hiring table)
$query = "SELECT c.city_name, COUNT(*) as total_applicants
          FROM hiring h
          JOIN cities c ON h.city_id = c.city_id
          GROUP BY c.city_name";
          
$result = mysqli_query($conn, $query);

$cities = [];
$applicants = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cities[] = $row['city_name'];
        $applicants[] = $row['total_applicants'];
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}

// Pass the bar chart data to JavaScript
echo "<script>
        var cityLabels = " . json_encode($cities) . ";
        var applicantData = " . json_encode($applicants) . ";
      </script>";

// Pie Chart: Fetch status distribution for scholarship applicants (Approved and Declined)
$status_data_query = "SELECT status, COUNT(*) as count FROM hiring WHERE status IN ('Approved', 'Declined') GROUP BY status";
$status_result = mysqli_query($conn, $status_data_query);

$status_data = [];
if ($status_result) {
    while ($row = mysqli_fetch_assoc($status_result)) {
        $status_data[$row['status']] = $row['count'];
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch count of 'Approved' and 'Declined' applications only
$approved_count_query = "SELECT COUNT(*) AS count FROM hiring WHERE status = 'Approved'";
$declined_count_query = "SELECT COUNT(*) AS count FROM hiring WHERE status = 'Declined'";

$approved_result = mysqli_query($conn, $approved_count_query);
$declined_result = mysqli_query($conn, $declined_count_query);

// Fetch the counts
$approved_count = mysqli_fetch_assoc($approved_result)['count'];
$declined_count = mysqli_fetch_assoc($declined_result)['count'];


// Fetch data for the table in the modal (only Approved and Declined)
$applicants_query = "SELECT id, fName, lName, email, status FROM hiring WHERE status IN ('Approved', 'Declined') ORDER BY status";
$applicants_result = mysqli_query($conn, $applicants_query);
$applicants_data = [];
if ($applicants_result) {
    while ($row = mysqli_fetch_assoc($applicants_result)) {
        $applicants_data[] = $row;
    }
} else {
    die("Query failed: " . mysqli_error($conn));
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
    <title>Home Page</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" /> -->
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>

<body class="sb-nav-fixed bg-dark">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="home.php">Microfinance</a>
        <!-- User Dropdown -->
        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
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
                        


                        <div class="sb-sidenav-menu-heading"> Charts </div>
                        <a class="nav-link" href="scholar_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Scholarship Charts
                        </a>
                        <a class="nav-link" href="job_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job Charts
                        </a>
                        <a class="nav-link" href="tesda_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Tesda Charts
                        </a>
                    </div>
                </div>
                
            </nav>
        </div>
        <div id="layoutSidenav_content" class="bg-dark" style="--bs-bg-opacity: .95;">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-light">Charts</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php" class="text-light">Dashboard</a></li>
                        <li class="breadcrumb-item active">Charts</li>
                    </ol>


                    <div class="row">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-area me-1"></i>
                                Scholarship Applicants Age Chart
                            </div>

                            <canvas id="myAreaChart" width="60%" height="10"></canvas>

                            <script>
                                // Debugging output for ages
                                console.log(<?php echo json_encode($ages); ?>); // Check output in console

                                var ages = <?php echo json_encode($ages); ?>;

                                var ctx = document.getElementById("myAreaChart");
                                var myLineChart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: Array.from({
                                            length: ages.length
                                        }, (_, i) => `Applicant ${i + 1}`),
                                        datasets: [{
                                            label: "Age",
                                            backgroundColor: "rgba(2,117,216,0.2)",
                                            borderColor: "rgba(2,117,216,1)",
                                            data: ages,
                                        }],
                                    },
                                    options: {
                                        scales: {
                                            x: {
                                                title: {
                                                    display: true,
                                                    text: 'Applicants'
                                                }
                                            },
                                            y: {
                                                title: {
                                                    display: true,
                                                    text: 'Age'
                                                },
                                                ticks: {
                                                    beginAtZero: true,
                                                    stepSize: 1, // Ensure the y-axis increments by 1
                                                    callback: function(value) {
                                                        if (value % 1 === 0) {
                                                            return value; // Display only whole numbers
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            </script>

                            <div class="card-body">
                                <canvas id="myAreaChart" width="60%" height="10"></canvas>
                            </div>
                        </div>


                        <!-- Bar  Chart  -->
                        <div class="col-lg-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-1"></i>
                                    Cities chart of Applicants
                                </div>
                                <div class="card-body"><canvas id="myBarChart" width="100%" height="50"></canvas></div>
                            </div>
                        </div>




                        

                        <!-- Pie Chart -->
                        <div class="col-lg-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie me-1"></i>
                                    Pie Chart Example
                                </div>
                                <div class="card-body" style="height: 415px;">
                                    <!-- Pie Chart -->
                                    <canvas id="myPieChart" width="70%" height="400"></canvas>
                                </div>
                            </div>

                            <!-- Modal Structure (Place it here, not inside the chart div) -->
                            <div id="statusModal" class="modal">
                                <div class="modal-content">
                                    <span class="close">&times;</span>
                                    <h2>Applicants Status</h2>
                                    <table id="statusTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Application</th>
                                                <th>Status</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Fetch data from the database for the table -->
                                            <?php
                                             // Update the SQL query to include 'application_type'
    $applicants_query = "SELECT id, fName, lName, email, application_type, status FROM hiring WHERE status IN ('Approved', 'Declined') ORDER BY status";

    $applicants_result = mysqli_query($conn, $applicants_query);
    $applicants_data = [];

    if (mysqli_num_rows($applicants_result) > 0) {
        while ($row = mysqli_fetch_assoc($applicants_result)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['fName']}</td>";
            echo "<td>{$row['lName']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['application_type']}</td>"; // Display application_type
            echo "<td>{$row['status']}</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No data available</td></tr>";
    }
    ?>
</tbody>

                                    </table>
                                    <button id="downloadBtn">Download as CSV</button>
                                </div>
                            </div>
                        </div>

                        <style>
                            /* Modal styles for status*/
                            .modal {
                                display: none;
                                position: fixed;
                                z-index: 1;
                                left: 0;
                                top: 0;
                                width: 100%;
                                height: 100%;
                                overflow: auto;
                                background-color: rgba(0, 0, 0, 0.5);
                            }

                            .modal-content {
                                background-color: rgb(175, 173, 173);
                                margin: 10% auto;
                                padding: 20px;
                                border: 1px solid #5e5c5c;
                                width: 50%;
                                border-radius: 5px;
                            }

                            .close {
                                color: #aaa;
                                float: right;
                                font-size: 50px;
                                font-weight: bold;
                            }

                            .close:hover,
                            .close:focus {
                                color: black;
                                text-decoration: none;
                                cursor: pointer;
                            }

                            #statusTable {
                                width: 100%;
                                border-collapse: collapse;
                            }

                            #statusTable th,
                            #statusTable td {
                                border: 1px solid #ddd;
                                padding: 8px;
                                text-align: left;
                            }

                            #statusTable th {
                                background-color: #f2f2f2;
                                font-weight: bold;
                            }

                            #downloadBtn {
                                margin-top: 10px;
                                padding: 10px 20px;
                                background-color: #4CAF50;
                                color: white;
                                border: none;
                                cursor: pointer;
                                border-radius: 5px;
                            }

                            #downloadBtn:hover {
                                background-color: #11da1b;
                            }
                        </style>

                    </div>

                </div>
        </div>
        </main>

    </div>
    </div>



    <!-- area chart -->
    <script>
        // Get the ages data from PHP
        var ages = <?php echo json_encode($ages); ?>;

        // Set new default font family and font color to mimic Bootstrap's default styling
        Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#292b2c';

        // Area Chart Example
        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({
                    length: ages.length
                }, (_, i) => `Applicant ${i + 1}`), // Labels for applicants
                datasets: [{
                    label: "Age",
                    lineTension: 0.3,
                    backgroundColor: "rgba(2,117,216,0.2)",
                    borderColor: "rgba(2,117,216,1)",
                    pointRadius: 5,
                    pointBackgroundColor: "rgba(2,117,216,1)",
                    pointBorderColor: "rgba(255,255,255,0.8)",
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "rgba(2,117,216,1)",
                    pointHitRadius: 50,
                    pointBorderWidth: 2,
                    data: ages, // Use the ages data
                }],
            },
            options: {
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'applicant'
                        },
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            maxTicksLimit: ages.length // Set ticks limit based on number of applicants
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            min: Math.min(...ages) - 5, // Adjust the Y axis min value dynamically
                            max: Math.max(...ages) + 5, // Adjust the Y axis max value dynamically
                            maxTicksLimit: 5
                        },
                        gridLines: {
                            color: "rgba(0, 0, 0, .125)",
                        }
                    }],
                },
                legend: {
                    display: false
                }
            }
        });
    </script>

    <!--  Bar Chart  -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("myBarChart").getContext("2d");

            var myBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cityLabels, // City names from PHP
                    datasets: [{
                        label: 'Number of Applicants',
                        data: applicantData, // Applicant counts from PHP
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1, // Ensure each step is a whole number
                                callback: function(value) {
                                    return Number.isInteger(value) ? value : null;
                                } // Show only integers
                            }
                        }
                    }
                }
            });
        });
    </script>

    <!-- Pie Chart -->
    <script>
      // Pass the PHP data to JavaScript for the pie chart
var approvedCount = <?php echo $approved_count; ?>;
var declinedCount = <?php echo $declined_count; ?>;

// Render the pie chart
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById("myPieChart").getContext("2d");

    var myPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Approved', 'Declined'], // Labels only for Approved and Declined
            datasets: [{
                data: [approvedCount, declinedCount], // Use the PHP data here
                backgroundColor: ['#36A2EB', '#FF6384'],
                hoverBackgroundColor: ['#36A2EB', '#FF6384'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw;
                        }
                    }
                }
            }
        }
    });

    // Modal handling
    var pieChart = document.getElementById("myPieChart");
    var modal = document.getElementById("statusModal");
    var closeBtn = document.querySelector(".close");

    pieChart.onclick = function() {
        modal.style.display = "block";
    };

    closeBtn.onclick = function() {
        modal.style.display = "none";
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Download CSV functionality
    var downloadBtn = document.getElementById("downloadBtn");
    downloadBtn.onclick = function() {
        downloadCSV();
    };

    function downloadCSV() {
        var table = document.getElementById("statusTable");
        var rows = Array.from(table.querySelectorAll("tr"));
        var csvContent = "";

        rows.forEach(function(row) {
            var cols = Array.from(row.querySelectorAll("td, th"));
            var data = cols.map(function(col) {
                return col.innerText;
            }).join(",");
            csvContent += data + "\n";
        });

        var blob = new Blob([csvContent], { type: 'text/csv' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'applicants_status.csv';
        link.click();
    }
});
    </script>


    <!-- Bootstrap Bundle and Other Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="assets/demo/chart-pie-demo.js"></script>

</body>

</html>