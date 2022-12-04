<?php

// скрипт выплаты сумм

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
    echo json_encode($ansver);
    exit;
}


$count_btc = trim($_POST['payWalletCount']);
$pay_transfer_adds = trim($_POST['payWalletAdds']);

if ($count_btc <= 0) {
    // сумма меньше нуля
    $ansver['code'] = 505;
    echo json_encode($ansver);
    exit;
}

$count_satoshi = $count_btc * 100000000;

// данные доступа
$api = "https://apirone.com/api/v2/";
$acc_id = "apr-777"; // acc id
$acc_pass = "234234hgjhg"; // order key

// создаем выплату
$url_invoice = $api . 'accounts/' . $acc_id . '/transfer';

$request_arr = array(
    "currency" => "btc",
    "transfer-key" => $tranfer_key,
    "destinations" => [
        array(
            "address" => $pay_transfer_adds,
            "amount" => (int)$count_satoshi,

        ),
    ],
    "fee" => "normal",
    "subtract-fee-from-amount" => true,
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

echo json_encode($data_invoice);

if (!$data_invoice['message']) {
    $ansver['code'] = 200;
    $ansver['type'] = $data_invoice['type']; // тип операции
    $ansver['fee_btc'] = (int)$data_invoice['fee']['network']['amount'] / 100000000; // сумма комиссии
    $ansver['amount'] = (int)$data_invoice['amount']  / 100000000; // сумма перевода без комиссий
    $ansver['total'] = (int)$data_invoice['total']  / 100000000; // сумма перевода с комиссией
    $ansver['status'] =  'create'; // статус
    $ansver['type'] =  'pay'; // тип операции

    $txs = json_encode($data_invoice['txs']);
    $amount_money = (int)$data_invoice['amount'] / 100000000;
} else {
    $ansver['code'] = 500;
    $ansver['message'] = $data_invoice['message']; // сообщение ошибки
    $ansver['amount'] =  (int)$count_satoshi / 100000000; // статус
    $ansver['status'] =  'canceled'; // статус
    $ansver['created'] = date('Y-m-d H:i:s', time());
}

// запись в базу данных
mysqli_query($conn, "INSERT INTO `operations`(`id`, `user_id`, `status_code`, `status_message`, `invoice_id`, `invoice_url`, `operation_id`, `txs`, `address`, `amount`, `total`, `fee`, `curr_name`, `lifetime`, `status`, `created`, `type`) VALUES (null,'$payer_id','$ansver[code]','$ansver[message]','','','$data_invoice[id]','$txs','$pay_transfer_adds','$ansver[amount]','$ansver[total]','$ansver[fee_btc]','btc','','$ansver[status]','$ansver[created]','pay')");

// mysqli_query($conn, "INSERT INTO `operations`(`id`, `user_id`, `status_code`, `status_message`, `invoice_id`, `invoice_url`, `operation_id`, `txs`, `address`, `amount`, `total`, `fee`, `curr_name`, `lifetime`, `status`, `created`, `type`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10],[value-11],[value-12],[value-13],[value-14],[value-15],[value-16],[value-17])");

// echo mysqli_error($conn);

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
//         "url":"https:\/\/online-lead.ru"},
//     "status":"created",
//     "history":[{"date":"2022-11-30T16:51:53.940231",
//         "status":"created"}],
//     "linkback":"https:\/\/online-lead.ru",
//     "callback-url":null,
//     "invoice-url":"https:\/\/apirone.com\/invoice?id=uc2D3xsN6STODz5c"
// }


// массив подтверждения выплаты
// {
    // "account":"apr-a9649f8c24951bb608717a12384e32e0",
    // "currency":"btc",
    // "created":"2022-12-01T17:47:27.845128",
    // "type":"payment",
    // "id":"cd8f2a13d7fc43cb92f9d6cd7d130d11fdb94a707195b5fccf68f3f769e2449c",
    // "txs":["2c5bbe709c837363c03c115a9261c8754b179da1567e7be9f3928849dca99208"],
    // "destinations":[{"address":"bc1q4agq7d0wfpqku3wrdpdaawzcdtlxtv7hdetf55",
        // "amount":20000},
    // {"address":"bc1qfw8jt83uxtq8szckg0uqq84jyaq02s3wfagnzw",
        // "amount":59583}],
    // "amount":80000,
    // "total":80000,
    // "fee":{"subtract-from-amount":true,
        // "processing":{"address":"bc1q4agq7d0wfpqku3wrdpdaawzcdtlxtv7hdetf55",
        // "amount":20000},
    // "network":{"strategy":"normal",
        // "amount":417,
        // "rate":2.13}},
    // "change-address":"3KMkmdp1uj9Dz4tn3kUisPtngYFnSkdjyd"}{"code":200,
        // "type":"pay",
        // "fee_btc":4.17e-6,
        // "amount":0.0008,
        // "total":0.0008,
        // "status":"create"
// }
