<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title></title>
    <!-- Latest compiled and minified CSS -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/custom.css') }}">


    <style>

    </style>
</head>

<body>
    <div class="row">
       
        <div class="col-xs-3" style="">
            <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" />
        </div>

        <div class="col-xs-8">

            @if ($data[0]->branch->business_name)
                <p class="company-name">
                    {{ $data[0]->branch->business_name }}
                </p>
            @endif



        </div>
    </div>
    <div class="row mt-3">


        <div class="col-xs-4 left-cont">
            <h3>Customer</h3>
            <p> {{ $data[0]->branch->business_name }}</p>
            @if ($data[0]->branch->branch)
                <p>Branch: {{ $data[0]->branch->branch }}</p>
            @endif

            @if ($data[0]->branch->postal_address)
                <p>Address: {{ $data[0]->branch->postal_address }}</p>
            @endif
            @if ($data[0]->branch->address)
                <p> {{ $data[0]->branch->address }}</p>
            @endif

        </div>

        <div class="col-xs-4">

        </div>
        <div class="col-xs-4 right-cont">
            <p>
            <h3>Purchase Order</h3>
            </p>
            <p> <b>Order No: </b>{{ $data[0]->order_no }}</p>
            <p> <b>Receipt Date: </b>{{ $data[0]->receipt_date }}</p>
        </div>
    </div>

    <div class="row bg-dark" style="margin-top:2rem;text:center;">
        <div class="col-xs-4">
            <h4><b class="title_2"> Vendor</b></h4>
        </div>
        <div class="col-xs-4"></div>
        <div class="col-xs-4">
            <h4><b class="title_2"> Shipping Address</b></h4>
        </div>
    </div>

    <div class="row" style="padding-top:2rem;">

        <div class="col-xs-4 left-cont">

            <p style="margin-left:0.6rem"><b>
                    <h4>{{ $data[0]->supplier->company_name }}</h4>
                </b></p>

            @if ($data[0]->supplier->company_phone)
                <p>Phone: {{ $data[0]->supplier->company_phone }} </p>
            @endif
            @if ($data[0]->supplier->company_email)
                <p>Email: {{ $data[0]->supplier->company_email }} </p>
            @endif
            @if ($data[0]->supplier->country)
                <p>Country: {{ $data[0]->supplier->country }}/{{ $data[0]->supplier->city }} </p>
            @endif
            @if ($data[0]->supplier->postal_address)
                <p>{{ $data[0]->supplier->postal_address }} / {{ $data[0]->supplier->address }} </p>
            @endif

        </div>
        <div class="col-xs-4"></div>
        <div class="col-xs-4 right-cont">


            @if ($data[0]->shipping_address)
                <p>Branch: {{ $data[0]->shipping_address }}</p>
            @else
                <p>Branch: {{ $data[0]->branch->address }}</p>
            @endif
            @if ($data[0]->branch->phone_no)
                <p>Phone: {{ $data[0]->branch->phone_no }}</p>
            @endif
            @if ($data[0]->branch->email)
                <p>Email: {{ $data[0]->branch->email }}</p>
            @endif

        </div>

    </div>

    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>PURCHASE ORDER</h3>
        </div>
    </div>

    <hr>
    <table style="margin-top: 2rem;margin-bottom:2rem;">
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Requested By: </b></td>
                <td><b>Department</b></td>
                <td><b>Deadline Date</b></td>

            </tr>
        </thead>
        <tr style="background:#eceff1;">
            <td>
                <p class="title_2"> {{ $data[0]->user->name }}</p>
            </td>
            <td>
                <p> <b class="title_2">{{ $data[0]->user->department->department }}</p>
            </td>
            <td>
                <p class="title_2">{{ $data[0]->order_deadline }}</p>
            </td>
        </tr>
    </table>

    <table class="table ">
        <thead class="bg-dark">
            <tr>
                <td><b>Code</b></td>
                <td><b>Item Name</b></td>
                <td><b>Unit</b></td>

                <td><b>Qty</b></td>
                <td><b>Unit Price</b></td>
                <td><b>Total</b></td>
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @php($total_tax = 0)
            @foreach ($data as $item)
                <tr>
                    <td>
                        <p class="item">{{ $item->stock->code }}</p>
                    </td>
                    <td>
                        <p class="item">{{ $item->stock->name }}</p>
                    </td>
                    <td>
                        <p class="item">{{ $item->stock->unit->name }}</p>
                    </td>

                    <td>
                        <p class="item">{{ $item->qty }}</p>
                    </td>
                    <td>
                        <p class="item">{{ number_format($item->unit_price, 2) }}</p>
                    </td>
                    <td>
                        <p class="item">{{ number_format($item->sub_total, 2) }}</p>
                    </td>
                    @php($total += $item->sub_total)
                    @php($total_tax += $item->taxes)
                </tr>
            @endforeach

            <tr>
                <td colspan="5">
                    <p> <b> TOTAL TAXES</b></p>
                </td>
                <td>
                    <p><b> {{ number_format($total_tax, 2) }}</b></p>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <p> <b> ORDER TOTAL</b></p>
                </td>
                <td>
                    <p> <b> {{ number_format($total, 2) }}</b></p>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-xs-4">
            <b>Approved By</b><br><br><br>
            -----------------------------------------
        </div>

        <div class="col-xs-4">
            <b>Signature </b><br><br><br>
            ------------------------------------------
        </div>

        <div class="col-xs-4">
            <b>Date </b><br><br><br>
            -------------------------------------------
        </div>
    </div>

</body>

</html>
