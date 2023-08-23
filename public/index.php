<?php

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$body = file_get_contents('php://input');
$payload = json_decode($body);

$apiKey = "";

require_once(__DIR__ . "/chatgpt.php");

send_chatgpt_message($payload, $apiKey);