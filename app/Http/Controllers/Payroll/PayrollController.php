<?php

namespace App\Http\Controllers\Payroll;

use App\Loan\Loan;
use Carbon\Carbon;
use App\Branch\Branch;
use App\Advance\Advance;
use App\Payroll\Payroll;
use App\TimeLog\TimeLog;
use App\Employee\Employee;
use App\Allowance\Allowance;
use App\Deduction\Deduction;
use App\Statutory\Statutory;
use Illuminate\Http\Request;
use App\Casuals\CasualTimelog;
use App\Commission\Commission;
use App\Expenses\Expenses;
use App\Expenses\ExpenseType;
use App\Ledgers\GeneralLedger;
use App\Payroll\PayrollProduction;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\salary_deductions\LoanPayment;
use App\Http\Controllers\BaseController;
use App\salary_deductions\DeductionPayment;

class PayrollController extends BaseController
{
    public function createCasualsPayslips(Request $request)
    {
        $request->validate([
            'payroll_month' => 'required|date',

        ]);
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            $employees_array = $request->employee_id;
            $totalSalary = 0;
            $totalNSSF = 0;
            $totalNHIF = 0;
            $totalPAYE = 0;
            $totalHousingLevy = 0;
            $payroll_code = $this->payrollCode();
            foreach ($employees_array as $employee_id) {
                $empp_data = $employee_id;


                $id = $empp_data['id'];
                $deduct_nssf = $empp_data['deduct_nssf'];
                $deduct_nhif = $empp_data['deduct_nhif'];
                $deduct_paye = $empp_data['deduct_paye'];
                $deduct_housinglevy = $empp_data['deduct_housinglevy'];
                $time_logs = CasualTimelog::where(['employee_id' => $id, 'isPaid' => '0'])->get();

                if (!$time_logs->isEmpty()) {
                    $gross_salary = 0;
                    $no_hrs = 0;
                  
                    foreach ($time_logs as  $value) {
                        $gross_salary += $value['amount'];
                        $no_hrs += $value['no_hrs'];
                    }
                    $total_allowance = $this->getAllowances($id);

                    $total_overtime = $this->getOverTime($id, $payroll_code);

                    $total_rewards = $this->getRewards($id, $payroll_code);
                    $gross_salary = $gross_salary + $total_allowance + $total_rewards + $total_overtime;



                    $nssf = $deduct_nssf == 1 ? $this->getNSSF($gross_salary) : 0;
                    $nssf_tier1 = $deduct_nssf == 1 ? $this->getNSSFTiers($gross_salary)['tier1'] : 0;
                    $nssf_tier2 = $deduct_nssf == 1 ? $this->getNSSFTiers($gross_salary)['tier2'] : 0;
                  
                    $nhif = $deduct_nhif == 1 ? $this->getNHIF($gross_salary) : 0;
                    $housing_levy = $deduct_housinglevy == 1 ? $this->getHousingLevy($gross_salary) : 0;
                    $taxable_income = $gross_salary - $nssf-$nhif-$housing_levy;
                    $income_tax = $deduct_paye == 1 ? $this->getPaye($taxable_income) : 0;


                    
                   $tax_relief=2400;
                   
                   $net_paye=0;
                    if($income_tax>$tax_relief){
                        $net_paye=$deduct_paye == 1 ? ($income_tax-$tax_relief):0;
                       }

                    $pay_after_tax = $taxable_income - $net_paye;

                    $advance = $this->getAdvance($id, $payroll_code);
                    $net_pay = $pay_after_tax - $advance;

                    $total_loans = $this->getLoans($id, $request->payroll_month);
                    $net_pay = $net_pay - $total_loans;

                    $total_deduction = $this->getDeductions($id, $request->payroll_month);
                    $net_pay = $net_pay - $total_deduction;

                    DB::enableQueryLog();
                    $payroll_res = Payroll::create([
                        'employee_id' => $id,
                        'payroll_from' => $request->payroll_month,
                        'payment_type' => $request->payment_type,
                        'payroll_to' => $request->payroll_month,
                        'basic_salary' => $gross_salary,
                        'total_allowance' => $total_allowance,
                        'total_rewards' => $total_rewards,
                        'nssf' => $nssf,
                        'nssf_tier1'=>$nssf_tier1,
                        'nssf_tier2'=>$nssf_tier2,
                        'nhif' => $nhif,
                        'housing_levy' => $housing_levy,
                        'income_tax' => $income_tax,
                        'tax_relief' => $tax_relief,
                       
                        'net_paye' => $net_paye,
                        'pay_after_tax' => $pay_after_tax,
                        'total_advance' => $advance,
                        'total_loans' => $total_loans,
                        'total_deduction' => $total_deduction,
                        'total_overtime' => $total_overtime,
                        'net_salary' => $net_pay,
                        'no_hrs' => $no_hrs,
                        'payroll_code' => $payroll_code,
                       
                    ] + Parent::user_branch_id());

                    CasualTimelog::where(['employee_id' => $id, 'isPaid' => '0'])->update(['isPaid' => '1', 'payroll_code' => $payroll_code]);
                    LoanPayment::where(['payroll_id' => null, 'employee_id' => $id])->update(['payroll_id' => $payroll_res->id]);
                    DeductionPayment::where(['payroll_id' => null, 'employee_id' => $id])->update(['payroll_id' => $payroll_res->id]);
                    $totalSalary += $net_pay;
                    $totalNSSF += $nssf;
                    $totalNHIF += $nhif;
                    $totalPAYE += $net_paye;
                    $totalHousingLevy += $housing_levy;
                }
            }
            $this->addAccounts($totalSalary, $totalNSSF, $totalNHIF, $totalPAYE, $totalHousingLevy, $payroll_code, $request->payroll_month);
        }, 5);
        return $request->all();
    }



    public function create(Request $request)
    {
        $request->validate([
            'payroll_month' => 'required|date',

        ]);

        ini_set('max_execution_time', 2400);

        DB::transaction(function () use ($request) {
            $employees_array = $request->employee_id;
            $totalSalary = 0;
            $totalNSSF = 0;
            $totalNHIF = 0;
            $totalPAYE = 0;
            $totalHousingLevy = 0;
            $payroll_code = $this->payrollCode();
            foreach ($employees_array as $employee_id) {


                $id = $employee_id['id'];
                $deduct_nssf = $employee_id['deduct_nssf'];
                $deduct_nhif = $employee_id['deduct_nhif'];
                $deduct_paye = $employee_id['deduct_paye'];
                //validate if month has already generate payslips
                $deduct_housinglevy = $employee_id['deduct_housinglevy'];
                $date = Carbon::createFromFormat('Y-m-d', $request->payroll_month);
                $empsalaries = Payroll::where('employee_id', $id)->whereMonth('payroll_to', $date->month)
                    ->whereYear('payroll_to', $date->year)
                    ->get();

                if (!count($empsalaries)) {
                    $employee_info = Employee::where('id', $id)->first();
                   
                    $gross_salary = $employee_info['salary'];

                    $total_allowance = $this->getAllowances($id);

                    $total_overtime = $this->getOverTime($id, $payroll_code);

                    $total_rewards = $this->getRewards($id, $payroll_code);
                    $gross_salary = $gross_salary + $total_allowance + $total_rewards + $total_overtime;

                    // $housing_levy = $this->getHousingLevy($gross_salary);


                    $nssf = $deduct_nssf == 1 ? $this->getNSSF($gross_salary) : 0;
                    $nssf_tier1 = $deduct_nssf == 1 ? $this->getNSSFTiers($gross_salary)['tier1'] : 0;
                    $nssf_tier2 = $deduct_nssf == 1 ? $this->getNSSFTiers($gross_salary)['tier2'] : 0;
                  
                    $nhif = $deduct_nhif == 1 ? $this->getNHIF($gross_salary) : 0;
                    $housing_levy = $deduct_housinglevy == 1 ? $this->getHousingLevy($gross_salary) : 0;
                    $taxable_income = $gross_salary - $nssf-$nhif-$housing_levy;
                    $income_tax = $deduct_paye == 1 ? $this->getPaye($taxable_income) : 0;


                    
                   $tax_relief=2400;
                   $net_paye=0;
                  
                   if($income_tax>$tax_relief){
                    $net_paye=$deduct_paye == 1 ? ($income_tax-$tax_relief):0;
                   }
                    $pay_after_tax = $taxable_income - $net_paye;
                  



             
                

                   


                  
                   
                    $advance = $this->getAdvance($id, $payroll_code);
                    $net_pay = $pay_after_tax - $advance;

                    $total_loans = $this->getLoans($id, $request->payroll_month);
                    $net_pay = $net_pay - $total_loans;

                    $total_deduction = $this->getDeductions($id, $request->payroll_month);
                    $net_pay = $net_pay - $total_deduction;

                    $payroll_res =   Payroll::create([
                        'employee_id' => $id,
                        'payroll_from' => $request->payroll_month,
                        'payment_type' => $request->payment_type,
                        'payroll_to' => $request->payroll_month,
                        'basic_salary' => $gross_salary,
                        'total_allowance' => $total_allowance,
                        'total_rewards' => $total_rewards,
                        'nssf' => $nssf,
                        'nssf_tier1'=>$nssf_tier1,
                        'nssf_tier2'=>$nssf_tier2,
                        'nhif' => $nhif,
                        
                        'housing_levy' => $housing_levy,
                        'income_tax' => $income_tax,
                        'tax_relief' => $tax_relief,
                        'insurance_relief' => 0,
                        'net_paye' => $net_paye,
                        'pay_after_tax' => $pay_after_tax,
                        'total_advance' => $advance,
                        'total_loans' => $total_loans,
                        'total_deduction' => $total_deduction,
                        'total_overtime' => $total_overtime,
                        'net_salary' => $net_pay,
                        'payroll_code' => $payroll_code,
                        "housing_levy_relief"=>0
                    ] + Parent::user_branch_id());
                    LoanPayment::where(['payroll_id' => null, 'employee_id' => $id])->update(['payroll_id' => $payroll_res->id]);
                    DeductionPayment::where(['payroll_id' => null, 'employee_id' => $id])->update(['payroll_id' => $payroll_res->id]);
                    //totals
                    $totalSalary += $net_pay;
                    $totalNSSF += $nssf;
                    $totalNHIF += $nhif;
                    $totalPAYE += $net_paye;
                    $totalHousingLevy += $housing_levy;
                }
            }
            $this->addAccounts($totalSalary, $totalNSSF, $totalNHIF, $totalPAYE, $totalHousingLevy, $payroll_code, $request->payroll_month);
        }, 5);
        return $request->all();
    }
    function addAccounts($totalSalary, $totalNSSF, $totalNHIF, $totalPAYE, $totalHousingLevy, $payroll_code, $payroll_month)
    {

        $accounts_data =  Branch::where(['id' => Parent::branch_id()])->whereNotNull('payroll_account_id')->first();
        if ($accounts_data) {
            if ($totalSalary != 0) {
                //net salaries
                $insert_data = array(
                    'other_account_name' => "Net Salary /" . $payroll_month,
                    'account_id' => $accounts_data->payroll_account_id,
                    'account_type' => 'Salaries',
                    'entry_code' => $payroll_code,
                    'ref' => $payroll_code,
                    'details' => "Net Salary /" . $payroll_month,
                    'credit_amount' => $totalSalary,
                    'description' => "Net Salary ",
                    'entry_date' => Parent::currentDate(),

                );

                GeneralLedger::create($insert_data + Parent::user_branch_id());
                $expenseType = ExpenseType::firstOrCreate([
                    'name' => "Salaries"
                ], []);
                Expenses::create([
                    'expense_type_id' => $expenseType->id,
                    'amount' => $totalSalary,
                    'date_recorded' => Parent::currentDate(),
                    'details' => "Net Salary /" . $payroll_month,
                    'ref_no' => $payroll_code,
                    'unpaid_balance' => $totalSalary,
                    'amount_paid'=>0
                ] + Parent::user_branch_id());
            }
            if ($totalNSSF != 0) {
                //net NSSF
                $insert_nssf = array(
                    'other_account_name' => "NSSF /" . $payroll_month,
                    'account_id' => $accounts_data->payroll_account_id,
                    'account_type' => 'NSSF',
                    'entry_code' => $payroll_code,
                    'ref' => $payroll_code,
                    'details' => "NSSF /" . $payroll_month,
                    'credit_amount' => $totalNSSF,
                    'description' => "NSSF ",
                    'entry_date' => Parent::currentDate(),

                );


                GeneralLedger::create($insert_nssf + Parent::user_branch_id());
                $expenseType = ExpenseType::firstOrCreate([
                    'name' => "NSSF"
                ], []);
                Expenses::create([
                    'expense_type_id' => $expenseType->id,
                    'amount' => $totalNSSF,
                    'amount_paid'=>0,
                    'date_recorded' => Parent::currentDate(),
                    'details' => "NSSF /" . $payroll_month,
                    'ref_no' => $payroll_code,
                    'unpaid_balance' => $totalNSSF,
                ] + Parent::user_branch_id());
            }
            //net nhif
            if ($totalNHIF != 0) {

                $insert_nhif = array(
                    'other_account_name' => "NHIF /" . $payroll_month,
                    'account_id' => $accounts_data->payroll_account_id,
                    'account_type' => 'NHIF',
                    'entry_code' => $payroll_code,
                    'ref' => $payroll_code,
                    'details' => "NHIF /" . $payroll_month,
                    'credit_amount' => $totalNHIF,
                    'description' => "NHIF ",
                    'entry_date' => Parent::currentDate(),

                );


                GeneralLedger::create($insert_nhif + Parent::user_branch_id());
                $expenseType = ExpenseType::firstOrCreate([
                    'name' => "NHIF"
                ], []);
                Expenses::create([
                    'expense_type_id' => $expenseType->id,
                    'amount' => $totalNHIF,
                    'date_recorded' => Parent::currentDate(),
                    'details' =>  "NHIF /" . $payroll_month,
                    'ref_no' => $payroll_code,
                    'unpaid_balance' => $totalNHIF,
                    'amount_paid'=>0
                ] + Parent::user_branch_id());
            }

            //PAYEE
            if ($totalPAYE != 0) {

                $insert_payee = array(
                    'other_account_name' => "PAYE /" . $payroll_month,
                    'account_id' => $accounts_data->payroll_account_id,
                    'account_type' => 'PAYE',
                    'entry_code' => $payroll_code,
                    'ref' => $payroll_code,
                    'details' => "PAYE /" . $payroll_month,
                    'credit_amount' => $totalPAYE,
                    'description' => "PAYE ",
                    'entry_date' => Parent::currentDate(),

                );


                GeneralLedger::create($insert_payee + Parent::user_branch_id());
                $expenseType = ExpenseType::firstOrCreate([
                    'name' => "PAYE"
                ], []);
                Expenses::create([
                    'expense_type_id' => $expenseType->id,
                    'amount' => $totalPAYE,
                    'date_recorded' => Parent::currentDate(),
                    'details' =>  "PAYE /" . $payroll_month,
                    'ref_no' => $payroll_code,
                    'unpaid_balance' => $totalPAYE,
                    'amount_paid'=>0
                ] + Parent::user_branch_id());
            }
            //HOUSING LEVY
            if ($totalHousingLevy != 0) {

                $insert_payee = array(
                    'other_account_name' => "HOUSING LEVY /" . $payroll_month,
                    'account_id' => $accounts_data->payroll_account_id,
                    'account_type' => 'HOUSING LEVY',
                    'entry_code' => $payroll_code,
                    'ref' => $payroll_code,
                    'details' => "HOUSING LEVY /" . $payroll_month,
                    'credit_amount' => $totalHousingLevy,
                    'description' => "HOUSING LEVY ",
                    'entry_date' => Parent::currentDate(),

                );


                GeneralLedger::create($insert_payee + Parent::user_branch_id());
                $expenseType = ExpenseType::firstOrCreate([
                    'name' => "HOUSING LEVY"
                ], []);
                Expenses::create([
                    'expense_type_id' => $expenseType->id,
                    'amount' => $totalHousingLevy,
                    'date_recorded' => Parent::currentDate(),
                    'details' =>  "HOUSING LEVY /" . $payroll_month,
                    'ref_no' => $payroll_code,
                    'unpaid_balance' => $totalHousingLevy,
                    'amount_paid'=>0
                ] + Parent::user_branch_id());
            }
                
        }
    }
  
    public function getNSSF($salary)
    {
        //calculate tier 1 NSSF
        $nhif_amount = 0;
        $tier_1 = 0;
        $tier_2 = 0;
        if ($salary <= 8000) {
            $tier_1 = ($salary) * 0.06;
            $nhif_amount = $tier_1;
        }
        if ($salary > 8000 && $salary <= 72000) {
            $tier_2 = ($salary - 8000) * 0.06;
            $nhif_amount = $tier_2 + 480;
        } else if ($salary > 72000) {
            $tier_2 = (72000 - 8000) * 0.06;
            $nhif_amount = $tier_2 + 480;
        }
        return $nhif_amount;
    }
    public function getNSSFTiers($salary)
    {

        $nhif_amount = 0;
        $tier_1 = 0;
        $tier_2 = 0;
        if ($salary <= 8000) {
            $tier_1 = ($salary) * 0.06;
            return array('tier1' => $tier_1, 'tier2' => 0);
        }
        if ($salary > 8000 && $salary <= 72000) {
            $tier_2 = ($salary - 8000) * 0.06;
            return array('tier1' => 480, 'tier2' => $tier_2);
        } else if ($salary > 72000) {
            $tier_2 = (72000 - 8000) * 0.06;
            return array('tier1' => 480, 'tier2' => $tier_2);
        }
    }
   
    function getInsuranceRelief($nhif)
    {
        $amount = 0.15 * $nhif;
        return $amount;
    }

    public function getHousingLevy($salary)
    {
        $statutory_info = Statutory::where('statutory_type', 'HOUSINGLEVY')->first();

        if ($statutory_info) {
            return $salary * (($statutory_info->percentage_deduction) / 100);
        } else {
            return 0;
        }
    }
    function getHousingLevyRelief($housing_levy){
       $housing_levy_relief= 0.15*$housing_levy;
       return $housing_levy_relief;
    }
    public function  getNHIF($salary)
    {
        $shif= 0.0275*$salary;
        return $shif;
    }
    public function getPaye($taxable_income)
    {

        $statutory_info = Statutory::where('statutory_type', 'PAYE')->orderBy('percentage_deduction', 'ASC')->get();

        if (!$statutory_info->isEmpty()) {

            $total_paye = 0;
            $upper_salary_limit = 0;

            foreach ($statutory_info as  $value) {

                $lower_salary = $value['salary_from'];
                $upper_salary = $value['salary_to'];
                $tax_rate = $value['percentage_deduction'] * 0.01;

                if ($taxable_income > $upper_salary) {

                    $taxable_group = $upper_salary - $lower_salary;

                    $upper_salary_limit = $upper_salary;
                    $tax_amount = $taxable_group * $tax_rate;
                    $total_paye += $tax_amount;
                } else {

                    $taxable_group1 = $taxable_income - $upper_salary_limit;

                    $tax_amount = $taxable_group1 * $tax_rate;
                    $total_paye += $tax_amount;

                    return $total_paye;
                }
            }
        } else {
            return 0;
        }
    }
    public function getRelief($insurance_relief)
    {
        return 2400;
    }
    
    public function getAdvance($id, $payroll_code)
    {
        $result = Advance::where(['employee_id' => $id, 'isDeducted' => '0'])->get();

        $total_advance = 0;
        if (count($result)) {

            foreach ($result as $value) {
                $total_advance += $value['amount'];
                Advance::where('id', $value['id'])->update(['isDeducted' => 1, 'payroll_code' => $payroll_code]);
            }
        }

        return $total_advance;
    }

    public function getAllowances($id)
    {
        $result = Allowance::where('employee_id', $id)->get();
        $total_allowance = 0;
        if (!$result->isEmpty()) {
            foreach ($result as $value) {
                $total_allowance += $value['amount'];
            }
        }
        return $total_allowance;
    }

    function getRewards($id, $payroll_code)
    {
        $result = Commission::where(['employee_id' => $id, 'isPaid' => 0])->get();
        $total_allowance = 0;
        if (!$result->isEmpty()) {
            foreach ($result as $value) {
                $total_allowance += $value['amount'];
                Commission::where(['id' => $value['id'], 'isPaid' => 0])->update(['isPaid' => 1, 'payroll_code' => $payroll_code]);
            }
        }

        return $total_allowance;
    }

    function getLoans($id, $payroll_date)
    {

        $result = Loan::where(['employee_id' => $id])->where('outstanding_balance', '>', 0)
            ->where('loan_date', '<=', $payroll_date)->get();
        $total_loan = 0;
        if (!$result->isEmpty()) {
            foreach ($result as $value) {
                if ($value['outstanding_balance'] > $value['installment_amount']) {
                    $total_loan += $value['installment_amount'];
                    Loan::where(['id' => $value['id']])->decrement('outstanding_balance', $value['installment_amount']);
                    LoanPayment::create([
                        'loan_id' => $value['id'],
                        'employee_id' => $id,
                        'amount_paid' => $value['installment_amount'],
                        'payroll_date' => $payroll_date
                    ] + Parent::user_branch_id());
                } else {
                    $total_loan += $value['outstanding_balance'];
                    Loan::where(['id' => $value['id']])->decrement('outstanding_balance', $value['outstanding_balance']);
                    LoanPayment::create([
                        'loan_id' => $value['id'],
                        'employee_id' => $id,
                        'amount_paid' => $value['outstanding_balance'],
                        'payroll_date' => $payroll_date
                    ] + Parent::user_branch_id());
                }
            }
        }

        return $total_loan;
    }

    function getOverTime($id, $payroll_code)
    {
        $result = TimeLog::where(['employee_id' => $id, 'isPaid' => 0])->get();
        $total_overtime = 0;
        if (!$result->isEmpty()) {
            foreach ($result as $value) {
                $total_overtime += $value['total_amount'];
                TimeLog::where(['id' => $value['id'], 'isPaid' => 0])->update(['isPaid' => 1, 'payroll_code' => $payroll_code]);
            }
        }

        return $total_overtime;
    }

    function getDeductions($id, $payroll_date)
    {

        $result = Deduction::where(['employee_id' => $id])->where('outstanding_balance', '>', 0)
            ->where('effective_date', '<=', $payroll_date)->get();
        $monthly_deduction = 0;
        if (!$result->isEmpty()) {
            foreach ($result as $value) {
                if ($value['outstanding_balance'] > $value['monthy_deduction']) {
                    $monthly_deduction += $value['monthy_deduction'];
                    Deduction::where(['id' => $value['id']])->decrement('outstanding_balance', $value['monthy_deduction']);
                    DeductionPayment::create([
                        'deduction_id' => $value['id'],
                        'employee_id' => $id,
                        'amount_paid' => $value['monthy_deduction'],
                        'payroll_date' => $payroll_date
                    ] + Parent::user_branch_id());
                } else {
                    $monthly_deduction += $value['outstanding_balance'];
                    Deduction::where(['id' => $value['id']])->decrement('outstanding_balance', $value['outstanding_balance']);

                    DeductionPayment::create([
                        'deduction_id' => $value['id'],
                        'employee_id' => $id,
                        'amount_paid' => $value['outstanding_balance'],
                        'payroll_date' => $payroll_date
                    ] + Parent::user_branch_id());
                }
            }
        }

        return $monthly_deduction;
    }
    public function createPieceWorkPayslips(Request $request)
    {
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            $employees_array = $request->employee_id;
            $totalSalary = 0;
            $totalNSSF = 0;
            $totalNHIF = 0;
            $totalPAYE = 0;
            $totalHousingLevy = 0;
            $payroll_code = $this->payrollCode();
            foreach ($employees_array as $employee_id) {
                $id = $employee_id['id'];
                $deduct_nssf = $employee_id['deduct_nssf'];
                $deduct_nhif = $employee_id['deduct_nhif'];
                $deduct_paye = $employee_id['deduct_paye'];
                $deduct_housinglevy = $employee_id['deduct_housinglevy'];
                $from = date($request->start_payroll_date);
                $to = date($request->end_payroll_date);

                $results =  PayrollProduction::where(['employee_id' => $id])->whereBetween('produced_on', [$from, $to])->get();
                //get total pay
                $basic_income = 0;
                foreach ($results as  $value) {

                    $basic_income += $value['total_pay'];
                }
                if ($basic_income > 0) {
                   
                    $gross_salary = $basic_income;

                    $total_allowance = $this->getAllowances($id);

                    $total_overtime = $this->getOverTime($id, $payroll_code);

                    $total_rewards = $this->getRewards($id, $payroll_code);
                    $gross_salary = $gross_salary + $total_allowance + $total_rewards + $total_overtime;



                  
                    $nssf = $deduct_nssf == 1 ? $this->getNSSF($gross_salary) : 0;
                    $nssf_tier1 = $deduct_nssf == 1 ? $this->getNSSFTiers($gross_salary)['tier1'] : 0;
                    $nssf_tier2 = $deduct_nssf == 1 ? $this->getNSSFTiers($gross_salary)['tier2'] : 0;
                  
                    $nhif = $deduct_nhif == 1 ? $this->getNHIF($gross_salary) : 0;
                    $housing_levy = $deduct_housinglevy == 1 ? $this->getHousingLevy($gross_salary) : 0;
                    $taxable_income = $gross_salary - $nssf-$nhif-$housing_levy;
                    $income_tax = $deduct_paye == 1 ? $this->getPaye($taxable_income) : 0;


                    
                   $tax_relief=2400;
                   if($income_tax>$tax_relief){
                    $net_paye=$deduct_paye == 1 ? ($income_tax-$tax_relief):0;
                   }
                    $pay_after_tax = $taxable_income - $net_paye;

                    $advance = $this->getAdvance($id, $payroll_code);
                    $net_pay = $pay_after_tax - $advance;

                    $total_loans = $this->getLoans($id, $request->end_payroll_date);
                    $net_pay = $net_pay - $total_loans;

                    $total_deduction = $this->getDeductions($id, $request->end_payroll_date);
                    $net_pay = $net_pay - $total_deduction;

                    $payroll_res =     Payroll::create([
                        'employee_id' => $id,
                        'payroll_from' => $request->start_payroll_date,
                        'payment_type' => $request->payment_type,
                        'payroll_to' => $request->end_payroll_date,
                        'basic_salary' => $gross_salary,
                        'total_allowance' => $total_allowance,
                        'total_rewards' => $total_rewards,
                        'nssf' => $nssf,
                        'nssf_tier1'=>$nssf_tier1,
                        'nssf_tier2'=>$nssf_tier2,
                        'housing_levy' => $housing_levy,
                        'nhif' => $nhif,
                        'income_tax' => $income_tax,
                        'tax_relief' => $tax_relief,
                       
                        'net_paye' => $net_paye,
                        'pay_after_tax' => $pay_after_tax,
                        'total_advance' => $advance,
                        'total_loans' => $total_loans,
                        'total_deduction' => $total_deduction,
                        'total_overtime' => $total_overtime,
                        'payroll_code' => $payroll_code,
                        'net_salary' => $net_pay,
                 

                    ] + Parent::user_branch_id());
                    LoanPayment::where(['payroll_id' => null, 'employee_id' => $id])->update(['payroll_id' => $payroll_res->id]);
                    DeductionPayment::where(['payroll_id' => null, 'employee_id' => $id])->update(['payroll_id' => $payroll_res->id]);
                      //totals
                      $totalSalary += $net_pay;
                      $totalNSSF += $nssf;
                      $totalNHIF += $nhif;
                      $totalPAYE += $net_paye;
                      $totalHousingLevy += $housing_levy;
                }
            }
            $this->addAccounts($totalSalary, $totalNSSF, $totalNHIF, $totalPAYE, $totalHousingLevy, $payroll_code, $request->payroll_month);
        }, 5);
        return true;
    }

   
    public function getEmployeesByPayMethod(Request $request)
    {
        // DB::enableQueryLog();
        $pay_type = $request->type;
        $data = Employee::where('payment_type', $pay_type)->get();
        // print_r(DB::getQueryLog());
        return response()->json(['results' => $data]);
    }

    public function getMonthlyEmplByDept(Request $request)
    {
        $dept_id = $request->type;

        if ($dept_id == "0") {
            $data = Employee::where('payment_type', 'monthly')->get();

            return response()->json(['results' => $data]);
        }
        $data = Employee::where(['department_id' => $dept_id, 'payment_type' => 'monthly'])->get();
        // print_r(DB::getQueryLog());
        return response()->json(['results' => $data]);
    }



    public function fetch()
    {
        $raw_query = Payroll::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('basic_salary', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('employee', function ($query) {
                        $query->whereRaw("concat(first_name, ' ', last_name) like '%" . request('search') . "%'");
                    });
            });
        })->with(['employee.department', 'employee.employee_type'])->orderBy('id', 'DESC');
        $data = $raw_query->get();
        if (request('page') > 0) {
            $data = $raw_query->paginate(10);
        }
        return response()->json(['results' => $data]);
    }

    public function fetchAll()
    {
        $data = Payroll::with(['employee'])->orderBy('id', 'DESC')->get();

        return response()->json(['results' => $data]);
    }


    public function downLoadPayslip(Request $request)
    {
        $payroll_data = $request->payroll_data;
        $employee_name = $payroll_data['employee']['first_name'] . ' ' . $payroll_data['employee']['last_name'];
        $data = $payroll_data;
        $path = public_path() . '/pdf/' . $employee_name . '.pdf';

        $pdf = PDF::loadView('pdf.payslip', compact('data'));

        $pdf->save($path);

        return response()->download($path);
    }
    function destroy(Request  $request)
    {
        $id = $request->route('id');
        $res = Payroll::find($id);
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($res) {
            $this->recoverLoans($res);
            $this->recoverDeductions($res);
            $this->recoverAdvance($res);
            $this->recoverRewards($res);
            $this->recoverOvertime($res);
            if ($res['payment_type'] == "casual") {

                CasualTimelog::where(['employee_id' => $res['employee_id'], 'payroll_code' => $res['payroll_code']])->update(['isPaid' => 0, 'payroll_code' => $res['payroll_code']]);
            }
            Payroll::find($res->id)->delete();
        }, 5);

        return true;
    }

    function recoverLoans($data)
    {

        $res = LoanPayment::where('payroll_id', $data->id)->get();
        if (!$res->isEmpty()) {
            foreach ($res as  $value) {

                Loan::where(['id' => $value['loan_id']])->increment('outstanding_balance', $value['amount_paid']);
            }
            LoanPayment::where('payroll_id', $data->id)->delete();
        }
    }
    function recoverDeductions($data)
    {

        $res = DeductionPayment::where('payroll_id', $data->id)->get();
        if (!$res->isEmpty()) {
            foreach ($res as  $value) {

                Deduction::where(['id' => $value['deduction_id']])->increment('outstanding_balance', $value['amount_paid']);
            }
            DeductionPayment::where('payroll_id', $data->id)->delete();
        }
    }
    function recoverAdvance($res)
    {

        Advance::where(['employee_id' => $res['employee_id'], 'payroll_code' => $res['payroll_code']])
            ->update(['isDeducted' => '0']);
    }

    function recoverRewards($res)
    {
        Commission::where(['employee_id' => $res['employee_id'], 'payroll_code' => $res['payroll_code']])->update(['isPaid' => 0, 'payroll_code' => $res['payroll_code']]);
    }
    function recoverOvertime($res)
    {
        TimeLog::where(['employee_id' => $res['employee_id'], 'payroll_code' => $res['payroll_code']])->update(['isPaid' => 0, 'payroll_code' => $res['payroll_code']]);
    }
    function payrollCode()
    {

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $payrollcode = $today . sprintf('%04d', $string);
        $res = Payroll::latest()->first();
        if ($res) {
            $payrollcode = $payrollcode . $res->id + 1;
        }
        return $payrollcode;
    }
}
