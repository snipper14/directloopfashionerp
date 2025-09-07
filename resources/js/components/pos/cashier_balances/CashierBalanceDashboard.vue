<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><cashier-balance-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted && cashier_options=='independent'">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4><b> Close Shifts</b></h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-7">
                                <label for=""
                                    ><b> Closing Cash Amount </b></label
                                >
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.closing_cash_amount"
                                />
                            </div>
                            <div class="form-group col-md-7">
                                <label for="">Opening Cash Amount</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.opening_amount"
                                />
                            </div>
                            <div class="form-group col-md-7">
                                <label for="">Cash Amount Left </label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.cash_left_amount"
                                />
                            </div>

                            <div class="form-group col-md-7">
                                <label for="">Details</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.details"
                                />
                            </div>

                            <div class="form-group col-md-7">
                                <label for="">Closing Time</label>
                                <input
                                    type="text"
                                    disabled
                                    class="form-control"
                                    v-model="form_data.closing_time"
                                />
                            </div>
                            <div class="form-group col-md-7">
                                <button
                                    @click="saveClosingBalance()"
                                    class="btn btn-info btn-sm"
                                >
                                    Close Shift $ Print Report
                                </button>
                                <h2
                                    disabled
                                    style="color: red"
                                    v-if="!closing_amount_status"
                                >
                                    Insufficient Balance
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <div class="row">
            <PrintClosingBalanceReceipt
                v-if="show_print"
                ref="PrintClosingBalanceReceipt"
                :form_data="form_data"
            />
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import CashierBalanceNav from "./CashierBalanceNav.vue";

import PrintClosingBalanceReceipt from "./PrintClosingBalanceReceipt.vue";
export default {
    data() {
        return {
            cashier_options: this.$store.state.branch.cashier_options,
            search: "",
            closing_amount_status: false,
            show_print: false,
            form_data: {
                id: null,
                closing_amount: 0,
                closing_time: null,
                opening_time: null,
                to: null,
                from: null,
                pending_closing: "0",
                sold_amount: 0,
                details: "",
                cash_collected: 0,
                mobile_money_collected: 0,
                card_collected: 0,

                cash_sold_amount: 0,
                mobile_money_sold_amount: 0,
                card_sold_amount: 0,
                conference_sales: 0,
                cash_left_amount: 0,
                closing_cash_amount: 0,
                cash_collected: 0,
                opening_amount: 0,
                expense_payments: 0,
                invoice_payments: 0,
                cash_sales_amount: 0,
            },

            activeTabName: null,
        };
    },
    components: {
        CashierBalanceNav,
        PrintClosingBalanceReceipt,
    },

    watch: {},
    methods: {
        async saveClosingBalance() {
            this.form_data.cash_collected =
                parseFloat(this.form_data.closing_cash_amount) -
                parseFloat(this.form_data.opening_amount);
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/cashier_balance/closeShift",
                this.form_data
            );

            this.hideLoader();
            if (res.status === 200) {
                this.s("Added");
                this.show_print = true;
                //await printJS("printReceipt", "html");
                setTimeout(() => {
                    this.$refs.PrintClosingBalanceReceipt.printReceipt();
                    this.concurrentApiReq();
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async closeShiftSales() {
            const res = await this.getApi(
                "data/cashier_balance/closeShiftSales"
            );

            if (res.status == 200) {
                this.form_data.conference_sales = res.data.conference_sales;
                var sale_tt =
                    parseFloat(res.data.sales_summary.total_sales_amount) +
                    parseFloat(this.form_data.conference_sales);
                this.form_data.sold_amount = sale_tt ? sale_tt : 0;
                this.form_data.expense_payments = parseFloat(
                    res.data.expense_payments
                );
                this.form_data.cash_sales_amount = res.data.sales_summary
                    .total_cash_pay
                    ? parseFloat(res.data.sales_summary.total_cash_pay)
                    : 0;
                this.form_data.invoice_payments = parseFloat(
                    res.data.invoice_payments
                );
                this.form_data.cash_sold_amount =
                    this.form_data.cash_sales_amount +
                    this.form_data.invoice_payments -
                    this.form_data.expense_payments;

                this.closing_amount_status =
                    this.form_data.cash_sold_amount > 0 ? true : false;
                var total_mm = res.data.sales_summary.total_mpesa_pay;
                this.form_data.mobile_money_sold_amount = total_mm
                    ? total_mm
                    : 0;

                var total_cardd = res.data.sales_summary.total_card_pay;
                this.form_data.card_sold_amount = total_cardd ? total_cardd : 0;

                this.form_data.closing_time = res.data.closing_time;
                this.form_data.opening_time = res.data.opening_time;
            } else {
                this.form_error();
            }
        },

        async concurrentApiReq() {
            this.showLoader();

            const res = await Promise.all([this.closeShiftSales(1)]).then(
                function (results) {}
            );

            this.hideLoader();
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
<style scoped>
.is_deducted {
    background: red !important;
}
</style>
