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




    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h4> ACCOMMODATION PAYMENT REPORT</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 ">
            @if ($guest_company_id)
                <b> Company: {{ $data[0]->guest_company->name }}</b><br>
            @endif
            Printed On {{ $curr_date }}<br>
            Printed By {{ $user->name }}<br>
            <b>From: </b>{{ date_format(date_create($from), 'Y/m/d H:i:s') }} <b>To:
            </b>{{ date_format(date_create($to), 'Y/m/d H:i:s') }}<br>

        </div>
    </div>



    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Guest</b></td>
             
                <td><b>Room</b></td>
               
               
                <td><b>Amount Paid</b></td>
               
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
                        <p>{{ $item['room']->door_name }}</p>
                    </td>
                
                  
                   
                    <td>
                        <p>{{ number_format($item['amount_paid'], 0) }}</p>
                    </td>
                  
                 


                    @php($total += $item['total'])
                    @php($total_guest += $item['no_guest'])
                    @php($total_rooms+=1)
                </tr>

            @endforeach

            <tr>
               
                <td colspan="2">
                    <p> <b> NO GUEST </b></p>
                </td>
               
                <td colspan="2">
                    <p> <b> {{ $total_guest }}</b></p>
                </td>
            </tr>
            <tr>
               
                <td colspan="2">
                    <p> <b> NO ROOMS </b></p>
                </td>
               
                <td colspan="2">
                    <p> <b> {{ $total_rooms }}</b></p>
                </td>
            </tr>
            <tr>
                
                <td colspan="2">
                    <p> <b> TOTAL </b></p>
                </td>
             
                <td colspan="2">
                    <p> <b> {{ number_format($total, 2) }}</b></p>
                </td>
            </tr>



        </tbody>
    </table>







    </table>
</body>

</html>
