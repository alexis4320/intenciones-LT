<?php
use PHPMailer\PHPMailer\PHPMailer;
//variables para la conexión
require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

// Ahora puedes acceder a las variables de entorno
$servername = $_ENV['DB_HOST'];
$database = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$port = $_ENV['DB_PORT'];

$mail_host = $_ENV['MAIL_HOST'];
$mail_port = $_ENV['MAIL_PORT'];
$mail_username = $_ENV['MAIL_USERNAME'];
$mail_pass = $_ENV['MAIL_PASSWORD'];
$mail_encryption = $_ENV['MAIL_ENCRYPTION'];
$mail_from = $_ENV['MAIL_FROM_ADDRESS'];

// Conexión a la base de datos (Modificar con tus credenciales)
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$email = $_POST['email'];
$fullName = $_POST['fullName'];
$phone = $_POST['phone'];
$parish = $_POST['parish'];
$intentionType = $_POST['intentionType'];
$recipientName = $_POST['recipientName'];
$date = date('Y-m-d H:i:s');
// Validar los datos

if (empty($email) || empty($fullName) || empty($phone) || empty($parish) || empty($intentionType) || empty($recipientName) || empty($date)) {
    die("Error: Todos los campos son requeridos.");
}

// Ejemplo de validación de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: El email no es válido.");
}

if (!filter_var($phone, FILTER_VALIDATE_INT)) {
    die("Error: El telefono no es válido.");
}

// Preparar la consulta SQL para insertar datos
$sql = "INSERT INTO requests (Email, FullName, Phone, Parish, IntentionType, Recipient, Date) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $email, $fullName, $phone, $parish, $intentionType, $recipientName, $date);

// Ejecutar la consulta
if ($stmt->execute()) {
    // Redireccionar a una página después de guardar los datos
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = $mail_host;  // Especifica tu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = $mail_username;  // Tu email SMTP
    $mail->Password = $mail_pass;  // Tu contraseña SMTP
    $mail->SMTPSecure = $mail_encryption;
    $mail->Port = $mail_port;

    $mail->setFrom($mail_from, 'Intenciones');
    $mail->addAddress($email);     // Agregar al destinatario

    $mail->isHTML(true);  // Establecer el formato del email a HTML

    $mail->Subject = 'Intencion Recibida';
    $htmlContent = file_get_contents('../public/bodyMail.html'); // Asegúrate de que la ruta al archivo sea correcta.
    $mail->Body = $htmlContent;

    if (!$mail->send()) {
        echo 'El mensaje no pudo ser enviado.';
        echo 'Error de correo: ' . $mail->ErrorInfo;
    } else {
        header("Location: ../public/confirm.html");
    }
    exit();
} else {
    echo "Error al guardar los datos: " . $stmt->error;
}

// Cerrar la conexión y el statement
$stmt->close();
$conn->close();
?>
