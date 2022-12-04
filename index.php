<h1>Страница трансфера</h1>

<div class="forms-container container row row-cols-1 row-cols-md-2">

    <form class="col bg-light py-3" data-form-transfer="recive">
        <h4 class="text-dark">Оплата регистрации</h4>
        <p class="form-text">Оплата регистрации в системе</p>
        <div class="mb-3">
            <label for="reciveWalletCount" class="form-label text-dark">Сумма оплаты в BTC</label>
            <input type="number" step="0.001" class="form-control" name="reciveWalletCount" id="reciveWalletCount">
        </div>
        <div data-form-warning="recive" class="d-none my-3 d-flex justify-content-center text-danger border border-danger rounded py-2">
            Не валид
        </div>
        <button type="submit" data-form-btn-transfer="recive" class="btn btn-primary" disabled>Оплатить</button>
        <p id="linkReciveContainer" class="d-none mt-3 py-2 border border-success rounded text-center">
            <a style="text-decoration: none;" data-form-link-transfer="recive" class="link-success fs-5" href="#" target="_blank">Перейти на страницу оплаты >></a>
        </p>
    </form>


    <form class="col bg-light py-3" data-form-transfer="pay">
        <h4 class="text-dark">Запрос выплаты</h4>
        <p class="form-text">Вывод средств из системы себе наикошелек</p>
        <div data-form-container>
            <div class="mb-3">
                <label for="payWalletAdds" class="form-label text-dark">Адрес вашего кошелька</label>
                <input type="text" class="form-control" name="payWalletAdds" id="payWalletAdds" required>
                <div id="payWalletAddsHelp" class="form-text">Скопируйте и вставьте уникальный хэш вашего крипто-кошелька</div>
            </div>
            <div class="mb-3">
                <label for="payWalletCount" class="form-label text-dark">Сумма выплаты в BTC</label>
                <input type="number" step="0.001" class="form-control" name="payWalletCount" id="payWalletCount">
                <div id="payWalletAddsHelp" class="form-text">Комиссия составляет 0.99% от суммы вывода + комиссия сети около 1-го процента</div>
                <div data-transfer-info="pay" class="d-inline-flex flex-column">
                    <p></p>
                </div>
            </div>
        </div>
        <div data-form-warning="pay" class="d-none my-3 d-flex justify-content-center text-danger border border-danger rounded py-2">
            Не валид
        </div>
        <button type="submit" data-form-btn-transfer="pay" class="btn btn-primary" disabled>Получить</button>
    </form>
</div>

<script>
    var count_recive = document.querySelector('#reciveWalletCount');
    var count_pay = document.querySelector('#payWalletCount');

    var adds_pay = document.querySelector('#payWalletAdds');

    var forms_transfer = document.querySelectorAll('[data-form-transfer]');
    let text_btn_res = '';

    // 
    forms_transfer.forEach(element => {
        element.addEventListener('submit', e => {
            e.preventDefault();
            // console.log(element.dataset.formTransfer);
            document.querySelector(`[data-form-btn-transfer="${element.dataset.formTransfer}"]`).setAttribute('disabled', 'disabled');
            document.querySelector(`[data-form-btn-transfer="${element.dataset.formTransfer}"]`).innerHTML = `<div class="d-flex px-4 justify-content-center">
            <div style="font-size: 10px;width: 20px;height: 20px;" class="spinner-border text-light" role="status">
            <span class="visually-hidden">Загрузка...</span>
            </div></div>`;

            // document.querySelector(`[data-form-transfer="${element.dataset.formTransfer}"]`).serialize();
            let data = $(`[data-form-transfer="${element.dataset.formTransfer}"]`).serialize();
            // console.log('data');
            // console.log(data);
            if (element.dataset.formTransfer == 'recive') {
                text_btn_res = 'Оплатить';
            } else if (element.dataset.formTransfer == 'pay') {
                text_btn_res = 'Получить';

            }

            reciveGo(element.dataset.formTransfer, data);
        })
    });

    function reciveGo(formTransfer, data) {
        $.ajax({
            type: "post",
            url: "/" + formTransfer,
            data: data,
            success: function(res) {
                document.querySelector(`[data-form-btn-transfer="${formTransfer}"]`).innerHTML = text_btn_res;
                document.querySelector('#reciveWalletCount').value = '';

                console.log(res);
                let resData = JSON.parse(res);
                console.log(resData);

                switch (resData.code) {
                    case 200:
                        if (resData.type === "receiving") {
                            document.querySelector(`#linkReciveContainer`).classList.remove('d-none');
                            document.querySelector(`[data-form-link-transfer="recive"]`).setAttribute('href', resData.linkPay);

                            document.querySelector(`[data-form-btn-transfer="recive"]`).classList.add('d-none');
                        } else if (resData.type === "pay") {
                            document.querySelector(`[data-form-container]`).classList.add('d-none');
                        }


                        break;

                    case 500:
                        document.querySelector(`[data-form-warning="${formTransfer}"]`).classList.remove('d-none');
                        document.querySelector(`[data-form-warning="${formTransfer}"]`).textContent = "Ошибка, попробуйте позже";
                        break;
                    case 505:
                        document.querySelector(`[data-form-warning="${formTransfer}"]`).classList.remove('d-none');
                        document.querySelector(`[data-form-warning="${formTransfer}"]`).textContent = "Сумма должна быть больше 0";
                        break;
                    case 507:
                        document.location.href='/'
                        break;
                }


            }
        });
    }

    //вывод
    count_pay.addEventListener('input', e => {
        if (count_pay.value > 0) {
            document.querySelector('[data-form-btn-transfer="pay"]').removeAttribute('disabled');
        } else if (count_pay.value < 0) {
            count_pay.value = 0;
            document.querySelector('[data-form-btn-transfer="pay"]').setAttribute('disabled', 'disabled');
        } else if (count_pay.value == 0 || count_pay.value === 0) {
            document.querySelector('[data-form-btn-transfer="pay"]').setAttribute('disabled', 'disabled');
        }

        let count_fee = (Number(count_pay.value) / 100) * 2;
        document.querySelector('#payWalletAddsHelp_count').textContent = Number(count_pay.value) - Number(count_fee);
    })

    //оплата
    count_recive.addEventListener('input', e => {
        if (count_recive.value > 0) {
            document.querySelector('[data-form-btn-transfer="recive"]').removeAttribute('disabled');
        } else if (count_recive.value < 0) {
            count_recive.value = 0;
            document.querySelector('[data-form-btn-transfer="recive"]').setAttribute('disabled', 'disabled');
        } else if (count_recive.value == 0 || count_recive.value === 0) {
            document.querySelector('[data-form-btn-transfer="recive"]').setAttribute('disabled', 'disabled');
        }
        let count_fee = (Number(count_recive.value) / 100) * 2;

    });
</script>