<?php

namespace App\Http\Controllers\Payslips;

use App\Payroll\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Barryvdh\DomPDF\Facade as PDF;
class PaySlipsReportController extends BaseController
{
    public function fetchGrouped()
    {
        $data = DB::table('payrolls')

            ->join(
                'employees',
                'employees.id',
                '=',
                'payrolls.employee_id'
            )

            ->join(
                'departments',
                'departments.id',
                '=',
                'employees.department_id'
            )->whereNull('payrolls.deleted_at')

            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('payrolls.basic_salary', 'LIKE', '%' . request('search') . '%')

                        ->orWhere('employees.first_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('employees.last_name', 'LIKE', '%' . request('search') . '%');
                });
            })

            ->selectRaw('employees.first_name,employees.last_name,employee_id,departments.department,
        sum(basic_salary) as basic_salary,
        sum(housing_levy) as housing_levy,
        sum(total_allowance) as total_allowance,
        sum(total_rewards) as total_rewards,
        sum(net_paye) as net_paye,
        sum(housing_levy) as sum_housing_levy,
        sum(income_tax) as income_tax,sum(nssf) as nssf,sum(nhif) as nhif,sum(total_advance) as total_advance,
        sum(total_loans) as total_loans,sum(total_deduction) as total_deduction,sum(total_overtime) as total_overtime,
        sum(net_salary) as net_salary,payrolls.payment_type,payrolls.payroll_from,payrolls.payroll_to,
        sum(pay_after_tax) as pay_after_tax
        ')
            ->groupBy('payrolls.employee_id')

            ->paginate(10);

        return response()->json(['results' => $data]);
    }

    public function fetchAll()
    {
        $data = DB::table('payrolls')

            ->join(
                'employees',
                'employees.id',
                '=',
                'payrolls.employee_id'
            )

            ->join(
                'departments',
                'departments.id',
                '=',
                'employees.department_id'
            )

            ->selectRaw('employees.first_name,employees.last_name,employee_id,departments.department,
        sum(basic_salary) as basic_salary,
        sum(total_allowance) as total_allowance,
        sum(total_rewards) as total_rewards,
        sum(net_paye) as net_paye,
        sum(income_tax) as income_tax,sum(nssf) as nssf,sum(nhif) as nhif,sum(total_advance) as total_advance,
        sum(total_loans) as total_loans,sum(total_deduction) as total_deduction,sum(total_overtime) as total_overtime,
        sum(net_salary) as net_salary,payrolls.payment_type,payrolls.payroll_from,payrolls.payroll_to,
        sum(pay_after_tax) as pay_after_tax
        ')
            ->groupBy('payrolls.employee_id')
            
            ->get();

        return response()->json(['results' => $data]);
    }
    public function   fetchPayslipById(Request $request)
    {
        $id = $request->route('id');
        $data = Payroll::where('employee_id', $id)->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('basic_salary', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('employee', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['employee.department', 'employee.employee_type'])->orderBy('id', 'DESC')->paginate(10);

        return response()->json(['results' => $data]);
    }

    public function fetchAllById(Request $request)
    {
        $id = $request->route('id');
        $data = Payroll::where('employee_id', $id)->with(['employee.department', 'employee.employee_type'])->orderBy('id', 'DESC')->get();

        return response()->json(['results' => $data]);
    }

    public function fetchSummarised()
    {
        $data =   Payroll::selectRaw(' payroll_from,payroll_to,sum(net_salary) as net_salary,sum(nssf) as nssf,
 sum(nhif) as nhif,sum(income_tax) as income_tax,sum(net_paye) as net_paye, sum(total_advance) as total_advance,
 sum(total_loans) as total_loans,sum(total_deduction) as total_deduction,sum(housing_levy) AS housing_levy,sum(total_overtime) as total_overtime,
 sum(total_rewards) as total_rewards,sum(total_allowance) as total_allowance,
 employee_id
 ')
            ->groupBy('payroll_to')
            
            ->orderBy('id', 'DESC')
            ->paginate(20);
        return response()->json(['results' => $data]);
    }
    public function fetchAllSummarised()
    {
        $data =   Payroll::selectRaw(' payroll_from,payroll_to,sum(net_salary) as net_salary,sum(nssf) as nssf,
    sum(nhif) as nhif,sum(income_tax) as income_tax,sum(net_paye) as net_paye, sum(total_advance) as total_advance,
    sum(total_loans) as total_loans,sum(total_deduction) as total_deduction,sum(total_overtime) as total_overtime,sum(housing_levy) AS housing_levy,
    sum(total_rewards) as total_rewards,sum(total_allowance) as total_allowance,
    employee_id
    ')
            ->groupBy('payroll_to')
          
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json(['results' => $data]);
    }

    function getPayslipsByMonth(Request $request)
    {

        $to = $request->to;
        $from = $request->from;

        $data = Payroll::with(['employee.department', 'employee.employee_type'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('basic_salary', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('employee', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->where('payroll_to',  $to)

            ->orderBy('id', 'DESC')
           
            ->get();
        return response()->json(['results' => $data]);
    }
    public function fetchAllPayslipsByMonth(Request $request)
    {

        $to = $request->to;
        $from = $request->from;

        $data = Payroll::with(['employee.department', 'employee.employee_type'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('basic_salary', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('employee', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->where('payroll_to', $to)

            ->orderBy('id', 'DESC')
          
            ->get();
        return response()->json(['results' => $data]);
    }
    function downloadP9()
    {
        $main_query = Payroll::with(['employee'])->where(['employee_id' => request('employee_id')])
            ->whereYear('payrolls.payroll_to', request('tax_year'));
        $data =  $main_query->groupBy('payroll_to')->orderBy('id', 'ASC')->get();


        $path = public_path() . '/pdf/' . 'p9form' . '.pdf';
        $pdf = PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ])
            ->loadView('pdf.p9form', ['data' => $data, 'tax_year' => request('tax_year')]);

        $pdf->save($path);

        return response()->download($path);
    }
}
