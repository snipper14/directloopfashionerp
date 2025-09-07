<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><cashier-z-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted && cashier_options=='consolidated'">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4><b> Close Shifts</b></h4>
                        
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                            <v-btn
                                color="secondary"
                                @click="show_register_modal = true"
                                small
                                >Open Register</v-btn
                            >
                        </div>
                            <div class="form-group col-6">
                                <label for=""
                                    ><b> Opening Cash Amount</b></label
                                >
                                <input
                                    type="number"
                                    disabled
                                    class="form-control"
                                    v-model="form_data.cashier_opening_cash"
                                />
                            </div>

                            <div class="form-group col-md-7">
                                <label for="">Cash Counted </label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.cashier_counted_cash"
                                />
                            </div>
                            <div class="form-group col-md-7">
                                <label for="">Left Cash Amount</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.cashier_left_cash"
                                />
                            </div>
                            <div class="form-group col-md-8" v-if="false">
                                <fieldset id="cash_denomination">
                                    <legend><p>Cash Denominations</p></legend>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">1000 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_1000"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">500 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_500"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">200 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_200"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">100 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_100"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">50 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_50"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">20 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_20"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">10 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_10"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">5 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_5"
                                            />
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">1 Count</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.count_1"
                                            />
                                        </div>
                                        <div class="col-md-4">
                                            <p class="total_denomination">
                                                Total Denominations
                                                {{
                                                    cashFormatter(
                                                        this.form_data
                                                            .total_denominations
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="form-group col-md-7">
                                <label for="">Opening Mpesa Balance</label>
                                <input
                                    type="number"
                                    disabled
                                    class="form-control"
                                    v-model="form_data.opening_mpesa_balance"
                                />
                            </div>
                            <div class="form-group col-md-7">
                                <label for="">Counted Mpesa Balance</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.counted_mpesa_amount"
                                />
                            </div>
                            <div class="form-group col-md-7">
                                <label for="">Closing Card Balance</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.counted_card_balance"
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
                                <v-btn
                                    v-if="show_btn"
                                    :loading="btn_loader"
                                    @click="closeRegister()"
                                    color="info"
                                >
                                    Close Shift $ Print Report
                                </v-btn>
                                <h4 v-else>No Available Balance</h4>
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
            <print-z-report
                v-if="show_print"
                ref="PrintClosingBalanceReceipt"
                :reprint_data="reprint_data"
            />
        </div>
        <Modal
            v-model="show_register_modal"
            transition="dialog-bottom-transition"
            width="600px"
        >
            <add-register-modal
                v-if="show_register_modal"
                v-on:dismiss-modal="dismissModal"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import AddRegisterModal from "./AddRegisterModal.vue";

import CashierZNav from "./CashierZNav.vue";

import PrintZReport from "./PrintZReport.vue";
export default {
    data() {
        return {
            show_register_modal: false,
            btn_loader: false,
            search: "",
            closing_amount_status: false,
            show_btn: true,
            show_print: false,
            form_data: {
                opening_mpesa_balance: 0,
                closing_mpesa_balance: 0,
                count_1000: 0,
                count_500: 0,
                count_200: 0,
                count_100: 0,
                count_50: 0,
                count_20: 0,
                count_10: 0,
                count_5: 0,
                count_1: 0,
                id: null,

                details: "",
                cash_collected: 0,
                mobile_money_collected: 0,

                counted_mpesa_amount: 0,
                cashier_counted_cash: 0,
                cashier_opening_cash: 0,
                cashier_left_cash: 0,
                counted_card_balance: 0,
                total_denominations: 0,
            },
              cashier_options: this.$store.state.branch.cashier_options,
            reprint_data: null,
            activeTabName: null,
        };
    },
    components: {
        PrintZReport,
        CashierZNav,
        AddRegisterModal,
    },

    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.form_data.total_denominations =
                    parseFloat(this.form_data.count_1000) * 1000 +
                    parseFloat(this.form_data.count_500) * 500 +
                    +(parseFloat(this.form_data.count_200) * 200) +
                    parseFloat(this.form_data.count_100) * 100 +
                    parseFloat(this.form_data.count_50) * 50 +
                    parseFloat(this.form_data.count_20) * 20 +
                    parseFloat(this.form_data.count_10) * 10 +
                    parseFloat(this.form_data.count_5) * 5 +
                    parseFloat(this.form_data.count_1) * 1;
            }, 500),
        },
    },
    methods: {
        dismissModal(){
            this.show_register_modal=false
        },
        async closeRegister() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/register/closeRegister",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("SAVED");
                this.reprint_data = res.data;
                this.show_print = true;
                setTimeout(() => {
                    this.$refs.PrintClosingBalanceReceipt.printReceipt();
                    this.concurrentApiReq();
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async saveClosingBalance() {
            this.form_data.net_mpesa_amount =
                parseFloat(this.form_data.closing_mpesa_balance) -
                parseFloat(this.form_data.opening_mpesa_balance) -
                parseFloat(this.form_data.expected_mpesa_amount);

            if (
                parseFloat(this.form_data.cashier_counted_cash) !=
                this.form_data.total_denominations
            ) {
                this.errorNotif(
                    "Total denominations not matching with cash total"
                );
                return;
            }

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
                this.show_btn = false;
                //await printJS("printReceipt", "html");
                setTimeout(() => {
                    this.$refs.PrintClosingBalanceReceipt.printReceipt();
                    this.concurrentApiReq();
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async fetchRegisterOpeningBalances() {
            const res = await this.getApi(
                "data/register/fetchRegisterOpeningBalances"
            );
            if (res.status == 200) {
                this.form_data.opening_mpesa_balance =
                    res.data.opening_mpesa_balance;
                this.form_data.cashier_opening_cash =
                    res.data.opening_cash_balance;
            } else {
                this.form_error(res);
            }
        },
        async closeShiftSales() {
            const res = await this.getApi(
                "data/cashier_balance/closeShiftSales"
            );

            if (res.status == 200) {
                var sale_tt =
                    parseFloat(res.data.sales_summary.total_sales_amount) +
                    parseFloat(this.form_data.conference_sales);
                this.form_data.sold_amount = sale_tt ? sale_tt : 0;
                this.closing_amount_status = sale_tt ? true : false;

                var cash_tt = res.data.sales_summary.total_cash_pay;
                this.form_data.cash_sold_amount = cash_tt ? cash_tt : 0;
                this.form_data.total_credit_cash = res.data.cash_credit;
                this.form_data.total_debit_cash = res.data.cash_debit;
                var total_mm = res.data.sales_summary.total_mpesa_pay;
                this.form_data.mobile_money_sold_amount = total_mm
                    ? total_mm
                    : 0;
                this.form_data.system_total_cash =
                    parseFloat(
                        this.form_data.total_debit_cash +
                            parseFloat(this.form_data.cash_sold_amount)
                    ) - parseFloat(this.form_data.total_credit_cash);
                var total_cardd = res.data.sales_summary.total_card_pay;
                this.form_data.card_sold_amount = total_cardd ? total_cardd : 0;

                this.form_data.closing_time = res.data.closing_time;
                this.form_data.opening_time = res.data.opening_time;
                this.form_data.expected_mpesa_amount =
                    res.data.expected_mpesa_amount;
            } else {
                this.form_error();
            }
        },

        async concurrentApiReq() {
            this.showLoader();

            const res = await Promise.all([
                this.fetchRegisterOpeningBalances(),
            ]).then(function (results) {});

            this.hideLoader();
        },
       
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
<style scoped>
.total_denomination {
    color: #007bff;
    font-weight: 800;
    font-family: Arial, sans-serif;
    font-size: 1rem;
}
.is_deducted {
    background: red !important;
}
fieldset {
    border: 2px solid #007bff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

legend {
    color: #007bff;
    font-size: 1.5em;
    padding: 0 10px;
    font-weight: bold;
}

label {
    display: block;
    margin-bottom: 10px;
    color: #333;
}
#cash_denomination input {
    margin-top: 0px !important;
    margin-bottom: 0px !important;
}
</style>
