<?php

// blockchain 
// echo "hello blockchain1";
echo "<pre>";

// список методов ['balance','addresses','transfer','','','','']

$method = $_POST['method'];


$api = "https://apirone.com/api/v2/";
$w_id = "btc-104e92b25b0af4f08cd70cf02d56b75e";
$pass = "BuhsdyWTMWLLYSAOQrUcTdGi2NZXWX4T";

// получаю адрес кошелька
$_adreses = $api . "wallets/" . $w_id . "/addresses";
$json_adreses = file_get_contents($_adreses);
$areses_data = json_decode($json_adreses, true);
$my_adreses = $areses_data['addresses'][0]['address'];
$my_adreses_type = $areses_data['addresses'][0]['type'];


// баланс по адрусу полученного коелька
$_adreses_balance = $api . "wallets/" . $w_id . "/addresses/" . $my_adreses . "/balance";
$json_adreses_balance = file_get_contents($_adreses_balance);
$areses_balance_data = json_decode($json_adreses_balance, true);
// доступный баланс для вывода
$available_balance = $areses_balance_data['available'];

// // авторизация для получения токена
// $url_auth = $api . 'auth/login';

// $request_arr = array(
//    "login" => $w_id,
//     "password" => $pass,
// );

// $request_string = json_encode($request_arr);
// $ch = curl_init($url_auth);
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// curl_setopt($ch, CURLOPT_POSTFIELDS, $request_string);
// curl_setopt($ch, CURLOPT_HEADER, true);
// curl_setopt(
//     $ch,
//     CURLOPT_HTTPHEADER,
//     array(
//         'Content-Type: application/json',
//         'charset: utf-8:'
//     )
// );

// $result_auth = curl_exec($ch);
// curl_close($ch);

// выплата
//https://apirone.com/api/v2/wallets/{wallet}/transfer

// $_adreses_balance = $api . "wallets/" . $w_id . "/transfer/" . $my_adreses . "/balance";
// $json_adreses_balance = file_get_contents($_adreses_balance);
// $areses_balance_data = json_decode($json_adreses_balance, true);
// авторизация для получения токена
$url_transfer = $api . 'wallets/' . $w_id . '/transfer';

$request_arr = array(
    "transfer_key" => $pass,
    "addresses" => [$my_adreses],
    "destinations" => [array(
        "address" => "3HmjxAZwoWjsJd8mig89qfn2rzFtxXYW4z",
        "amount" => 400000
    )],
    "fee" => "normal",
    "subtract-fee-from-amount" => false
);

$request_string = json_encode($request_arr);
$ch = curl_init($url_transfer);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_string);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array(
        'Content-Type: application/json'
    )
);

$data_transfer = curl_exec($ch);
curl_close($ch);

$data_transfer = json_decode($data_transfer, true);


print_r($data_transfer['message']);



// https://bots.online-lead.store/_test-script/bitcoin_pay/index.php
