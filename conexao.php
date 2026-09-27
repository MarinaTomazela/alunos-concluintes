<?php

/*
 * Arquivo responsável pela conexão com o banco de dados.
 * O PDO foi utilizado para facilitar o tratamento de erros
 * e permitir consultas preparadas.
 */

$servidor = 'localhost';
$banco = 'pwii';
$usuario = 'root';
$senha = '';

try {
    $conexao = new PDO(
        "mysql:host={$servidor};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha
    );

    $conexao->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexao->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );
} catch (PDOException $erro) {
    die(
        'Não foi possível conectar ao banco de dados. ' .
        'Verifique se o MySQL está iniciado.'
    );
}