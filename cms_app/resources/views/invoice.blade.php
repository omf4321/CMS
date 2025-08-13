<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BT-GEC• - Invoice #00{{$invoice->id}}</title>

    <link href="{{ asset('css/invoice-bootstarp-all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/invoice.css') }}" rel="stylesheet">
    <script src="{{ asset('js/invoice-scripts.min.js') }}"></script>

</head>
<body>

    <div class="container-fluid invoice-container">
            <div class="row invoice-header">
                <div class="invoice-col">
                    <!-- <p><img src="//clients.hostnil.com/assets/img/logo.png" title="Host Nil•" /></p> -->
                    <h3>Invoice #00{{$invoice->id}}</h3>
                </div>
                <div class="invoice-col text-center">
                    <div class="invoice-status">
                        @if($invoice->status == 'paid')
                            <span class="paid">Paid</span>
                        @else
                            <span class="unpaid">{{ucfirst($invoice->status)}}</span>
                        @endif
                    </div>
                </div>
            </div>
            <hr>

            <div class="row">
                <!-- <div class="invoice-col right">
                    <strong>Pay To</strong>
                    <address class="small-text">
                        95/A R.K. Mission Road, Gopibag, Motijheel, Dhaka 1203
                    </address>
                </div> -->
                <div class="invoice-col">
                    <strong>Invoiced To</strong>
                    <address class="small-text">
                        {!! $invoice->invoice_to !!}
                    </address>
                </div>
            </div>

            <div class="row">
                <!-- <div class="invoice-col right">
                    <strong>Payment Method</strong><br>
                    <span class="small-text" data-role="paymethod-info">bKash</span>
                    <br /><br />
                </div> -->
                <div class="invoice-col">
                    <strong>Invoice Date</strong><br>
                    <span class="small-text">
                        {{$invoice->created_at}}<br><br>
                    </span>
                </div>
            </div>

            <br />

            
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>Invoice Items</strong></h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <td><strong>Description</strong></td>
                                    <td width="20%" class="text-center"><strong>Amount</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Basic Coaching Management Web Application ({{$invoice->date_from}} - {{$invoice->date_to}})</td>
                                    <td class="text-center">৳ {{$invoice->amount}} BDT</td>
                                </tr>
                                <!-- <tr>
                                    <td>Bkash Fees (1.5%)</td>
                                    <td class="text-center">৳ {{$invoice->bkash_fees}} BDT</td>
                                </tr> -->
                                <tr>
                                    <td class="total-row text-right"><strong>Sub Total</strong></td>
                                    <td class="total-row text-center">৳ {{$invoice->amount}} BDT</td>
                                </tr>
                                <tr>
                                    <td class="total-row text-right"><strong>Total</strong></td>
                                    <td class="total-row text-center">৳ {{$invoice->amount}} BDT</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="transactions-container small-text">
                <div class="table-responsive">
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <td class="text-center"><strong>Transaction Date</strong></td>
                                <td class="text-center"><strong>Gateway</strong></td>
                                <td class="text-center"><strong>Transaction ID</strong></td>
                                <td class="text-center"><strong>Amount</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">{{$invoice->transaction_date}}</td>
                                <td class="text-center">{{$invoice->payment_method}}</td>
                                <td class="text-center">{{$invoice->trxid}}</td>
                                <td class="text-center">৳ {{$invoice->paid}} BDT</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="3"><strong>Due</strong></td>
                                <td class="text-center text-red">৳ {{$invoice->amount - $invoice->paid}} BDT</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pull-right btn-group btn-group-sm hidden-print">
                <a style="margin-right: 10px" href="javascript:window.print()" class="btn btn-info"><i class="fa fa-print" style="margin-right: 5px;"></i>Print</a>
                <a href="/bkash-payment/{{$invoice->id}}" class="btn btn-success"><i class="fa fa-credit-card" style="margin-right: 5px;"></i>Pay Now</a>
                <!-- <a href="dl.php?type=i&amp;id=459" class="btn btn-default"><i class="fas fa-download"></i> Download</a> -->
            </div>    
    </div>

    <p class="text-center hidden-print" style="padding-bottom: 20px; font-size: 20px;"><a href="/admin/home/billing_invoice">Back to Invoice List</a></a></p>

</body>
</html>
