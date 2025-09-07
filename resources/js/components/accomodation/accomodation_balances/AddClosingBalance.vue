<template>
    <div class="container">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <b>Opening Time:</b>{{ form_data.opening_time }} <br />
                    <b>Opening Balance:</b
                    >{{ cashFormatter(form_data.opening_amount) }} <br />
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

                <div class="form-group">
                    <label for="">Total Sales</label>
                    <input
                        disabled
                        type="number"
                        class="form-control"
                        v-model="form_data.sold_amount"
                    />
                </div>
                <div class="d-flex">
                    <div class="form-group">
                        <label for="">Total Cash</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.cash_amount"
                        />
                    </div>

                    <div class="form-group">
                        <label for="">Mobile Money Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.mobile_money"
                        />
                    </div>
                </div>
                <div class="d-flex">
                    <div class="form-group">
                        <label for="">Cheque Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.cheque"
                        />
                    </div>
                    <div class="form-group">
                        <label for="">Bank Transfer Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.bank_transfer_amount"
                        />
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Credit Card</label>
                    <input
                        type="number"
                        class="form-control"
                        v-model="form_data.credit_card"
                    />
                </div>
                <div class="form-group">
                    <label for="">Details</label>
                    <input
                        type="text"
                        class="form-control"
                        v-model="form_data.details"
                    />
                </div>

                <div class="form-group">
                    <button
                        @click="saveClosingBalance()"
                        class="btn btn-primary btn-sm"
                    >
                        Save Details
                    </button>
                </div>
            </div>
            <div class="col-5">
                <ul class="list-group">
                    <h4>
                        <b
                            >Total Amount:
                            {{ cashFormatter(form_data.total_amount) }}</b
                        >
                    </h4>
                    <h4>
                        <b
                            >Total Paid:
                            {{ cashFormatter(form_data.sold_amount) }}</b
                        >
                    </h4>
                    <li class="list-group-item">
                        <br />
                        <b
                            >Cash Paid:
                            {{ cashFormatter(balance_data.total_cash_paid) }}
                        </b>
                    </li>
                    <li class="list-group-item">
                        <br />
                        <b
                            >Mobile Money Paid:
                            {{
                                cashFormatter(
                                    balance_data.total_mobile_money_paid
                                )
                            }}
                        </b>
                    </li>
                    <li class="list-group-item">
                        <br />
                        <b
                            >Credit Card Paid:
                            {{ cashFormatter(balance_data.total_card_paid) }}
                        </b>
                    </li>
                    <li class="list-group-item">
                        <br />
                        <b
                            >Cheque Paid:
                            {{ cashFormatter(balance_data.total_cheque_paid) }}
                        </b>
                    </li>
                    <li class="list-group-item">
                        <br />
                        <b
                            >Bank Transfer:
                            {{
                                cashFormatter(
                                    balance_data.total_bank_transfer_paid
                                )
                            }}
                        </b>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row">
            <PrintClosingBalanceComponent
                ref="PrintClosingBalanceComponent"
                :form_data="form_data"
                :balance_data="balance_data"
            />
        </div>
       
    </div>
</template>
<script>
import PrintClosingBalanceComponent from "./PrintClosingBalanceComponent.vue";
export default {
    components: {  PrintClosingBalanceComponent },
    data() {
        return {
            balance_data: [],

            form_data: {
                id: null,
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
            },
            total_obj: null,
        };
    },
    mounted() {
        this.fetchOpeningTime();
    },

    methods: {
        async getTotal() {
            if (this.form_data.to == null) {
                this.errorNotif("Select closing time");
                return;
            }

            this.form_data.closing_time = this.form_data.to;
            this.showLoader();
            const res = await this.getApi("data/reception_balance/showTotal", {
                params: {
                    from: this.formatDateTime(this.form_data.opening_time),
                    to: this.formatDateTime(this.form_data.closing_time),
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.balance_data = res.data;
                this.form_data.sold_amount = this.balance_data.total_paid;
                this.form_data.total_amount = this.balance_data.total_amount;
            } else {
                this.form_error(res);
            }
        },
        async fetchOpeningTime() {
            this.showLoader();
            const res = await this.getApi(
                "data/reception_balance/getOpeningTime"
            );
            this.hideLoader();
            if (res.status == 200) {
                this.form_data.opening_time = res.data.opening_time;
                this.form_data.id = res.data.id;
                this.form_data.from = res.data;
                this.form_data.opening_amount = res.data.opening_amount;
            } else {
                this.form_error();
            }
        },
        async saveClosingBalance() {
            this.form_data.closing_amount =
                parseFloat(this.form_data.cash_amount) +
                parseFloat(this.form_data.bank_transfer_amount) +
                parseFloat(this.form_data.mobile_money) +
                  parseFloat(this.form_data.online_paid) +
                parseFloat(this.form_data.credit_card) +
                parseFloat(this.form_data.cheque);
            this.form_data.deficit_amount =
                parseFloat(this.form_data.opening_amount) +
                parseFloat(this.form_data.sold_amount) -
                parseFloat(this.form_data.closing_amount);
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/reception_balance/createClosingBalance",
                this.form_data
            );

            this.hideLoader();
            if (res.status === 200) {
                this.s("Added");
                 setTimeout(() => {
                    this.$refs.PrintClosingBalanceComponent.printReceipt();
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
