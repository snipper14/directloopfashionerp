<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-9" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header-wrapper">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <label> From Month *</label>
                                    <date-picker
                                        v-model="params.from"
                                        type="month"
                                        valueType="format"
                                        format="YYYY-MM"
                                 
                                        placeholder="Select start month"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column mr-2">
                                    <label> To Month *</label>
                                    <date-picker
                                        v-model="params.to"
                                        type="month"
                                        valueType="format"
                                        format="YYYY-MM"
                                        
                                        placeholder="Select end month"
                                    ></date-picker>
                                </div>
                            </div>
                            <div>
                                <v-btn
                                    color="primary"
                                    small
                                    @click="fetchProfitLoss"
                                    >Filter</v-btn
                                >
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12" ref="contentToPrint">
                            <div class="row">
                                <div class="col-md-12">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th colspan="100">
                                                    <center>
                                                        <h4>
                                                            PROFIT & LOSS REPORT
                                                            ({{
                                                                params.from
                                                            }}
                                                            to {{ params.to }})
                                                        </h4>
                                                    </center>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered custom-table"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>Income</th>
                                                    <th
                                                        v-for="month in profitLossData.months"
                                                        :key="month"
                                                    >
                                                        {{ month }}
                                                    </th>
                                                    <th>Cumulative</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Cash Sales</td>
                                                    <td
                                                        v-for="(
                                                            value, index
                                                        ) in profitLossData.data
                                                            .cash_sales"
                                                        :key="
                                                            'cash_sales_' +
                                                            index
                                                        "
                                                    >
                                                        {{
                                                            cashFormatter(value)
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                profitLossData
                                                                    .cumulative
                                                                    .cash_sales
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Credit Sales</td>
                                                    <td
                                                        v-for="(
                                                            value, index
                                                        ) in profitLossData.data
                                                            .credit_sales"
                                                        :key="
                                                            'credit_sales_' +
                                                            index
                                                        "
                                                    >
                                                        {{
                                                            cashFormatter(value)
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                profitLossData
                                                                    .cumulative
                                                                    .credit_sales
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Cost of Goods Sold</td>
                                                    <td
                                                        v-for="(
                                                            value, index
                                                        ) in profitLossData.data
                                                            .cost_of_goods_sold"
                                                        :key="'cogs_' + index"
                                                    >
                                                        {{
                                                            cashFormatter(value)
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                profitLossData
                                                                    .cumulative
                                                                    .cost_of_goods_sold
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><b>Gross Profit</b></td>
                                                    <td
                                                        v-for="(
                                                            value, index
                                                        ) in profitLossData.data
                                                            .gross_profit"
                                                        :key="
                                                            'gross_profit_' +
                                                            index
                                                        "
                                                    >
                                                        <b>{{
                                                            cashFormatter(value)
                                                        }}</b>
                                                    </td>
                                                    <td>
                                                        <b>{{
                                                            cashFormatter(
                                                                profitLossData
                                                                    .cumulative
                                                                    .gross_profit
                                                            )
                                                        }}</b>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered custom-table"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>Expenses</th>
                                                    <th
                                                        v-for="month in profitLossData.months"
                                                        :key="month"
                                                    >
                                                        {{ month }}
                                                    </th>
                                                    <th>Cumulative</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(
                                                        account, accountIndex
                                                    ) in expenseAccounts"
                                                    :key="
                                                        'expense_' +
                                                        accountIndex
                                                    "
                                                >
                                                    <td>{{ account }}</td>
                                                    <td
                                                        v-for="(
                                                            monthExpenses,
                                                            monthIndex
                                                        ) in profitLossData.data
                                                            .expenses"
                                                        :key="
                                                            'expense_' +
                                                            accountIndex +
                                                            '_' +
                                                            monthIndex
                                                        "
                                                    >
                                                        {{
                                                            cashFormatter(
                                                                getExpenseAmount(
                                                                    account,
                                                                    monthExpenses
                                                                )
                                                            )
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                getCumulativeExpenseAmount(
                                                                    account
                                                                )
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <b>Total Expenses</b>
                                                    </td>
                                                    <td
                                                        v-for="(
                                                            monthExpenses, index
                                                        ) in profitLossData.data
                                                            .expenses"
                                                        :key="
                                                            'total_expenses_' +
                                                            index
                                                        "
                                                    >
                                                        <b>{{
                                                            cashFormatter(
                                                                monthExpenses.reduce(
                                                                    (
                                                                        sum,
                                                                        exp
                                                                    ) =>
                                                                        sum +
                                                                        exp.dr_amount,
                                                                    0
                                                                )
                                                            )
                                                        }}</b>
                                                    </td>
                                                    <td>
                                                        <b>{{
                                                            cashFormatter(
                                                                profitLossData
                                                                    .cumulative
                                                                    .total_expenses
                                                            )
                                                        }}</b>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered custom-table"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>PROFIT/LOSS</th>
                                                    <th
                                                        v-for="(
                                                            value, index
                                                        ) in profitLossData.data
                                                            .profit_loss"
                                                        :key="
                                                            'profit_loss_' +
                                                            index
                                                        "
                                                    >
                                                        {{
                                                            cashFormatter(value)
                                                        }}
                                                    </th>
                                                    <th>
                                                        {{
                                                            cashFormatter(
                                                                profitLossData
                                                                    .cumulative
                                                                    .profit_loss
                                                            )
                                                        }}
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <v-btn
                                color="primary"
                                medium
                                @click="printProfitLoss"
                                >Print Profit & Loss</v-btn
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
import LedgerNav from "../general_ledger/LedgerNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";

export default {
    components: {
        LedgerNav,
        Unauthorized,
        DatePicker,
    },
    data() {
        return {
            params: {
                from: this.getCurrentDate().slice(0, 7), // YYYY-MM format
                to: this.getCurrentDate().slice(0, 7),
            },
            profitLossData: {
                months: [],
                data: {
                    cash_sales: [],
                    credit_sales: [],
                    cost_of_goods_sold: [],
                    gross_profit: [],
                    expenses: [],
                    profit_loss: [],
                },
                cumulative: {
                    cash_sales: 0,
                    credit_sales: 0,
                    cost_of_goods_sold: 0,
                    gross_profit: 0,
                    expenses: [],
                    total_expenses: 0,
                    profit_loss: 0,
                },
            },
            expenseAccounts: [],
            isLoading: false,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiCash,
            },
        };
    },
    mounted() {
        this.fetchProfitLoss();
    },
    methods: {
        async fetchProfitLoss() {
            // Validate YYYY-MM format
            if (
                !/^\d{4}-\d{2}$/.test(this.params.from) ||
                !/^\d{4}-\d{2}$/.test(this.params.to)
            ) {
                this.swr(
                    "Invalid date format. Please select months in YYYY-MM format."
                );
                return;
            }
            this.showLoader();
            const res = await this.getApi("data/profit_loss/fetchProfitLoss", {
                params: this.params,
            });
            this.hideLoader();
            if (res.status === 200) {
                this.profitLossData = res.data;

                // Extract unique expense accounts from both monthly and cumulative expenses
                const allExpenses = [
                    ...res.data.data.expenses.flat(),
                    ...res.data.cumulative.expenses,
                ];
                this.expenseAccounts = [
                    ...new Set(allExpenses.map((exp) => exp.account)),
                ];
            } else if (res.status === 422) {
                this.swr(
                    "Invalid date range. Please select a range within 5 months."
                );
            } else {
                this.swr("Server error, please try again later");
            }
        },
        getExpenseAmount(account, monthExpenses) {
            const expense = monthExpenses.find(
                (exp) => exp.account === account
            );
            return expense ? expense.dr_amount : 0;
        },
        getCumulativeExpenseAmount(account) {
            const expense = this.profitLossData.cumulative.expenses.find(
                (exp) => exp.account === account
            );
            return expense ? expense.dr_amount : 0;
        },
        // disableBeforeFromDate(date) {
        //     if (!this.params.from) return false;
        //     const fromDate = new Date(this.params.from + "-01");
        //     return date < fromDate;
        // },
        // disableAfterToDate(date) {
        //     if (!this.params.to) return false;
        //     const toDate = new Date(this.params.to + "-01");
        //     return date > toDate;
        // },
        printProfitLoss() {
            const contentToPrint = this.$refs.contentToPrint;
            const printWindow = window.open("", "", "");
            printWindow.document.open();
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Profit & Loss</title>
                        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
                        <style>
                            .custom-table { border-collapse: collapse; width: 100%; }
                            .custom-table th, .custom-table td { border: 1px solid #000; padding: 8px; }
                            .table-title { padding: 10px; }
                        </style>
                    </head>
                    <body>
                        ${contentToPrint.innerHTML}
                    </body>
                </html>`);
            printWindow.document.close();
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        },
     
        swr(message) {
            this.$notify({
                group: "foo",
                type: "error",
                title: "Error",
                text: message,
            });
        },
    },
};
</script>

<style scoped>
/* Add any custom styles if needed */
</style>
