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
            <h4> In-House Guest Report</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 ">

            Printed On {{ $curr_date }}<br>
            Printed By {{ $user->name }}<br>

        </div>
    </div>



    @foreach ($data as $item)
        <table>
            <tr>
                <td>
                    <p>PACKAGE: {{ $item['room_package'] }}</p>
                </td>
                <td>
                    <p>TOTAL: {{ number_format($item['sum_total']) }}</p>
                </td>
                <td>
                    <p>NO GUEST: {{ $item['sum_no_guest'] }}</p>
                </td>
            </tr>
        </table>




        <div>
            <table class="table ">
                <thead class="bg-dark">
                    <tr class="table-title">
                        <td><b>Guest</b></td>
                        <td><b>No. Guest</b></td>

                        <td><b>Room</b></td>
                        <td><b> Type</b></td>

                        <td><b> Pckge </b></td>
                        <td><b>Meal Rate</b></td>
                        <td><b>Price </b></td>
                        <td><b>Total </b></td>

                        <td><b>Occupation</b></td>

                    </tr>
                </thead>
                <tbody>
                    @php($total_meal = 0)
                    @php($total_guest = 0)
                    @php($total = 0)

                    @foreach ($item['data'] as $item)
                        <tr class="td-height">
                            <td>
                                <p>{{ $item['name'] }}</p>
                            </td>
                            <td>
                                {{ $item['no_guest'] }}
                            </td>
                            <td>
                                <p>{{ $item['room']->door_name }}</p>
                            </td>

                            <td>
                                <p>{{ $item['room']->room_standard->name }}</p>
                            </td>
                            <td>
                                <p>{{ $item['room_package']->details }}</p>
                            </td>

                            <td>
                                <p>{{ number_format($item['room_rate']->meal_rate, 0) }}</p>
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


                            @php($total_meal += $item['room_rate']->meal_rate)
                            @php($total_guest += $item['no_guest'])
                            @php($total += $item['total'])
                        </tr>

                    @endforeach
                    <tr>
                        <td>Totals</td>
                        <td>{{ $total_guest }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            {{ number_format($total_meal,0) }}
                        </td>
                        <td></td>
                        <td>{{number_format($total, 0)}}</td>
                    </tr>






                </tbody>
            </table>
        </div>

    @endforeach









    </table>
</body>

</html>
