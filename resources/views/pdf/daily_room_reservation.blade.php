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
            <img src="{{ Auth::user()->branch->img_path }}"  height="70" width="100" />
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




    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>DAILY ACCOMMODATION REPORT</h3>
        </div>
    </div>
  
    <div class="row">
        <div class="col-xs-12 ">
            DATE {{ $curr_date }}<br>
           <b> Printed By {{ $user->name }}</b><br>
        </div>
    </div>



    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Guest</b></td>
                <td><b>No. Guest</b></td>

                <td><b>R.M</b></td>
                <td><b> Type</b></td>

                <td><b> Package </b></td>
                <td><b>From</b></td>
                <td><b> Day/s </b></td>
                <td><b>Amount </b></td>
                <td><b>Total </b></td>


                <td><b>Occupation</b></td>

            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @php($total_guest = 0)
            @php($total_rooms = 0)
            @foreach ($data as $item)
                <tr class="td-height">
                    <td>
                        <p>{{ $item['name'] }}</p>
                    </td>
                    <td>
                        <p>{{ $item['no_guest'] }}</p>
                    </td>
                    <td>
                        <p>{{ $item['room']->door_name }}</p>
                    </td>
                    <td>
                        <p>{{ $item['room']->room_standard->name }}</p>
                    </td>
                    <td>
                        <p>{{ $item['room_package']->name }}</p>
                    </td>
                    <td>
                        <p>{{ $item['guest_company']->name }}</p>
                    </td>
                  
                    <td>
                        <p>{{ $item['qty'] }}</p>
                    </td>

                    <td>
                        <p>{{ number_format($item['price'], 0) }}</p>
                    </td>
                    <td>
                        <p>{{ number_format($item['total'], 0) }}</p>
                    </td>


                    <td>
                        <p>{{ $item['clear_status'] }}</p>
                    </td>


                    @php($total += $item['price'])
                    @php($total_guest += $item['no_guest'])
                    @php($total_rooms+=1)
                </tr>

            @endforeach

            <tr>
                <td>
                    <p> <b> NO GUEST </b></p>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td>

                </td>
                <td>
                    <p> <b> {{ $total_guest }}</b></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p> <b> NO ROOMS </b></p>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td>

                </td>
                <td>
                    <p> <b> {{ $total_rooms }}</b></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p> <b>DAILY TOTAL </b></p>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td>

                </td>
                <td>
                    <p> <b> {{ number_format($total, 0) }}</b></p>
                </td>
            </tr>



        </tbody>
    </table>







    </table>
</body>

</html>
