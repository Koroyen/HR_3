<?php
session_start();
require 'db.php'; // Ensure your database connection is successful
require 'vendor/autoload.php'; // Include Composer autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Check if user is logged in and is an admin
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch declined hire applications
$declined_query = "SELECT h.fName, h.lName, h.age, h.job_position, h.experience, h.suitability_score, h.interview_date
                   FROM hiring h
                   JOIN users u ON h.user_id = u.id
                   LEFT JOIN cities c ON h.city_id = c.city_id
                   WHERE h.application_type = 'hiring' AND h.status = 'declined'";
$declined_result = $conn->query($declined_query);

if (!$declined_result) {
    die("Query Failed: " . $conn->error);
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers
$sheet->setCellValue('A1', 'First Name');
$sheet->setCellValue('B1', 'Last Name');
$sheet->setCellValue('C1', 'Age');
$sheet->setCellValue('D1', 'Job Position');
$sheet->setCellValue('E1', 'Experience');
$sheet->setCellValue('F1', 'Suitability Score');
$sheet->setCellValue('G1', 'Interview Date');

// Center-align headers
$headerStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'font' => [
        'bold' => true,
    ],
];
$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

// Add data to the spreadsheet
$row = 2;
while ($row_data = $declined_result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $row_data['fName']);
    $sheet->setCellValue('B' . $row, $row_data['lName']);
    $sheet->setCellValue('C' . $row, $row_data['age']);
    $sheet->setCellValue('D' . $row, $row_data['job_position']);
    $sheet->setCellValue('E' . $row, $row_data['experience']);
    $sheet->setCellValue('F' . $row, $row_data['suitability_score']);
    $sheet->setCellValue('G' . $row, $row_data['interview_date']);
    $row++;
}

// Center-align data
$dataStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];
$sheet->getStyle('A2:G' . ($row - 1))->applyFromArray($dataStyle);

// Set column widths for better compactness
$sheet->getColumnDimension('A')->setWidth(15); // First Name
$sheet->getColumnDimension('B')->setWidth(15); // Last Name
$sheet->getColumnDimension('C')->setWidth(10); // Age
$sheet->getColumnDimension('D')->setWidth(20); // Job Position
$sheet->getColumnDimension('E')->setWidth(15); // Experience
$sheet->getColumnDimension('F')->setWidth(20); // Suitability Score
$sheet->getColumnDimension('G')->setWidth(20); // Interview Date

// Set print area and scaling to fit on one page
$sheet->getPageSetup()->setPrintArea('A1:G' . ($row - 1));
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

// Set headers for Excel file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="declined_applications.xlsx"');

// Write the spreadsheet to a file and output it to the browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>