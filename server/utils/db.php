<?php

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../../database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die(json_encode(['code' => 500, 'msg' => 'connect error: ' . $e->getMessage()]));
}
