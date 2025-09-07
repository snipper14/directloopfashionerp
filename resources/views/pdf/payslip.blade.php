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

        .table-bordered td {}

        .table-bordered thead {
            border-top: 1px solid rgb(14, 13, 13);
        }

        table {
            width: 50% !important;
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
            font-size: 2.0rem !important;
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





        .line-wrapper {
            display: inline-block;
            width: 100%;
            justify-content: space-between;
            font-size: 0.4rem !important;
        }

        .card-header {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between;
        }




        .top_row {
            display: table;
            width: 100%;
        }

        .top_row>div {
            display: table-cell;

            border-bottom: 1px solid #eee;
        }

        .top_row>.item-val {
            text-align: right !important;
        }

        .attribute {
            float: right;
        }

        .thin-line {
            border-bottom: 0.5px solid #414040;
            /* Black line */
            width: 50%;
            /* Full width */
            margin: 5px 0;
            /* Optional spacing */
        }

        .banking-details {
            font-size: 12px !important;
            padding: 0 !important;
        }

        .reduced-padding {

            margin: 0 !important;
            /* Remove any default margins */
            padding: 0 0 !important;
            /* Adjust top and bottom padding */

        }

        .mini-titles {
            font-size: 12px !important;
        }
    </style>
</head>

<body>
    <table>

        <tr>
            <td class="top-title">
                <center> <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" /></center>

            </td>
          
        </tr>
        <tr>  <td>
            <div><b >
                    {{ Auth::user()->branch->business_name }}
                </b>
            </div>

        </td></tr>

    </table>


    <table>

        <tr>


            <td>
                <b> Payslip for the month of
                    {{ $data['payroll_to'] }}
                </b>
            </td>

        </tr>
    </table>
    <table style="width: 50%">
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Name</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ $data['employee']['first_name'] . ' ' . $data['employee']['last_name'] }}
                </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Job Number</div>
            </td>
            <td>
                <div class="attribute banking-details">{{ $data['employee']['job_no'] }}</div>
            </td>
        </tr>

        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Job title</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ $data['employee']['employee_type']['name'] }}</div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Dept./Region:</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ $data['employee']['department']['department'] }}</div>
            </td>
        </tr>
    </table>
    <div class="thin-line"></div>


    <table style="width: 50%">
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Basic Pay:</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ number_format($data['employee']['salary'], 2) }}
                </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Gross Pay:</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ number_format($data['basic_salary'], 2) }}
                </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div>
                    <strong class="mini-titles">Deductions Before
                        Tax:</strong>
                </div>
            </td>
            <td>
                <div class="attribute"> </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Tier I Employee NSSF</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    -{{ number_format($data['nssf_tier1'], 2) }}
                </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Tier II Employee NSSF</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    -{{ number_format($data['nssf_tier2'], 2) }}
                </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">N.S.S.F:</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    -{{ number_format($data['nssf'], 2) }}
                </div>
            </td>
        </tr>

        <tr class="reduced-padding">
            <td>
                <div class="banking-details">S.H.I.F.:</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    -{{ number_format($data['nhif'], 2) }}
                </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Housing Levy</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    -{{ number_format($data['housing_levy'], 2) }}
                </div>
            </td>
        </tr>

        <tr class="reduced-padding">
            <td>
                <strong class="banking-details">Taxable Pay:</strong>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ number_format($data['basic_salary'] - $data['nssf']-$data['housing_levy']-$data['nhif'], 2) }}
                </div>
            </td>
        </tr>


   
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Tax Relief:</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ number_format($data['tax_relief'], 2) }}
                </div>
            </td>
        </tr>
        <tr class="reduced-padding">
            <td>
                <div><strong class="banking-details">P.A.Y.E:</strong></div>
            </td>
            <td>
                <div class="attribute banking-details">
                    -{{ number_format($data['net_paye'], 2) }}
                </div>
            </td>
        </tr>
    </table>
    <div class="thin-line"></div>
    <table style="width: 50%">
        <tr class="reduced-padding">
            <td>
                <div class="banking-details">Gross Pay after Tax:</div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ number_format($data['pay_after_tax']) }}
                </div>
            </td>
        </tr>


        <tr class="reduced-padding">
            <td>
                <div>
                    <strong class="banking-details">Deductions After
                        Tax:</strong>
                </div>
            </td>
            <td>
                <div class="attribute banking-details">
                </div>
            </td>
        </tr>




        @if ($data['total_loans'])
            <tr class="reduced-padding">
                <td>
                    <div class="banking-details">Loans:</div>
                </td>
                <td>
                    <div class="attribute banking-details">
                        {{ number_format($data['total_loans'], 2) }}
                    </div>
                </td>
            </tr>
        @endif
        @if ($data['total_advance'])
            <tr class="reduced-padding">
                <td>
                    <div class="banking-details">Advance:</div>
                </td>
                <td>
                    <div class="attribute banking-details">
                        {{ number_format($data['total_advance'], 2) }}
                    </div>
                </td>
            </tr>
        @endif
        @if ($data['total_deduction'])
            <tr class="reduced-padding">
                <td>
                    <div class="banking-details">Other Deductions:</div>
                </td>
                <td>
                    <div class="attribute banking-details">
                        {{ number_format($data['total_deduction'], 2) }}
                    </div>
                </td>
            </tr>
        @endif

    </table>
    <div class="thin-line"></div>
    <table style="width: 100%">
        <tr class="reduced-padding">
            <td>
                <div><strong class="banking-details">Net Pay:</strong></div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ number_format($data['net_salary'], 2) }}
                </div>
            </td>
        </tr>

    </table>
    <div class="thin-line"></div>
    <table style="width: 100%">
        @if ($data['employee']['pay_method'] == 'bank_transfer')
            <tr class="reduced-padding">
                <td>
                    <div>
                        <strong>Personal Info:</strong>
                    </div>
                </td>
                <td>
                    <div class="attribute">
                    </div>
                </td>
            </tr>
            <tr class="reduced-padding">
                <td>
                    <div>
                        <p class="banking-details">Payment Mode::</p>
                        <p class="banking-details">Bank Transfer</p>
                    </div>
                </td>
                <td>
                    <div class="attribute">
                        <div class="banking-details">Bank And Branch::</div>
                        <p class="banking-details">
                            {{ $data['employee']['bank_name'] }}
                            /
                            {{ $data['employee']['bank_branch'] }}
                        </p>
                    </div>
                </td>

            </tr>
        @endif
        <tr>
            <td>
                <div>
                    <div class="banking-details">Acc Name:: Account No</div>
                </div>
            </td>
            <td>
                <div class="attribute banking-details">
                    {{ $data['employee']['account_name'] }}
                    /
                    {{ $data['employee']['account_no'] }}
                </div>
            </td>
        </tr>
    </table>


    @if ($data['employee']['pay_method'] == 'mobile_money')
        <table style="width: 100%">
            <td>
                <div>
                    <strong>Personal Info:</strong>
                </div>
                <div></div>
            </td>
            <td>
                <div class="attribute">
                    <div>Payment Mode::</div>
                    <div>Mobile Money</div>
                </div>
            </td>
            <td>
                <div class="attribute">
                    <div>Phone Number::</div>
                    <div>
                        {{ $data['employee']['payment_phone'] }}
                    </div>
                </div>
            </td>
        </table>
    @endif
    <table>
        <tr>
            <td class="banking-details">  Salary Month
                <b>{{ $data['payroll_to'] }}</b></td>
        </tr>

    </table>
</body>

</html>
