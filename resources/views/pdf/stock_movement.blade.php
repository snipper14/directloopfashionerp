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
.profile-title{
font-size: 1rem !important;
}
    </style>
</head>

<body>
    <div class="row">

        <div class="col-xs-3" style="">
            <img src="{{ Auth::user()->branch->img_path }}"  height="70" width="100" /></div>

        <div class="col-xs-8">
           
                @if($branch->business_name)
                <p class="company-name" >
                    {{ $branch->business_name}}
                </p>
                @endif
                @if($branch->branch)
                <b >
                 Branch:   {{ $branch->branch}}
                </b>
                @endif
                @if($branch->address)
                <b >
                 Address:   {{ $branch->address}}
                </b>
                @endif
                @if($branch->phone_no)
                <b >
                 Phone:   {{ $branch->phone_no}}
                </b>
                @endif



        </div>
    </div>



    <div style="background:#eceff1;">
       {{date('F d Y')}}
</div>
    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>MAIN STORE STOCK MOVEMENT  REPORT</h3>
        </div>
    </div>
    <hr>
<div class="row">
    <div class="col-xs-12 ">
        DATE  {{$report_date}} 
    </div>
</div>



    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Code</b></td>

                <td><b>Item Name</b></td>
                <td><b>Purchase P</b></td>

                <td><b>Opening Stock </b></td>
                <td><b>Added GRN </b></td>
                  <td><b>T.T Stock </b></td>
                <td><b>T.Issue Qty</b></td>


                <td><b>S.Balance</b></td>
                <td><b>System Stock</b></td>
          <td><b>Physical Stock</b></td>
          <td><b>Deficit</b></td>
            </tr>
        </thead>
        <tbody>

            @foreach ($data as $item)
            <tr class="td-height">
                <td>
                    <p >{{ $item['code']}}</p>
                </td>
                <td>
                    <p >{{ $item['product_name']}}</p>
                </td>


                <td>
                    <p >{{ number_format($item['purchase_price'],2)}}</p>
                </td>



                <td>
                    <p >{{ $item['opening_stock']}}</p>
                </td>
                <td>
                    <p >{{$item['sum_grn_qty']}}</p>
                </td>
                <td>
                    <p >{{$item['total_stock']}}</p>
                </td>

                <td>
                    <p >{{$item['sum_issued_qty']}}</p>
                </td>
                <td>
                    <p >{{  $item['total_balance']}}</p>
                </td>


             <td>
                <p >{{  $item['system_stock'] }}</p>

             </td>
             <td>
                <p >{{  $item['physical_qty'] }}</p>

             </td>
             <td>
                <p >
                  {{

                    $item['deficit'] }}

                </p>

             </td>


            </tr>

            @endforeach





        </tbody>
    </table>







       </table>
</body>

</html>
