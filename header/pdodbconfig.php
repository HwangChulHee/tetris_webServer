<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=tetris;charset=utf8',
        'hch','4597');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e){
    echo $output = 'DB 접속 불가'.'<br>' . $e;
    echo $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
}

?>