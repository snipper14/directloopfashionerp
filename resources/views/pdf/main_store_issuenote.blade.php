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
    <div class="row" style="margin-top: -2rem">
        <div class="col-xs-12 ">
            <h3>MAIN STORE ISSUE NOTE</h3>
        </div>
    </div>
    <div class="row" style="margin-top: -2rem">
        <table>
            <tr>
                <td>
                  <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" />

    </div>

    </td>

    <td>
        <div class="left-cont">
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
    </td>
    <td>
        <div class="right-cont">
            <p>
            <h4> Issue Note</h4>
            </p>
            <p> <b>Issue No: </b>{{ $data[0]->issue_no }}</p>

            <p> <b>Issue Date: </b>{{ $data[0]->date_issued }}</p>
          
        </div>
    </td>
    </tr>
    </table>
    </div>




    <table>
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Created By: </b></td>
                <td><b>Department</b></td>



            </tr>
        </thead>
        <tr style="background:#eceff1; " class="title_2">
            <td>
                <p class="title_2"> {{ $data[0]->user->name }}</p>
            </td>
            <td>
                <p> <b class="title_2">{{ $data[0]->user->department->department }}</p>
            </td>

        </tr>
    </table>

    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Code</b></td>
                <td><b>Item Name</b></td>
                <td><b>Unit</b></td>
                <td><b>Qty Issued </b></td>
               
                <td><b>Purchase Price</b></td>


                <td><b>Total</b></td>

            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @foreach ($data as $item)
                <tr class="td-height">
                    <td>
                        <p>{{ $item->stock->code }}</p>
                    </td>
                    <td>
                        <p>{{ $item->stock->name }}</p>
                    </td>
                    <td>
                        <p>{{ $item->unit }}</p>
                    </td>
                    <td>
                        <p>{{ number_format($item->qty_issued, 2) }}</p>
                    </td>
                  
                    <td>
                        <p>{{ number_format($item->purchase_price, 2) }}</p>
                    </td>


                    <td>
                        <p>{{ number_format($item->row_total, 2) }}</p>
                    </td>


                </tr>
                @php($total += $item->row_total)
            @endforeach
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




    </table>
</body>

</html>
