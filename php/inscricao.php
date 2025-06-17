<?php

$encarregado = $_POST['encarregado'] ?? '';
$educando = $_POST['educando'] ?? '';
$data_nascimento = $_POST['data_nascimento'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$mensagem = $_POST['mensagem'] ?? '';

$admin_email = 'estrelinhaamarela2018@gmail.com';

$subject = "Nova inscrição submetida no site";
$body = "Foi submetida uma nova inscrição no site:\n\n";
$body .= "Encarregado de Educação: $encarregado\n";
$body .= "Nome do Educando: $educando\n";
$body .= "Data de Nascimento: $data_nascimento\n";
$body .= "Email: $email\n";
$body .= "Telefone: $telefone\n";
$body .= "Mensagem: $mensagem\n";

$headers = "From: noreply@estrelinhaamarela2018.com\r\n";

mail($admin_email, $subject, $body, $headers);

echo "<script>alert('Inscrição enviada com sucesso!');window.location.href='../../indexPT.html';</script>";
