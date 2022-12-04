<?php
// 

$ansver = array();
$payer_id = '';
if ($_SESSION) {
    $payer_id_sess = $_SESSION['id'];

    $data_payer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `users` where id='$payer_id_sess'"));
    if (isset($data_payer)) {
        $payer_id = $data_payer['id'];
    }
} else {
    // отсутствует сессия нужно авторизоваться
    $ansver['code'] = 507;
    exit;
}



$count_btc = trim($_POST['reciveWalletCount']);

if ($count_btc <= 0) {
    // сумма меньше нуля
    $ansver['code'] = 505;
    exit;
}

$count_satoshi = $count_btc * 100000000;

$api = "https://apirone.com/api/v2/";
$acc_id = "apr-777"; // acc id
$acc_pass = "234234hgjhg"; // order key
$lifetime = 3600;

// создаем инвойс
$url_invoice = $api . 'accounts/' . $acc_id . '/invoices';

$request_arr = array(
    "amount" => (int)$count_satoshi,
    "currency" => "btc",
    "lifetime" => $lifetime,
    "user-data" => array(
        "merchant" => "название Магазин",
        "url" => "https://link.ru", // по клику на merchant переходим сюда
    ),
    "linkback" => "https://link.ru" // редирект после оплаты
);

$request_string = json_encode($request_arr);
$ch = curl_init($url_invoice);
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

$data_invoice = curl_exec($ch);
curl_close($ch);

$data_invoice = json_decode($data_invoice, true);

if (!$data_invoice['message']) {
    $ansver['code'] = 200;
    $ansver['type'] = $data_invoice['type']; // тип операции
    $ansver['amount'] = (int)$data_invoice['amount']  / 100000000; // сумма перевода без комиссий в битках
    $ansver['total'] = (int)$data_invoice['total']  / 100000000; // сумма перевода с комиссией
    $ansver['status'] =  'create'; // статус
    $ansver['type'] =  'receiving'; // тип операции
    $ansver['linkPay'] = $data_invoice['invoice-url']; // страница оплаты
    $ansver['created'] = $data_invoice['created'];

    $invoice_url = $data_invoice['invoice-url'];

    $amount_money = (int)$data_invoice['amount'] / 100000000;
} else {
    $ansver['code'] = 500;
    $ansver['message'] = $data_invoice['message']; // сообщение ошибки
    $ansver['amount'] =  (int)$count_satoshi / 100000000; // сумма перевода в битках
    $ansver['status'] =  'canceled'; // статус
    $ansver['created'] = date('Y-m-d H:i:s', time());
}

// запись в базу данных
mysqli_query($conn, "INSERT INTO `operations`(`id`, `user_id`, `status_code`, `status_message`, `invoice_id`, `invoice_url`, `operation_id`, `txs`, `address`, `amount`, `total`, `fee`, `curr_name`, `lifetime`, `status`, `created`, `type`) VALUES (null,'$payer_id','$ansver[code]','$ansver[message]','$data_invoice[invoice]','$invoice_url','$data_invoice[id]','$data_invoice[txs]','$pay_transfer_adds','$ansver[amount]','$ansver[total]','$ansver[fee_btc]','btc','$lifetime','$ansver[status]','$ansver[created]','receiving')");

echo json_encode($ansver);


// {
//     "account":"apr-a9649f8c24951bb608717a12384e32e0",
//     "invoice":"uc2D3xsN6STODz5c",
//     "created":"2022-11-30T16:51:53.940231",
//     "currency":"btc",
//     "address":"3B1HsYdSpUd4hvd88h4hSV1R9MjjG8g3DA",
//     "expire":"2022-11-30T17:51:53.940231",
//     "amount":200000000,
//     "user-data":{"merchant":"1-9-90 \u041c\u0430\u0433\u0430\u0437\u0438\u043d",
//         "url":"https:\/\/link.ru"},
//     "status":"created",
//     "history":[{"date":"2022-11-30T16:51:53.940231",
//         "status":"created"}],
//     "linkback":"https:\/\/link.ru",
//     "callback-url":null,
//     "invoice-url":"https:\/\/apirone.com\/invoice?id=uc2D3xsN6STODz5c"
// }
