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
        .profile-title {
            font-size: 1rem !important;
        }

    </style>
</head>

<body>
    <div class="row">

        <div class="col-xs-3" style="">
            <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" />
        </div>

        <div class="col-xs-8">

            @if ($data[0]['branch']->business_name)
                <p class="company-name">
                    {{ $data[0]['branch']->business_name }}
                </p>
            @endif
            @if ($data[0]['branch']->branch)
                <b>
                    Branch: {{ $data[0]['branch']->branch }}
                </b>
            @endif
            @if ($data[0]['branch']->address)
                <b>
                    Address: {{ $data[0]['branch']->address }}
                </b>
            @endif
            @if ($data[0]['branch']->phone_no)
                <b>
                    Phone: {{ $data[0]['branch']->phone_no }}
                </b>
            @endif



        </div>
    </div>



    <div style="background:#eceff1;">
        {{ date('F d Y') }}
    </div>
    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>POS STOCK MOVEMENT REPORT</h3>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-xs-12 ">
            DATE FROM {{ $from }} TO {{ $to }}
        </div>
    </div>



    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Code</b></td>

                <td><b>Item Name</b></td>
                <td><b>Selling P.</b></td>

                <td><b>Opening Stock </b></td>
                <td><b>T.Issued</b></td>
                <td><b>T.Balance </b></td>
                <td><b>System Stock</b></td>
                <td><b>Sold Items</b></td>
                <td><b>Physical Items</b></td>
                <td><b>Deficit</b></td>


            </tr>
        </thead>
        <tbody>

            @foreach ($data as $item)
                <tr class="td-height">
                    <td>
                        <p>{{ $item['stock']->code }}</p>
                    </td>
                    <td>
                        <p>{{ $item['stock']->product_name }}</p>
                    </td>


                    <td>
                        <p>{{ number_format($item['stock']->selling_price, 2) }}</p>
                    </td>



                    <td>
                        <p>{{ $item['opening_dept_stock'] }}</p>
                    </td>
                    <td>
                        <p>{{ $item['sum_dept_issued_qty'] }}</p>
                    </td>
                    <td>
                        <p>{{ $item['total_stock'] }}</p>
                    </td>

                    <td>
                        <p>{{ $item['dept_system_qty'] }}</p>
                    </td>
                  
                    <td>
                        <p>{{ $item['total_sold'] }}</p>
                    </td>
                    <td>
                        <p>{{ $item['physical_dept_qty'] }}</p>
                    </td>
                    <td>
                        <p>{{ $item['deficit'] }}</p>
                    </td>
                </tr>

            @endforeach





        </tbody>
    </table>







    </table>
</body>

</html>
