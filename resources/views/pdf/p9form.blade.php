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
            color: #7CC954 !important;
            font-weight: 700;
            font-size: 1.4rem;
            margin-left: -14px !important;
        }

        .company-add {
            color: rgb(211, 79, 79);
            font-style: italic;
            font-weight: 600;
            font-size: 1rem;
        }

        .doc-type {
            margin-top: -16px;
            margin-bottom: -14px
        }

        .top-title {
            margin-left: -34px !important;

        }

        .header-text {
            font-size: 0.8rem;
        }

        .table-header b {
            font-size: 0.6rem !important;
        }

        .table-body td {
            font-size: 0.6rem !important;
        }

        .table-footer td {
            font-size: 0.6rem !important;
            font-weight: bold !important;
        }



        .table,
        td {
            border: 1px solid black !important;
            border-collapse: collapse;
        }

        .col-inner {
            float: left;
            border: 1px solid black !important;
            width: 33.33%;
        }

        .cols-inner {
            font-size: 0.6rem !important;
            font-weight: bold !important;
        }

        .bottom-text1 {
            font-size: 0.6rem !important;
        }

        .bottom-text2 {
            font-size: 0.8rem !important;
        }

    </style>
</head>

<body>
    <center>
        <b style="font-size: 1.2rem">KENYA REVENUE AUTHORITY</b><br>
        <b>
            INCOME TAX DEPARTMENT
        </b><br>
        <b>
            TOTAL DEDUCTION CARD YEAR {{ $tax_year }}
        </b>
    </center>
    <center>
        <div>

            <h3><b>

                </b></h3>
        </div>
    </center><br>
    <table>
        <tr>
            <td>
                <b>P9</b><br>
                <b class="header-text"> Employer's Name:</b><span class="header-text"> {{ Auth::user()->branch->business_name }} </span>
                <br>
                <b class="header-text"> Employee's Name:</b><span class="header-text">
                    {{ $data[0]->employee->first_name . ' ' . $data[0]->employee->last_name }}</span>
            </td>
            <td>
                <b class="header-text"> Employer's PIN:</b> <span class="header-text">  {{ Auth::user()->branch->kra_pin }}</span> <br>
                <b class="header-text"> Employee's PIN:</b><span class="header-text">
                    {{ $data[0]->employee->kra_pin }}</span>

            </td>
        </tr>
        <table class="table table-bordered">
            <thead>
                <tr class="table-header" border="1">
                    <td><b>Month</b></td>
                    <td><b>Basic Salary (Ksh)</b></td>
                    <td><b>Benefits Non Cash (Ksh)</b></td>

                    <td><b>Value of Quarters (Ksh)</b></td>
                    <td><b>TOTAL Gross Pay (Ksh)</b></td>
                    <td colspan="3"><b>Defined Contribution Retirement scheme Contribution & Owner Occupied interest</b>
                    </td>
                    <td><b>Owner occupied interest</b></td>
                    <td><b>Retirement Contribution $ owner occupied interest</b></td>
                    <td><b>chargeable pay</b></td>
                    <td><b>Tax Charged</b></td>
                    <td><b>Personal relief + Insurance Relief</b></td>
                    <td><b>PAYE Tax (J-K)</b></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="2"></td>
                    <td rowspan="2">A</td>
                    <td rowspan="2"></td>

                    <td rowspan="2"></td>
                    <td rowspan="2"></td>

                    <td colspan="3">E</td>


                    <td rowspan="2">F</td>
                    <td rowspan="2">G</td>
                    <td rowspan="2">H</td>
                    <td rowspan="2">J</td>
                    <td rowspan="2">K</td>
                    <td rowspan="2">L</td>


                </tr>
                <tr>
                    <td class="cols-inner">

                        E1 30% of A
                    </td>
                    <td class="cols-inner"> E2 Actual</td>

                    <td class="cols-inner"> E3 Fixed</td>
                </tr>
                @php($total_basic_salary = 0)
                @php($total_e1 = 0)
                @php($total_nssf = 0)
                @php($total_chargeable_pay = 0)
                @php($total_relief = 0)
                @php($total_paye = 0)
                @php($total_jk = 0)

                @foreach ($data as $item)
                    @php($e1 = $item->basic_salary * 0.3)
                    @php($total_basic_salary += $item->basic_salary)
                    @php($total_e1 += $e1)
                    @php($total_nssf += $item->nssf)
                    @php($chargeable_pay = $item->basic_salary - $item->nssf)
                    @php($total_chargeable_pay += $chargeable_pay)
                    @php($total_relief += $item->tax_relief)
                    @php($total_paye += $item->income_tax)
                    @php($jk = $item->income_tax - $item->tax_relief > 0 ? $item->income_tax - $item->tax_relief : 0)
                    @php($total_jk += $jk)
                    <tr class="table-body">
                        <td>{{ date('M', strtotime($item->payroll_to)) }}</td>

                        <td>{{ number_format($item->basic_salary,2) }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ number_format($item->basic_salary,2) }}</td>
                        <td>{{ number_format($e1, 1) }}</td>
                        <td>{{ $item->nssf }}</td>
                        <td></td>

                        <td></td>
                        <td></td>

                        <td>{{ number_format($chargeable_pay, 1) }}</td>
                        <td>{{ number_format($item->income_tax, 1) }}</td>
                        <td>{{ number_format($item->tax_relief, 1) }}</td>
                        <td>{{ number_format($jk, 1) }}</td>
                    </tr>
                @endforeach
                <tr class="table-footer">
                    <td><b>TOTAL</b></td>

                    <td>{{ number_format($total_basic_salary, 2) }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($total_basic_salary, 2) }}</td>
                    <td>{{ number_format($total_e1, 2) }}</td>
                    <td>{{ number_format($total_nssf, 2) }}</td>
                    <td></td>

                    <td></td>
                    <td></td>
                    <td>{{ number_format($total_chargeable_pay, 2) }}</td>
                    <td>{{ number_format($total_paye, 2) }}</td>
                    <td>{{ number_format($total_relief, 2) }}</td>

                    <td>{{ number_format($total_jk, 2) }}</td>
                </tr>
            </tbody>
        </table><br><br>
        <div>
            <div class="bottom-text1">
                To be completed by Employer at the end of the year
            </div><br>
            <div class="bottom-text2">
                <b>TOTAL CHARGEABLE PAY(COL.H)</b> KSh__________________ <b>TOTAL TAX(COL.H)</b> KSh__________________
            </div><br>
            <div class="bottom-text2">
                <span>Information required from employer at the end of the year</span><br>
                1. Date employee commenced if during the year...................................... 4.L.R NO of owner
                occupied the property.....................<br>
                2. Date employee left if during the year...................................... 5. Name of financial
                institution advancing loan................. <br>
                3.Wherehousing provide, state monthly charged...................................... 6. Date of
                occupation of the house....................... <br>
            </div>
        </div>

</body>

</html>
