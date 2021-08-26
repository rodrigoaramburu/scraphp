<?php 
    if($_SERVER['REQUEST_METHOD'] != 'POST') {
        http_response_code(404);die();
    }
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <div>Nome: <?php echo $_POST['nome'] ?></div>
    <div>Sobrenome: <?php echo $_POST['sobrenome'] ?></div>
    <div>E-mail: <?php echo $_POST['email'] ?></div>
    <div>Senha: <?php echo $_POST['senha'] ?></div>
    
</body>
</html>