<?php
require('../public/libs/fpdf/fpdf.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

// Configura la conexión a la base de datos
$servername = $_ENV['DB_HOST'];
$database = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$port = $_ENV['DB_PORT'];

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
// Consulta para obtener la lista de nombres
$sql = "SELECT Recipient FROM requests WHERE IntentionType = 'Difunto'";
$result = $conn->query($sql);

// Genera el PDF
class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('../public/img/CLERIGOs_LOGO_RECORTADA.png',10,6,30);
        // Título
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Lista de Difuntos',0,1,'C');
        $this->Ln(10);
    }

    function Footer() {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        // Número de página
        $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function FancyTable($header, $data) {
        // Colores, ancho de línea y fuente en negrita
        $this->SetFillColor(255,0,0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        // Cabeceras
        $w = array(75, 85);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();
        // Restauración de colores y fuentes
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Datos
        $fill = false;
        foreach($data as $row) {
            $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
            $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
            // $this->Cell($w[2],6,$row[2],'LR',0,'L',$fill);
            // $this->Cell($w[3],6,$row[3],'LR',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Línea de cierre
        $this->Cell(array_sum($w),0,'','T');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Títulos de las columnas
$header = array('Nombre', 'Nombre');
// Datos de ejemplo
$data = array();
while($row = $result->fetch_assoc()) {
    $data[] = array($row['Recipient'], '');
}
$pdf->FancyTable($header,$data);
$pdf->Output('F', '../public/docs/lista_difuntos'.date("d-m-Y").'.pdf');

// Enviar el PDF por correo electrónico
// $mail = new PHPMailer(true);
// try {
//     // Configuración del servidor
//     $mail->SMTPDebug = 0;
//     $mail->isSMTP();
//     $mail->Host = 'smtp.example.com';
//     $mail->SMTPAuth = true;
//     $mail->Username = 'tu_email@example.com';
//     $mail->Password = 'tu_contraseña';
//     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//     $mail->Port = 587;

//     // Destinatarios
//     $mail->setFrom('tu_email@example.com', 'Nombre');
//     $mail->addAddress('destinatario@example.com');

//     // Contenido del correo
//     $mail->isHTML(true);
//     $mail->Subject = 'Lista de Nombres Semanal';
//     $mail->Body    = 'Adjunto encontrarás la lista de nombres para esta semana.';
//     $mail->addAttachment('path/to/lista_nombres.pdf');

//     $mail->send();
//     echo 'El mensaje ha sido enviado';
// } catch (Exception $e) {
//     echo "El mensaje no pudo ser enviado. Mailer Error: {$mail->ErrorInfo}";
// }

$conn->close();
?>
