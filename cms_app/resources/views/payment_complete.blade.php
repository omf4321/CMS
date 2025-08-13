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
    #card {
      position: relative;
      width: 320px;
      display: block;
      margin: 40px auto;
      text-align: center;
      font-family: 'Source Sans Pro', sans-serif;
    }

    #upper-side.successful {
      padding: 2em;
      background-color: #8BC34A;
      display: block;
      color: #fff;
      /*border-top-right-radius: 8px;*/
      /*border-top-left-radius: 8px;*/
    }
    #upper-side:not(.successful) {
      padding: 2em;
      background-color: #FF0000;
      display: block;
      color: #fff;
      /*border-top-right-radius: 8px;*/
      /*border-top-left-radius: 8px;*/
    }

    #checkmark {
      font-weight: lighter;
      fill: #fff;
      margin: -3.5em auto auto 20px;
    }

    #status {
      font-weight: lighter;
      text-transform: uppercase;
      letter-spacing: 2px;
      font-size: 20px;
      margin-top: -.2em;
      margin-bottom: 0;
    }

    #lower-side {
      padding: 2em 2em 5em 2em;
      background: #fff;
      display: block;
      border-bottom-right-radius: 8px;
      border-bottom-left-radius: 8px;
    }

    #message {
      margin-top: -.5em;
      color: #757575;
      letter-spacing: 1px;
    }

    #contBtn {
      position: relative;
      top: 1.5em;
      text-decoration: none;
      background: #1b6478;
      color: #fff;
      margin: auto;
      padding: .8em 3em;
    }
</style>

<body>
    <section>
        <div class="rt-container">
            <div class="col-rt-12">
                <div class="Scriptcontent" style="display: flex; justify-content: center; align-items: center; height: 100vh;">
                    <!-- partial:index.partial.html -->
                    <!-- {{$payment}} -->
                        <div id="card" class="animated fadeIn card">
                        <div class="panel panel-default" style="border: none; margin-bottom: 0px;">
                            <div class="panel text-center" style="border: none; margin-bottom: 0px; padding-bottom: 10px;">
                                <img src="/image/bkash_payment_logo.png" style="width: 250px; padding-top: 15px;">
                            </div>
                        </div>
                            <div id="upper-side" class="{{$payment->status}}">
                                @if($payment->status == 'successful')
                                    <span class="fa fa-check-square-o fs-50"></span>
                                    <h3 id="status" class="m-t-5 fw-600">Success</h3>
                                @else 
                                    <span class="fa fa-times-circle-o fs-50"></span>
                                    <h2 id="status" class="m-t-5 fw-600">Failure</h2>
                                @endif
                            </div>
                            <div id="lower-side">
                                @if($payment->status == 'successful')
                                    <p id="message">Thank you for your payment. Your payment has been successfully received and updated in your account.</p>
                                    @if($payment->type == 'software')
                                        <a href="/admin/home" id="contBtn">Go to Dashboard</a>
                                    @else
                                        <a href="/admin/home/recharge_history" id="contBtn">Go Back</a>
                                    @endif
                                @else
                                    <p id="message">Unable to process the payment because invalid data was supplied with the transaction or you have insufficient balance.</p>
                                    @if($payment->type == 'software')
                                        <a href="/admin/home/billing_invoice" id="contBtn">Go Back</a>
                                    @else
                                        <a href="/admin/home/recharge_history" id="contBtn">Go Back</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    <!-- partial -->
                </div>
            </div>
        </div>
    </section>

</body>

</html>



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