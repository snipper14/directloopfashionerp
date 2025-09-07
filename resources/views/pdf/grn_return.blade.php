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
        .header-section {
            font-size: 0.6rem !important;
        }
    </style>
</head>

<body>
    <div class="row">

        <div class="col-xs-3 " style="">
            <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" />
        </div>

        <div class="col-xs-7 header-section">

            @if ($branch->business_name)
                <p class="company-name">
                    {{ $branch->business_name }}
                </p>
            @endif



        </div>
    </div>
    <div class="row mt-3">


        <div class="col-xs-4 right-cont">
            <h3>Company</h3>
            <p> {{ $branch->business_name }}</p>
            @if ($branch->branch)
                <p>Branch: {{ $branch->branch }}</p>
            @endif

            @if ($branch->postal_address)
                <p>Address: {{ $branch->postal_address }}</p>
            @endif
            @if ($branch->address)
                <p> {{ $branch->address }}</p>
            @endif

        </div>

        <div class="col-xs-4">

        </div>
        <div class="col-xs-4 left-cont">
            <p>
            <h3> GRN Return Note</h3>
            </p>
            <p> <b>Return Code: </b>{{ $data[0]->return_code }}</p>
            <p> <b>Delivery No: </b>{{ $data[0]->delivery_no }}</p>

            <p> <b>Return Date: </b>{{ $data[0]->return_date }}</p>

        </div>
    </div>

    <div class="row bg-dark" style="margin-top:2rem;text:center;">
        <div class="col-xs-4">
            <h4><b class="title_2"> Vendor</b></h4>
        </div>
        <div class="col-xs-4"></div>

    </div>

    <div class="row" style="padding-top:2rem;">

        <div class="col-xs-4 left-cont header-section">

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


    </div>

    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>GRN Return Note</h3>
        </div>
    </div>

    <hr>
    <table style="margin-top: 2rem;margin-bottom:2rem;">
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Returned By: </b></td>
                <td><b>Department</b></td>
                <td><b>Received Date</b></td>


            </tr>
        </thead>
        <tr style="background:#eceff1;">
            <td>
                <p class="title_2"> {{ $data[0]->user->name }}</p>
            </td>
            <td>
                <p> <b class="title_2">{{ $data[0]->department->department }}</p>
            </td>
            <td>
                <p class="title_2"> {{ $data[0]->purchase_order_receivable->received_date }}</p>
            </td>

        </tr>
    </table>

    <table class="table ">
        <thead class="bg-dark">
            <tr>
                <td><b>Code</b></td>
                <td><b>Item Name</b></td>
                <td><b>Unit</b></td>
                <td><b>Return Condition</b></td>
                <td><b>Return Reason</b></td>
                <td><b>Qty </b></td>
                <td><b>Vat</b></td>
                <td><b>Sub Total</b></td>

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
                        <p class="item">{{ $item->returns_conditions }}</p>
                    </td>
                    <td>
                        <p class="item">{{ $item->return_reasons }}</p>
                    </td>

                    <td>
                        <p class="item">{{ $item->qty }}</p>
                    </td>

                    <td>
                        <p class="item">{{ number_format($item->tax_amount, 2) }}</p>
                    </td>
                    <td>
                        <p class="item">{{ number_format($item->sub_total, 2) }}</p>
                    </td>

                </tr>
                @php($total += $item->sub_total)
                @php($total_tax += $item->tax_amount)
            @endforeach

            <tr>
                <td colspan="6"></td>
                <td>
                    <b class="totals"> Tax Total</b>
                </td>
                <td>
                    <b class="totals"> {{ number_format(round($total_tax), 2) }}</b>
                </td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td>
                    <p><b class="totals"> Total Amount</b></p>
                </td>
                <td>



                    <b class="totals"> {{ number_format(round($total), 2) }}</b>
                </td>
            </tr>

        </tbody>
    </table>
    <div class="row">
        <div class="col-xs-3">
            <span>Received By</span><br><br><br>
            -----------------------------------------
        </div>

        <div class="col-xs-3">
            <span>Signature </span><br><br><br>
            ------------------------------------------
        </div>

        <div class="col-xs-3">
            <span>Date </span><br><br><br>
            -------------------------------------------
        </div>
    </div>

</body>

</html>
