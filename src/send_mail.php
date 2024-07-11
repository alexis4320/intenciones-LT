<?php 

$destinatario = 'correo_destino@example.com';
$asunto = 'Documento PDF generado';
$mensaje = 'Adjunto encontrarás el documento PDF generado.';

// Adjuntar el PDF generado
$archivo_adjunto = 'documento.pdf';
$contenido_adjunto = file_get_contents($archivo_adjunto);
$adjunto = chunk_split(base64_encode($contenido_adjunto));

// Cabeceras para el correo electrónico
$cabeceras = "MIME-Version: 1.0" . "\r\n";
$cabeceras .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";
$cabeceras .= "From: remitente@example.com\r\n";

// Cuerpo del mensaje
$mensaje_correo = "--boundary\r\n";
$mensaje_correo .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
$mensaje_correo .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$mensaje_correo .= $mensaje . "\r\n\r\n";
$mensaje_correo .= "--boundary\r\n";
$mensaje_correo .= "Content-Type: application/pdf; name=\"".$archivo_adjunto."\"\r\n";
$mensaje_correo .= "Content-Transfer-Encoding: base64\r\n";
$mensaje_correo .= "Content-Disposition: attachment; filename=\"".$archivo_adjunto."\"\r\n\r\n";
$mensaje_correo .= $adjunto . "\r\n";
$mensaje_correo .= "--boundary--";

// Enviar correo electrónico
if (mail($destinatario, $asunto, $mensaje_correo, $cabeceras)) {
    echo "Correo electrónico enviado con éxito.";
} else {
    echo "Error al enviar el correo electrónico.";
}

?>
