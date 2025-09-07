<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><reception-balance-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4><b>Room Reservation Closing Shift</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-4">
                                <label for=""><b> Total Closing Amount</b></label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.closing_amount"
                                />
                            </div>
                             <div class="form-group col-4">
                                <label for="">Total Cash Collected</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.cash_amount"
                                />
                            </div>
                            <div class="form-group col-4">
                                <label for="">Mobile Money Collected</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.mobile_money"
                                />
                            </div>

                            <div class="form-group col-4">
                                <label for="">Cheque Amount Collected</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.cheque"
                                />
                            </div>
                            <div class="form-group col-4">
                                <label for="">Bank Transfer Collected</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.bank_transfer_amount"
                                />
                            </div>

                            <div class="form-group col-4">
                                <label for="">Credit Card Collected</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.credit_card"
                                />
                            </div>
                             <div class="form-group col-4">
                                <label for="">Online Payments Collected</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.online_paid"
                                />
                            </div>
                            <div class="form-group col-4">
                                <label for="">Details</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.details"
                                />
                            </div>
                            <div class="form-group col-4 mt-3">
                              
                               
                                 <button
                                v-if="closing_amount_status"
                                @click="saveClosingBalance()"
                                class="btn btn-info btn-sm"
                            >
                                Close Shift $ Print Report
                            </button>
                            <button
                                disabled
                                class="btn btn-warning btn-sm"
                                v-else
                            >
                                No sales Found
                            </button>
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
            <PrintClosingBalanceComponent
            v-if="show_printer"
                ref="PrintClosingBalanceComponent"
                :form_data="form_data"
                
            />
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import ReceptionBalanceNav from "./ReceptionBalanceNav.vue";
import PrintClosingBalanceComponent from "./PrintClosingBalanceComponent.vue";
export default {
    data() {
        return {
            closing_amount_status: false,
            show_printer:false,
            form_data: {
                    opening_amount: 0,
                deficit_amount: 0,
                closing_amount: 0,
                closing_time: null,
                opening_time: null,
                to: null,
                from: null,
                pending_closing: 0,
                sold_amount: 0,
                details: "",
                total_amount: 0,
                cash_amount: 0,
                bank_transfer_amount: 0,
                mobile_money: 0,
                online_paid:0,
                credit_card: 0,
                cheque: 0,

                cash_sold_amount: 0,
                mobile_money_sold_amount: 0,
                online_sold_amount:0,
                card_sold_amount: 0,
                bank_transfer_sold_amount: 0,
                cheque_sold_amount: 0,
            },
        };
    },
    components: {
        ReceptionBalanceNav,
        PrintClosingBalanceComponent
    },

    watch: {},
    methods: {
         async saveClosingBalance() {
            
            this.form_data.deficit_amount = parseFloat(this.form_data.closing_amount)-parseFloat(this.form_data.sold_amount);
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/reception_balance/createClosingBalance",
                this.form_data
            );

            this.hideLoader();
            if (res.status === 200) {
                this.s("Added");
                this.show_printer=true
                 setTimeout(() => {
                    this.$refs.PrintClosingBalanceComponent.printCloseShift();
                    this.concurrentApiReq()
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async closeShiftSales() {
            const res = await this.getApi(
                "data/reception_balance/closeShiftSales"
            );

            if (res.status == 200) {
                var sale_tt = res.data.sales_summary.total_paid;
                this.form_data.sold_amount = sale_tt ? sale_tt : 0;
                this.closing_amount_status = sale_tt ? true : false;

                var cash_tt = res.data.sales_summary.total_cash_paid;
                this.form_data.cash_sold_amount = cash_tt ? cash_tt : 0;

                var total_mm = res.data.sales_summary.total_mobile_money_paid;
                this.form_data.mobile_money_sold_amount = total_mm
                    ? total_mm
                    : 0;

                    var total_online = res.data.sales_summary.total_online_paid;
                this.form_data.online_sold_amount = total_online
                    ? total_online
                    : 0;

                var total_cardd = res.data.sales_summary.total_card_paid;
                this.form_data.card_sold_amount = total_cardd ? total_cardd : 0;

                var total_cheque = res.data.sales_summary.total_cheque_paid;
                this.form_data.cheque_sold_amount = total_cheque
                    ? total_cheque
                    : 0;

                var total_bank_transfer =
                    res.data.sales_summary.total_bank_transfer_paid;
                this.form_data.bank_transfer_sold_amount = total_bank_transfer
                    ? total_bank_transfer
                    : 0;

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

        displayContents(name) {
            return this.activeTabName === name;
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
