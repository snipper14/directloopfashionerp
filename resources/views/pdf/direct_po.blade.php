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

    <div class="row mt-3">

        <div class="col-xs-3">
            <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" />
        </div>
        <div class="col-xs-4 left-cont">
            @if ($data[0]->branch->business_name)
                <h3 class="company-name">
                    {{ $data[0]->branch->business_name }}
                </h3>
            @endif

            @if ($data[0]->branch->branch)
                <p>Branch: {{ $data[0]->branch->branch }}</p>
            @endif

            @if ($data[0]->branch->postal_address)
                <p>Address: {{ $data[0]->branch->postal_address }}</p>
            @endif
            @if ($data[0]->branch->address)
                <p> {{ $data[0]->branch->address }}</p>
            @endif
            @if ($data[0]->branch->phone_no)
                <p>Tel: {{ $data[0]->branch->phone_no }}</p>
            @endif

        </div>


        <div class="col-xs-5 right-cont">
            <p>
            <h4>Local Purchase Order</h3>
                </p>
                <p> <b>Order No: </b>{{ $data[0]->order_no }}</p>
                <p> <b>Receipt Date: </b>{{ $data[0]->order_date }}</p>
        </div>
    </div>




    <table>
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Requested By: </b></td>
                <td><b>Department</b></td>
                <td><b>Print Date</b></td>

            </tr>
        </thead>
        <tr style="background:#eceff1;" class="title_2">
            <td>
                <p class="item"> {{ $data[0]->user->name }}</p>
            </td>
            <td>
                <p class="item"> {{ $data[0]->user->department->department }}</p>
            </td>
            <td>
                <p class="item">{{ date('m/d/Y h:i:s ', time()) }}</p>
            </td>
        </tr>
    </table>

    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Code</b></td>
                <td><b>Item Name</b></td>
                <td><b>Unit</b></td>

                <td><b>Qty</b></td>
                <td><b>Unit Price</b></td>
                <td><b>Tax</b></td>
                <td><b>Total</b></td>
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @php($total_tax = 0)
            @foreach ($data as $item)
                <tr class="td-height">
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
                        <p class="item"> {{ number_format($item->taxes, 2) }}</p>
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
                    <p> <b> {{ number_format($total_tax, 2) }}</b></p>
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
            {{ $data[0]->approver->name }}
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
