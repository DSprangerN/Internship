<?php
   $host = '127.0.0.1';
   $user = 'root';
   $password = '';
   $database = 'estrelinha_login';

   $conn = new mysqli($host, $user, $password, $database);

   if ($conn->connect_error) {
       die("Falha na conexão: " . $conn->connect_error);
   }
   echo "Conexão bem-sucedida!";
   ?>