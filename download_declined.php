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

// Fetch approved hire applications with the new columns
$approved_query = "SELECT h.fName, h.lName, h.age, h.job_position, h.experience_years, h.experience_months, h.education, h.otherEducation, h.suitability_score
                   FROM hiring h
                   JOIN users u ON h.user_id = u.id
                   LEFT JOIN cities c ON h.city_id = c.city_id
                   WHERE h.application_type = 'hiring' AND h.status = 'Declined'";
$approved_result = $conn->query($approved_query);

if (!$approved_result) {
    die("Query Failed: " . $conn->error);
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'First Name');
$sheet->setCellValue('B1', 'Last Name');
$sheet->setCellValue('C1', 'Age');
$sheet->setCellValue('D1', 'Job Position');
$sheet->setCellValue('E1', "Experience\n(Years)");  // Add line break
$sheet->setCellValue('F1', "Experience\n(Months)"); // Add line break
$sheet->setCellValue('G1', 'Education');
$sheet->setCellValue('H1', "Other\nEducation");     // Add line break
$sheet->setCellValue('I1', "Suitability\nScore");    // Add line break
    

// Enable text wrapping for the headers
$sheet->getStyle('A1:I1')->getAlignment()->setWrapText(true);

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
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

// Add data to the spreadsheet
$row = 2;
while ($row_data = $approved_result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $row_data['fName']);
    $sheet->setCellValue('B' . $row, $row_data['lName']);
    $sheet->setCellValue('C' . $row, $row_data['age']);
    $sheet->setCellValue('D' . $row, $row_data['job_position']);
    $sheet->setCellValue('E' . $row, $row_data['experience_years']);
    $sheet->setCellValue('F' . $row, $row_data['experience_months']);
    $sheet->setCellValue('G' . $row, $row_data['education']);
    $sheet->setCellValue('H' . $row, $row_data['otherEducation']);
    $sheet->setCellValue('I' . $row, $row_data['suitability_score']);
    $row++;
}




// Center-align data and adjust font size for data rows
$dataStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'font' => [
        'size' => 12, // Adjusted font size for better readability
    ],
];
$sheet->getStyle('A2:I' . ($row - 1))->applyFromArray($dataStyle);

// Set the font size for headers and make them bold
$headerStyle = [
    'font' => [
        'bold' => true,
        'size' => 14, // Slightly larger font for headers
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);




// Set column widths for better compactness
$sheet->getColumnDimension('A')->setWidth(15); // First Name
$sheet->getColumnDimension('B')->setWidth(15); // Last Name
$sheet->getColumnDimension('C')->setWidth(8);  // Age (compact)
$sheet->getColumnDimension('D')->setWidth(25); // Job Position (slightly narrower)
$sheet->getColumnDimension('E')->setWidth(15); // Experience (Years)
$sheet->getColumnDimension('F')->setWidth(15); // Experience (Months)
$sheet->getColumnDimension('G')->setWidth(18); // Education
$sheet->getColumnDimension('H')->setWidth(15); // Other Education
$sheet->getColumnDimension('I')->setWidth(15); // Suitability Score (compact)


// Set print area and scaling to fit on one page
$sheet->getPageSetup()->setPrintArea('A1:J' . ($row - 1));
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

// Adjust margins to reduce space on the sides
$sheet->getPageMargins()->setLeft(0.2);  // Reduce left margin
$sheet->getPageMargins()->setRight(0.2); // Reduce right margin
$sheet->getPageMargins()->setTop(0.5);   // Optional: Adjust top margin if necessary
$sheet->getPageMargins()->setBottom(0.5);// Optional: Adjust bottom margin if necessary

// Set headers for Excel file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="approved_applications.xlsx"');

// Write the spreadsheet to a file and output it to the browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
