<template>
    <div class="container">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <b>Opening Time:</b>{{ form_data.opening_time }}
                </div>
                <div class="">
                    <div class="form-group">
                        <label>Closing Time *</label
                        ><date-picker
                            v-model="form_data.to"
                            type="datetime"
                        ></date-picker>
                    </div>

                    <button
                        class="btn btn-primary btn-sm mr-4 mb-2"
                        @click="getTotal()"
                    >
                        Get Total Sales
                    </button>
                </div>
                <div class="row">
                    <div class="form-group col-6" v-if="show_amount">
                        <label for="">Total Sales</label>
                        <input
                            disabled
                            type="number"
                            class="form-control"
                            v-model="form_data.sold_amount"
                        />
                    </div>

                    <div class="form-group col-6">
                        <label for="">Closing Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.closing_amount"
                        />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-4">
                        <label for="">Cash Collected</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.cash_collected"
                        />
                    </div>
                    <div class="form-group col-4">
                        <label for="">Mobile Money Collected</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.mobile_money_collected"
                        />
                    </div>

                    <div class="form-group col-4">
                        <label for="">CardPay Collected</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.card_collected"
                        />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-6">
                        <label for="">Details</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form_data.details"
                        />
                    </div>

                    <div class="form-group col-6"></div>
                    <button
                        @click="saveClosingBalance()"
                        class="btn btn-primary btn-sm"
                    >
                        Save Details
                    </button>
                </div>
            </div>
            <div class="col-5" v-if="total_obj && show_amount">
                <ul class="list-group">
                    <h4><b>Dine In</b></h4>
                    <li class="list-group-item" v-if="total_obj">
                        <br />
                        <b
                            >Dine In Total:
                            {{
                                total_obj.dinein_total.total_sales_amount
                                    ? cashFormatter(
                                          total_obj.dinein_total
                                              .total_sales_amount
                                      )
                                    : 0
                            }}
                        </b>
                    </li>

                    <li class="list-group-item" v-if="total_obj">
                        <b
                            >Cash Paid:
                            {{
                                total_obj.dinein_total.total_cash_pay
                                    ? cashFormatter(
                                          total_obj.dinein_total.total_cash_pay
                                      )
                                    : 0
                            }}
                        </b>
                    </li>

                    <li class="list-group-item" v-if="total_obj">
                        <b
                            >Mpesa Paid:
                            {{
                                total_obj.dinein_total.total_mpesa_pay
                                    ? cashFormatter(
                                          total_obj.dinein_total.total_mpesa_pay
                                      )
                                    : 0
                            }}
                        </b>
                    </li>
                    <li class="list-group-item" v-if="total_obj">
                        <b
                            >Card Paid:
                            {{
                                total_obj.dinein_total.total_card_pay
                                    ? cashFormatter(
                                          total_obj.dinein_total.total_card_pay
                                      )
                                    : 0
                            }}
                        </b>
                    </li>
                </ul>

                <ul class="list-group">
                    <h4><b>Room Service Total</b></h4>
                    <li class="list-group-item" v-if="total_obj">
                        <br />
                        <b
                            >RoomService Total:
                            {{
                                total_obj.room_service_sales.total_sales_amount
                                    ? cashFormatter(
                                          total_obj.room_service_sales
                                              .total_sales_amount
                                      )
                                    : 0
                            }}
                        </b>
                    </li>

                    <li class="list-group-item" v-if="total_obj">
                        <b
                            >Cash Paid:
                            {{
                                total_obj.room_service_sales.total_cash_pay
                                    ? cashFormatter(
                                          total_obj.room_service_sales
                                              .total_cash_pay
                                      )
                                    : 0
                            }}
                        </b>
                    </li>

                    <li class="list-group-item" v-if="total_obj">
                        <b
                            >Mpesa Paid:
                            {{
                                total_obj.room_service_sales.total_mpesa_pay
                                    ? cashFormatter(
                                          total_obj.room_service_sales
                                              .total_mpesa_pay
                                      )
                                    : 0
                            }}
                        </b>
                    </li>
                    <li class="list-group-item" v-if="total_obj">
                        <b
                            >Card Paid:
                            {{
                                total_obj.room_service_sales.total_card_pay
                                    ? cashFormatter(
                                          total_obj.room_service_sales
                                              .total_card_pay
                                      )
                                    : 0
                            }}
                        </b>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <PrintClosingBalanceReceipt
                ref="PrintClosingBalanceReceipt"
                :form_data="form_data"
            />
        </div>

    
    </div>
</template>
<script>
import PrintClosingBalanceReceipt from "./PrintClosingBalanceReceipt.vue";
export default {
    components: { PrintClosingBalanceReceipt },
    data() {
        return {
            show_amount:false,
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

                total_cash: 0,
                total_mm: 0,
                total_cardd: 0,
            },
            total_obj: null,
        };
    },
    mounted() {
       // this.fetchOpeningTime();
       this.closeShiftSales();
       
    },
    methods: {
        async closeShiftSales() {
            this.showLoader();
            const res = await this.getApi(
                "data/cashier_balance/closeShiftSales"
            );
            this.hideLoader();
            if (res.status == 200) {
                console.log();
            } else {
                this.form_error();
            }
        },
        async getTotal() {
            if (this.form_data.to == null) {
                this.errorNotif("Select closing time");
                return;
            }

            this.form_data.closing_time = this.form_data.to;
            this.showLoader();
            const res = await this.getApi("data/cashier_balance/showTotal", {
                params: {
                    from: this.formatDateTime(this.form_data.opening_time),
                    to: this.formatDateTime(this.form_data.closing_time),
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.total_obj = res.data;
                this.form_data.sold_amount = this.total_obj.total_sales;

                var cash_dinein = this.total_obj.dinein_total.total_cash_pay
                    ? this.total_obj.dinein_total.total_cash_pay
                    : 0;
                var cash_rs = this.total_obj.room_service_sales.total_cash_pay
                    ? this.total_obj.room_service_sales.total_cash_pay
                    : 0;

                var mm_dinein = this.total_obj.dinein_total.total_mpesa_pay
                    ? this.total_obj.dinein_total.total_mpesa_pay
                    : 0;
                var mm_rs = this.total_obj.room_service_sales.total_mpesa_pay
                    ? this.total_obj.room_service_sales.total_mpesa_pay
                    : 0;

                var card_dinein = this.total_obj.dinein_total.total_card_pay
                    ? this.total_obj.dinein_total.total_card_pay
                    : 0;
                var card_rs = this.total_obj.room_service_sales.total_card_pay
                    ? this.total_obj.room_service_sales.total_card_pay
                    : 0;

                this.form_data.total_cash = cash_dinein + cash_rs;
                this.form_data.total_mm = mm_dinein + mm_rs;
                this.form_data.total_cardd = card_dinein + card_rs;
            } else {
                this.form_error(res);
            }
        },
        async fetchOpeningTime() {
            this.showLoader();
            const res = await this.getApi(
                "data/cashier_balance/getOpeningTime"
            );
            this.hideLoader();
            if (res.status == 200) {
                this.form_data.opening_time = res.data.opening_time;
                this.form_data.id = res.data.id;
                this.form_data.from = res.data;
            } else {
                this.form_error();
            }
        },
        async saveClosingBalance() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/cashier_balance/createClosingBalance",
                this.form_data
            );

            this.hideLoader();
            if (res.status === 200) {
                this.s("Added");
                //await printJS("printReceipt", "html");
                setTimeout(() => {
                    this.$refs.PrintClosingBalanceReceipt.printReceipt();
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
<style scoped>
#printReceipt {
    visibility: hidden;
}
</style>
