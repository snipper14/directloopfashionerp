<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title></title>
    <style>
        .table-bordered table {
            border-collapse: collapse;
        }

        .table-bordered th,
        .table-bordered td,
        .table-bordered tr {
            /* border-bottom: 1px solid rgb(14, 13, 13);
            border-right: 1px solid rgb(14, 13, 13); */

        }

        .table-bordered td {
            padding: 4px !important;
        }

        .table-bordered thead {
            border-top: 1px solid rgb(14, 13, 13);
        }

        table {
            width: 100% !important;
        }

        .table-address {
            width: 100% !important;
            margin-bottom: 0rem;
            margin-top: -14px;
        }

        .right-cont {}

        .left-cont p {
            line-height: 0.6em;
            font-weight: 600;
        }

        .right-cont span {
            float: right;
            margin-right: 1rem;

        }

        .right-cont span p {
            line-height: 0.6em;
            font-weight: 600;
        }

        .table-address p {
            font-size: 0.8em;
        }

        .invoice {
            text-align: center;
            color: rgb(83, 81, 81);
            font-weight: 700;
            font-size: 1.4rem;
        }

        .company-name {
            color: #9A631D !important;
            font-family: 'Times New Roman', Times, serif !important;
            font-weight: 900 !important;
            font-size: 2.4.0rem !important;

        }

        .company-add {
            color: rgb(211, 79, 79);
            font-style: italic;
            font-weight: 600;
            font-size: 1rem;
        }

        .top-title {
            margin-left: -34px !important;

        }
    </style>
</head>

<body>
    <table>

        <tr>
            <td class="top-title">
            
                    <img src="{{ Auth::user()->branch->img_path }}" style="margin-left:2rem" height="70" width="100" />

            </td>
            <td>
                <div><b class="company-name">
                        @if($data[0]['branch']->business_name)
                        <p class="company-name">
                            {{ $data[0]['branch']->business_name}}
                        </p>
                        @endif
                    </b>
                </div>
            </td>
        </tr>

    </table>
    <table class="table-address">
        <tr>
          
            <td class="right-cont">
                <span>
                    <p>
                        <h4>Account: </h4> {{ $data[0]['account']->account}}
                    </p><br>

                  

                </span>
            </td>
        </tr>
    </table>
    <table>
        <tr>

            <td>
                <p> <b>Statement Date: </b>{{ date('Y/m/d h:i:', time()) }}</p>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="invoice">
                <p>GENERAL LEDGER STATEMENT</p>
            </td>
        </tr>

    </table>

    <table class="table table-bordered">
        <thead>
            <tr><th scope="col">Date</th>
                <th scope="col">Ref.</th>
                <th scope="col">Type</th>
                <th scope="col">Details</th>
                
                <th scope="col"> Acc.</th>
                <th scope="col">Debit</th>
                <th scope="col">Credit</th>
              
                <th scope="col">balance</th>
            </tr>
        </thead>
        <tbody>
            @php($total = 0)

            @foreach ($data as $item)
            <tr>
                <td>{{ $item['entry_date']}}</td>
                <td>{{ $item['entry_code']}}</td>
                <td>{{ $item['account_type']}}</td>
                <td>{{ $item['details']}}</td>
                <td>{{ $item['other_account_name'] }}</td>
                <td>{{ number_format(($item['debit']),2)}}</td>
                <td>{{ number_format(($item['credit']),2)}}</td>

                <td>{{ number_format(($item['balance']),2)}}</td>

            </tr>
            @endforeach


        </tbody>
    </table>
    <table style="   position: fixed;
    bottom: 24px; border: 1px solid rgb(14, 13, 13);border-radius:4px;">
        <tr>
            <td>
                180 days
            </td>
            <td>
                150 days
            </td>
            <td>
                120 days
            </td>
            <td>
                90 days
            </td>
            <td>
                60 days
            </td>
            <td>
                30 days
            </td>
            <td>
                Current
            </td>

        </tr>
        <tr>
            <td>
                {{number_format(($one_80),2)}}
            </td>
            <td>

                {{number_format(($one_50),2)}}
            </td>
            <td>
                {{number_format(($one_20),2)}}
            </td>
            <td>
                {{number_format(($ninety),2)}}
            </td>
            <td>
                {{number_format(($sixty),2)}}

            </td>
            <td>
                {{number_format(($thirty),2)}}
            </td>
            <td>
                {{number_format(($current),2)}}
            </td>

        </tr>
    </table>
</body>

</html>