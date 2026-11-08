<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

require_once('TCPDF-main/tcpdf.php');
include 'database/dbconfig.php';

$school_id = mysqli_real_escape_string($con, $_SESSION['school_id']);
$house = mysqli_real_escape_string($con, $_GET['house']);
$roll_dates = mysqli_real_escape_string($con, $_GET['date']);
$select = "SELECT * FROM roll_calls WHERE school_id='$school_id' AND house='$house' AND roll_date='$roll_dates' AND moreven='Morning' ORDER BY level DESC, student";
$sql = mysqli_query($con, $select);

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    private $house;

    public function __construct($house) {
        parent::__construct();
        $this->house = $house;
    }
    // Page header
    public function Header() {
        // Calculate the center position
        $image_file = 'img/Sengshs.jpg';
        $image_width = 15; // Width of the logo
        $image_height = 15; // Height of the logo
        $page_width = $this->getPageWidth();
        $x_position = ($page_width - $image_width) / 2;

        // Center the logo
        $this->Image($image_file, $x_position, 5, $image_width, $image_height, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);

        // Add some space below the logo
        $this->Ln(15);

        // School name
        $this->SetFont('helvetica', 'B', 18);
        $this->Cell(0, 5, 'SERWAA NYARKO GIRLS SENIOR HIGH SCHOOL', 0, 1, 'C');

        // Add some space below the school name
        $this->Ln(1);

        // House name
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 5, $this->house, 0, 1, 'C');
         $this->Ln(5);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF($house);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Serwaa Nyarko Girls Senior High School');
$pdf->SetTitle('Roll Call Report');
$pdf->SetSubject('Roll Call Report');
$pdf->SetKeywords('TCPDF, PDF, roll call, report');

// set default header data
$pdf->SetHeaderData('', 0, '', '');

// set header and footer fonts
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// set font
$pdf->SetFont('helvetica', '', 12);

// add a page
$pdf->AddPage();

// Title
$pdf->Cell(0, 25, 'Roll Call for ' . $roll_dates . ' - Morning', 0, 1, 'C');

// Column titles
$html = '<table border="1" cellpadding="4">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Student</th>
                    <th>Level</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

$rowNumber = 1;
if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_array($sql)) {
        $html .= '<tr>
                    <td>' . $rowNumber . '</td>
                    <td>' . $row['student'] . '</td>
                    <td>' . $row['level'] . '</td>
                    <td>' . $row['roll_status'] . '</td>
                </tr>';
        $rowNumber++;
    }
} else {
    $html .= '<tr>
                <td colspan="4" style="text-align:center; font-weight:bolder">No Roll Call Today</td>
              </tr>';
}

$html .= '</tbody></table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// close and output PDF document
$pdf->Output('roll_call_report.pdf', 'I');
?>
