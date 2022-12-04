<?php
// история апдейта инвойса
//require_once 'config/db/db_conn.php';

$ansver = array();


$user_id = 26;

$api = "https://apirone.com/api/v2/";
$acc_id = "apr-777"; // acc id
$acc_pass = "234234hgjhg"; // order key

$operation_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `operations` WHERE id='$user_id' order by `created` desc limit"));

$invoice_id = 'C3caTCzKKOzY0n5f';
// $_invoice_history = $api . "invoices/" . $invoice_id;
$_invoice_history = $api . "accounts/" . $acc_id . "/invoices/'.$invoice_id.'?transfer-key=" . $acc_pass;
// accounts/{AccountID}/invoices/{InvoiceID}

// создаем инвойс
$url_invoice = $api . 'accounts/' . $acc_id . '/invoices/' . $invoice_id . '?transfer-key=' . $acc_pass;

$request_string = json_encode($request_arr);
$ch = curl_init($url_invoice);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
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

$data_invoice = curl_exec($ch);
curl_close($ch);
$data = json_decode($data_invoice, true);

// общий массив изменений данного инвойса
$data_history = $data['history'];

// последнее из массива историй
$end_history = array_pop($data_history);

mysqli_query($conn, "UPDATE `operations` SET status='$end_history[status]' WHERE invoice_id='$invoice_id'");


// UPDATE `operations` SET `id`=[value-1],`user_id`=[value-2],`status_code`=[value-3],`status_message`=[value-4],`invoice_id`=[value-5],`invoice_url`=[value-6],`operation_id`=[value-7],`txs`=[value-8],`address`=[value-9],`amount`=[value-10],`total`=[value-11],`fee`=[value-12],`curr_name`=[value-13],`lifetime`=[value-14],`status`=[value-15],`created`=[value-16],`type`=[value-17] WHERE 1