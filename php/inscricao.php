<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$encarregado = $_POST['encarregado'] ?? '';
$educando = $_POST['educando'] ?? '';
$data_nascimento = $_POST['data'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$mensagem = $_POST['mensagem'] ?? '';

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
    $mail->isHTML(false);
    $mail->Subject = "Nova inscrição submetida no site";
    $mail->Body =
        "Foi submetida uma nova inscrição no site:\n\n" .
        "Encarregado de Educação: $encarregado\n" .
        "Nome do Educando: $educando\n" .
        "Data de Nascimento: $data_nascimento\n" .
        "Email: $email\n" .
        "Telefone: $telefone\n" .
        "Mensagem: $mensagem\n";

    $mail->send();
    echo '
    <div style="text-align:center; margin-top:50px;">
        <img src="../img/mascote.png" alt="Mascote" style="width:150px; margin-bottom:20px;">
        <h2>Inscrição enviada com sucesso!</h2>
        <button onclick="window.location.href=\'../index.html\'">Fechar</button>
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
            <img src="../img/mascote.png" alt="Mascote" style="width:150px; margin-bottom:20px;">
            <h2>Inscrição enviada com sucesso!</h2>
            <button onclick="window.location.href=\'../index.html\'">Fechar</button>
        </div>
        ';
    } catch (Exception $e) {
        echo "<script>alert('Erro ao enviar email: {$mail->ErrorInfo}');window.location.href='../HTML/PT/inscricao.html';</script>";
    }
}
