<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$guardian = $_POST['guardian'] ?? '';
$child = $_POST['child'] ?? '';
$dob = $_POST['dob'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'mail.aestrelinhaamarela.pt';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@aestrelinhaamarela.pt';
    $mail->Password = 'Familia20*';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('info@aestrelinhaamarela.pt', 'Estrelinha Amarela');
    $mail->addAddress('estrelinhaamarela2018@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = "New application submitted on the website";
    $mail->Body = nl2br(
        "A new application has been submitted on the website:<br><br>"
            . "Parent/Guardian Name: $guardian<br>"
            . "Child's Name: $child<br>"
            . "Child's Date of Birth: $dob<br>"
            . "Email: $email<br>"
            . "Phone: $phone<br>"
            . "Message: $message<br>"
    );

    $mail->send();
    echo '
    <div style="text-align:center; margin-top:50px;">
        <img src="../img/mascote.png" alt="Mascot" style="width:150px; margin-bottom:20px;">
        <h2>Registration sent successfully!</h2>
        <button onclick="window.location.href=\'../indexEN.html\'">Close</button>
    </div>
    ';
} catch (Exception $e) {
    // Tenta com TLS/587 se SSL/465 falhar
    try {
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->send();
        echo '
        <div style="text-align:center; margin-top:50px;">
            <img src="../img/mascote.png" alt="Mascot" style="width:150px; margin-bottom:20px;">
            <h2>Registration sent successfully!</h2>
            <button onclick="window.location.href=\'../indexEN.html\'">Close</button>
        </div>
        ';
    } catch (Exception $e) {
        echo "<script>alert('Error sending email: {$mail->ErrorInfo}');window.location.href='../HTML/EN/registration.html';</script>";
    }
}
