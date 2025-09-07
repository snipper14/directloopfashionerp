<template>
    <div class="container">
        <div class="row">
            <div class="col-6" style="background: #d7ccc8">
                <fieldset class="border mb-4">
                    <div
                        class="col-md-12 d-flex flex-column justify-content-between"
                    >
                        <div class="form-group d-flex flex-row">
                            <label for="">Cash Pay</label>
                            <input
                                type="number"
                                v-model="form_data.cash_pay"
                                placeholder="Cash Pay"
                                @focus="changeKeyboardCash"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group d-flex flex-row">
                            <label for="">Mpesa</label>
                            <input
                                type="number"
                                v-model="form_data.mpesa_pay"
                                @focus="changeKeyboardMpesa"
                                placeholder="Mpesa"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group d-flex flex-row">
                            <label for="">Card</label>
                            <input
                                type="number"
                                v-model="form_data.card_pay"
                                placeholder="Card"
                                @focus="changeKeyboardCard"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group d-flex flex-row">
                            <label for="">ref No</label>
                            <input
                                type="text"
                                v-model="form_data.payment_ref"
                                placeholder="Reference No"
                                class="form-control"
                            />
                        </div>
                    </div>
                </fieldset>
                <custom-board
                    style="margin-top: 4px"
                    @pressed="form_data.cash_pay = $event"
                    v-if="numeric_keyboard.cash"
                    :selfValue="form_data.cash_pay"
                />
                <custom-board
                    style="margin-top: 4px"
                    v-if="numeric_keyboard.mpesa"
                    @pressed="form_data.mpesa_pay = $event"
                    :selfValue="form_data.mpesa_pay"
                />
                <custom-board
                    style="margin-top: 4px"
                    v-if="numeric_keyboard.card"
                    @pressed="form_data.card_pay = $event"
                    :selfValue="form_data.card_pay"
                />
            </div>
            <div class="col-5">
                <div class="col-md-11" style="background: #fb8c00">
                    <h3 class="t-amount">
                        RECEIPT TOTAL:
                        {{ cashFormatter(form_data.receipt_total) }}
                    </h3>
                </div>

                <div class="col-md-11" style="background: #fb8c00">
                    <h3 class="t-amount">
                        BALANCE:
                        {{ cashFormatter(form_data.receipt_balance) }}
                    </h3>
                </div>
                <div class="col-md-11" style="background: #fb8c00">
                    <p class="t-amount">
                        TAX AMOUNT():
                        {{ cashFormatter(round(form_data.total_vat)) }}
                    </p>
                </div>
                <div class="col-md-11" style="background: #fb8c00">
                    <p class="t-amount" v-if="form_data.cash_pay.length > 0">
                        CASH:
                        {{ cashFormatter(round(form_data.cash_pay)) }}
                    </p>
                    <p class="t-amount" v-if="form_data.mpesa_pay.length > 0">
                        M-PESA:
                        {{ cashFormatter(round(form_data.mpesa_pay)) }}
                    </p>
                    <p class="t-amount" v-if="form_data.card_pay.length > 0">
                        Card:
                        {{ cashFormatter(round(form_data.card_pay)) }}
                    </p>
                </div>
                <div class="d-flex flex-row justify-content-between mt-2">
                    <button
                        class="btn btn-primary btn-lg"
                        @click="printReceipt()"
                    >
                        SAVE
                    </button>
                    <button
                        class="btn btn-secondary"
                        v-if="false"
                        @click="printReceipt()"
                    >
                        SAVE & PRINT
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CustomBoard from "../menu_components/dinerscomponents/CustomBoard.vue";
export default {
    components: { CustomBoard },
    props: ["order_no", "order_data", "total_order", "total_tax"],
    data() {
        return {
            bill_total: 0,
            tax_total: 0,
            numeric_keyboard: {
                cash: true,
                card: false,
                mpesa: false,
            },
            form_data: {
                card_pay: 0,
                mpesa_pay: 0,
                cash_pay: 0,
                payment_ref: "",
                order_no: "",
                receipt_no: "",
                receipt_balance: 0,
                paid_date: "",
                receipt_total: 0,
                total_vat: 0,
                customer_id: null,
                user_id: null,
                order_date: "",
                order_date: "",
                order_type: "",
                employee_id: null,
                cashier_status: "uncleared",
            },
        };
    },
    mounted() {
        this.form_data.order_no = this.order_no;
        this.form_data.receipt_total = this.total_order;
        this.form_data.total_vat = this.total_tax;
        this.form_data.customer_id = this.order_data[0].customer_id;
        this.form_data.user_id = this.order_data[0].user_id;
        this.form_data.order_date = this.order_data[0].order_date;
        this.form_data.order_type = this.order_data[0].order_type;
    },
    watch: {
        form_data: {
            handler() {
                this.calculateBalances();
            },
            deep: true,
        },
    },
    methods: {
        changeKeyboardMpesa() {
            this.changeKeyboardStatus("mpesa");
        },
        changeKeyboardCard() {
            this.changeKeyboardStatus("card");
        },
        changeKeyboardCash() {
            this.changeKeyboardStatus("cash");
        },
        changeKeyboardStatus(paytype) {
            Object.keys(this.numeric_keyboard).forEach(
                (key) => (this.numeric_keyboard[key] = false)
            );
            this.numeric_keyboard[paytype] = true;
        },
        calculateBalances() {
            var pending_balance = 0;

            if (this.form_data.cash_pay == "") {
                this.form_data.cash_pay = 0;
            }
            if (this.form_data.mpesa_pay == "") {
                this.form_data.mpesa_pay = 0;
            }
            if (this.form_data.card_pay == "") {
                this.form_data.card_pay = 0;
            }

            var cash = parseFloat(this.form_data.cash_pay);
            var card = parseFloat(this.form_data.card_pay);
            var mpesa = parseFloat(this.form_data.mpesa_pay);
            var total = parseFloat(this.form_data.receipt_total);

            this.form_data.receipt_balance = total - (cash + card + mpesa);
        },
        generateReceiptNo() {
            const receptNo =
                this.$store.state.branch.id +
                "_" +
                this.$store.state.user.id +
                "_" +
                (
                    Date.now().toString(36) +
                    Math.random().toString(36).substr(2, 5)
                ).toUpperCase();

            this.form_data.receipt_no = receptNo;
        },
        async printReceipt() {
            this.generateReceiptNo();

            this.form_data.paid_date = this.getCurrentDate();

            if (parseFloat(this.form_data.receipt_balance) > 0) {
                this.swr("Pending payments...");
                return;
            }
            if (!parseFloat(this.form_data.receipt_total) > 0) {
                this.swr("Empty receipt totals...");
                return;
            }
            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/pos_sale/create",
                this.form_data
            );

            this.hideLoader();

            if (res.status === 200) {
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = 0)
                );
                this.successNotif(" Successfully saved");
                this.$emit("dismiss-diag");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
