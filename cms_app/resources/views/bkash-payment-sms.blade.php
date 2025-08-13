<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bkash Payment</title>
    <link href="https://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
    <link href='{{ asset('css/style-v1.1.css') }}' rel="stylesheet">
    <link href='{{ asset('css/util.css') }}' rel="stylesheet">
</head>
<style>
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    #bKash_button {
        background-color: #585883;
        padding: 11px 25px;
        color: #ffffff;
        font-size: 15px;
        font-weight: 600;
        border: 1px solid #585883;
        border-radius: 5px;
    }

    #bKash_button:hover {
        background-color: #5252a3;
        border: 1px solid #5252a3;
    }

    .error-message {
        position: absolute;
        margin-bottom: 100px;
        color: red;
        background-color: #ff00001c;
        padding: 10px 20px;
        border-radius: 5px;
    }

    /*span {
        position: absolute;
        margin-top: 52px;
        margin-left: -213px;
        color: red;
    }*/
</style>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">            
                <div class="card p-15" style="min-width: 380px; max-width: 500px;">
                    <form action="{{ route('bkash.get.token') }}" method="POST">
                        <div class="panel panel-default">
                            <div class="panel text-center">
                                <img src="/image/bkash_payment_logo.png" style="width: 250px; padding-top: 15px;">
                            </div>
                            <div class="panel-body">
                                <label for="trxid">SMS Quantity that you want to buy</label>
                                <div class="input-group">
                                    <input type="text" name="sms_quantity" id="sms_quantity" class="form-control">
                                    <span id="sms_quantity_refresh" style="cursor: pointer;" class="input-group-addon"><i class="fa fa-refresh"></i></span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-condensed">
                                        <thead>
                                            <tr>
                                                <td><strong>Detail</strong></td>
                                                <td width="30%" class="text-center"><strong>Amount</strong></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span id="t_sms_quantity"></span></td>
                                                <input style="display: none;" name="amount" id="t_sms_amount_input" required>
                                                <td class="text-center">৳ <span id="t_sms_amount"></span> BDT</td>
                                            </tr>
                                            <tr>
                                                <td class="total-row text-right"><strong>Bkash Fees (1.5%)</strong></td>
                                                <td class="total-row text-center">৳ <span id="t_sms_bkash_fee"></span> BDT</td>
                                            </tr>
                                            <tr>
                                                <td class="total-row text-right"><strong>Total</strong></td>
                                                <td class="total-row text-center">৳ <span id="t_sms_bkash_total"></span> BDT</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {{ csrf_field() }}
                        <div class="container-fluid">
                            <label for="sel2">Choose Payment Type</label>
                            <select class="form-control" id="payment_type_select" name="payment_type" required>
                                <option></option>
                                <option value="payment">Payment</option>
                                <option value="cash_in">Cash In</option>
                            </select>
                            <div id="trxid_input_div" style="display: none; margin-top: 10px;">
                                <div class="m-b-10" style="color: red">Make a payment of amount <span id="payable_amount"></span> TK to 01326599338. Then input the trxID to the following box. Click refresh button to get the trxid.</div>
                                <label for="trxid">Bkash TrxID <span>(Uppercase)</span></label>
                                <div class="input-group">    
                                    <input type="text" name="trxid" class="form-control">
                                    <span style="cursor: pointer;" class="input-group-addon"><i class="fa fa-refresh"></i></span>
                                </div>
                            </div>
                        </div>
                        <input style="display: none;" name="product_type" value="sms">
                        <button id="bKash_button" style="margin: 20px auto; display: flex;">
                            PROCEED
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
</body>

</html>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script> 
<script type="text/javascript">
    $('#payment_type_select').change(function(){
        if ($('#payment_type_select').val() == 'cash_in') {
            $('#trxid_input_div').show()
            $("input[name='trxid']").prop('required',true);
        } else {
            $('#trxid_input_div').hide()
            $("input[name='trxid']").prop('required',false);
        }
    });

    $('#sms_quantity_refresh').click(function(){
        // alert()
        var sms_quantity = $('#sms_quantity').val()
        var amount =  sms_quantity * 0.33
        var bkash_charge = amount * .015
        var total = parseFloat(amount) + parseFloat((amount * .015))
        // console.log(amount)
        $('#t_sms_amount').html(amount.toFixed(2))
        var validity = parseInt(sms_quantity) > 1999 ? 6 : 3;
        $('#t_sms_quantity').html(sms_quantity + "SMS - (0.33 BDT Per SMS - Validity " + validity + " Months)")
        $('#t_sms_bkash_fee').html(bkash_charge.toFixed(2))
        $('#t_sms_bkash_total').html(total.toFixed(2))
        $('#payable_amount').html(Math.ceil(total))
        $('#t_sms_amount_input').val(Math.ceil(total))
    });
</script>

<!-- 
<script type="text/javascript">
    function BkashPayment() {
        // get bkash token
        $.ajax({
            url: "{{ route('bkash.get.token') }}",
            type: 'GET',
            contentType: 'application/json',
            success: function(data) {
                if (data.status == 'fail') {
                    $('#error-message').show();
                } else {
                    $('#loader').show();
                    $('#bKash_button').hide();
                }
            }
        });
    }

    let paymentID = '';
    bKash.init({
        paymentMode: 'checkout',
        paymentRequest: {},
        createRequest: function(request) {
            setTimeout(function() {
                createPayment(request);
            }, 2000)
        },

        executeRequestOnAuthorization: function(request) {
            $.ajax({
                url: '{{ route('bkash.execute.payment') }}',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    "paymentID": paymentID
                }),
                success: function(data) {
                    if (data) {
                        if (data.paymentID != null) {
                            BkashSuccess(data);
                        } else {
                            bKash.execute().onError();
                        }
                    } else {
                        $.get('{{ route('bkash.query.payment') }}', {
                            payment_info: {
                                payment_id: paymentID
                            }
                        }, function(data) {
                            if (data.transactionStatus === 'Completed') {
                                BkashSuccess(data);
                            } else {
                                createPayment(request);
                            }
                        });
                    }
                },
                error: function(err) {
                    bKash.execute().onError();
                }
            });
        },
        onClose: function() {
            // for error handle after close bKash Popup
        }
    });

    function createPayment(request) {
        // Amount already checked and verified by the controller
        // because of createRequest function finds amount from this request
        request['amount'] = 99; // max two decimal points allowed
        $.ajax({
            url: '{{ route('bkash.make.payment') }}',
            data: {
                "amount": 99,
                "_token": "{{ csrf_token() }}",

            },
            type: 'GET',
            contentType: 'application/json',

            success: function(data) {
                console.log(data)
                window.location.href = data.bkash_url;
                bKash.create().onSuccess(data);
                if (data && data.paymentID != null) {
                    paymentID = data.paymentID;
                } else {
                    bKash.create().onError();
                }
            },
            error: function(err) {
                bKash.create().onError();
            }
        });
    }

    function BkashSuccess(data) {
        $.post('{{ route('bkash.success') }}', {
            payment_info: data
        }, function(res) {
            location.reload()
        });
    }
</script>

 -->