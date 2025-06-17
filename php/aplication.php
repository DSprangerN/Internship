<?php

$guardian = $_POST['guardian'] ?? '';
$child = $_POST['child'] ?? '';
$dob = $_POST['dob'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

$admin_email = 'estrelinhaamarela2018@gmail.com';

$subject = "Nova inscrição submetida no site";
$body = "Foi submetida uma nova inscrição no site:\n\n";
$body .= "Encarregado de Educação: $guardian\n";
$body .= "Nome do Educando: $child\n";
$body .= "Data de Nascimento: $dob\n";
$body .= "Email: $email\n";
$body .= "Telefone: $phone\n";
$body .= "Mensagem: $message\n";

$headers = "From: noreply@estrelinhaamarela2018.com\r\n";

mail($admin_email, $subject, $body, $headers);

echo "<script>alert('Application sent successfully!');window.location.href='../../indexEN.html';</script>";
