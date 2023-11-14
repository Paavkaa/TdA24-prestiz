<?php
header('Content-Type: application/json');
$response = [
    'secret' => 'The cake is a lie',
];
echo json_encode($response);