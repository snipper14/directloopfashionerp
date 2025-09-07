<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Exchange Cashout</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6" style="background: #d7ccc8">
                                <fieldset class="border mb-4">
                                    <legend class="text-center">
                                        <b>Payments</b>
                                    </legend>
                                    <div
                                        class="col-md-12 d-flex flex-column justify-content-between"
                                    >
                                        <div class="form-group d-flex flex-row">
                                            <label for="">Cash Pay</label>
                                            <input
                                                type="number"
                                                v-model="form_data.cash_pay"
                                                placeholder="Cash Pay"
                                                class="form-control"
                                            />
                                        </div>
                                        <div class="form-group d-flex flex-row">
                                            <label for="">Mpesa</label>
                                            <input
                                                type="number"
                                                v-model="form_data.mpesa_pay"
                                                placeholder="Mpesa"
                                                class="form-control"
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label for=""
                                                >Cashier Terminal
                                            </label>
                                            <treeselect
                                                :load-options="
                                                    fetchCashierAccounts
                                                "
                                                :options="terminal_select_data"
                                                :auto-load-root-options="false"
                                                v-model="
                                                    form_data.card_terminal_account_id
                                                "
                                                :multiple="false"
                                                :show-count="true"
                                                placeholder="Select   Terminal "
                                            />
                                        </div>
                                        <div class="form-group d-flex flex-row">
                                            <label for="">Card</label>
                                            <input
                                                type="number"
                                                v-model="form_data.card_pay"
                                                placeholder="Card"
                                                class="form-control"
                                            />
                                        </div>

                                        <div class="form-group d-flex flex-row">
                                            <label for="">Credit</label>
                                            <input
                                                type="number"
                                                v-model="form_data.credit_pay"
                                                placeholder="Card"
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
                                        <div class="row">
                                            <div class="col-md-6">
                                                <v-btn
                                                    color="secondary"
                                                    v-if="isDownloadPermitted"
                                                    :loading="btn_loading"
                                                    @click="printReceipt(true)"
                                                >
                                                    SAVE & PRINT
                                                </v-btn>
                                            </div>

                                            <div class="col-md-6">
                                                <v-btn
                                                    color="primary"
                                                    :loading="btn_loading"
                                                    @click="printReceipt(false)"
                                                >
                                                    SAVE
                                                </v-btn>
                                            </div>
                                            <div class="col-md-6">
                                                <v-btn
                                                    color="info"
                                                    :loading="btn_loading"
                                                    @click="printOnEtims(true)"
                                                >
                                                    Sale On Etims
                                                </v-btn>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="col-md-11"
                                    style="background: #81d4fa"
                                >
                                    <h3 class="t-amount">
                                        RECEIPT TOTAL:
                                        {{
                                            cashFormatter(
                                                form_data.receipt_total
                                            )
                                        }}
                                    </h3>
                                </div>

                                <div
                                    class="col-md-11"
                                    style="background: #81d4fa"
                                >
                                    <h3 class="t-amount">
                                        BALANCE:
                                        {{
                                            cashFormatter(
                                                form_data.receipt_balance
                                            )
                                        }}
                                    </h3>
                                </div>
                                <div
                                    class="col-md-11"
                                    style="background: #81d4fa"
                                >
                                    <p class="t-amount">
                                        TAX AMOUNT():
                                        {{
                                            cashFormatter(
                                                round(form_data.total_vat)
                                            )
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="col-md-11"
                                    style="background: #81d4fa"
                                >
                                    <p
                                        class="t-amount"
                                        v-if="form_data.cash_pay.length > 0"
                                    >
                                        CASH:
                                        {{
                                            cashFormatter(
                                                round(form_data.cash_pay)
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="t-amount"
                                        v-if="form_data.mpesa_pay.length > 0"
                                    >
                                        M-PESA:
                                        {{
                                            cashFormatter(
                                                round(form_data.mpesa_pay)
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="t-amount"
                                        v-if="form_data.card_pay.length > 0"
                                    >
                                        Card:
                                        {{
                                            cashFormatter(
                                                round(form_data.card_pay)
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="t-amount"
                                        v-if="form_data.credit_pay.length > 0"
                                    >
                                        Credit Pay:
                                        {{
                                            cashFormatter(
                                                round(form_data.credit_pay)
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <PrintReceipt
                    ref="ReceiptPrintComponent"
                    v-if="load_printer_component"
                    :form_data="form_data"
                    :order_data="order_data_offline"
                    :etims_data="etims_data"
                    :etims_qrcode_data="etims_qrcode_data"
                />
            </div>
        </div>
    </div>
</template>

<script>
import Swal from "sweetalert2/dist/sweetalert2.js";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import PrintReceipt from "../../pos/menu_components/PrintReceipt.vue";
export default {
    props: ["order_data_offline", "formData"],
    data() {
        return {
            load_printer_component: false,
            btn_loading: false,
            etims_data: {},
            etims_qrcode_data: {},
            form_data: {
                card_terminal_account_id: null,
                card_pay: 0,
                mpesa_pay: 0,
                cash_pay: 0,
                credit_pay: 0,
                payment_ref: "",
                order_no: "",
                receipt_no: "",
                receipt_balance: 0,
                paid_date: "",
                receipt_total: 0,
                total_vat: 0,
                credit_no: "",
                order_date: "",
                customer_id: null,
                customer_name: "",
            },
        };
    },
    components: { Treeselect, Swal, PrintReceipt },
    mounted() {
        this.form_data.receipt_total = this.formData.topupReceiptAmount;
        this.form_data.total_vat = this.formData.sale_total_tax;
        this.form_data.order_no = this.order_data_offline[0].order_no;
        this.form_data.receipt_no = this.order_data_offline[0].order_no;
        this.form_data.customer_id = this.order_data_offline[0].customer_id;
        this.form_data.customer_name = this.order_data_offline[0].customer_name;
        this.form_data.credit_no = this.formData.credit_no;
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
            if (this.form_data.credit_pay == "") {
                this.form_data.credit_pay = 0;
            }

            var cash = parseFloat(this.form_data.cash_pay);
            var card = parseFloat(this.form_data.card_pay);
            var mpesa = parseFloat(this.form_data.mpesa_pay);
            var credit = parseFloat(this.form_data.credit_pay);
            var total = parseFloat(this.form_data.receipt_total);

            this.form_data.receipt_balance =
                total - (cash + card + mpesa + credit);
        },
        async printReceipt(print_receipt) {
            this.form_data.paid_date = this.getCurrentDate();
            if (
                parseFloat(this.form_data.mpesa_pay) >
                parseFloat(this.total_order)
            ) {
                this.errorNotif(
                    "Mobile Money Payment cannot be greater than bill total"
                );
                return;
            }
            if (
                parseFloat(this.form_data.card_pay) >
                parseFloat(this.total_order)
            ) {
                this.errorNotif(
                    "Card Payment cannot be greater than bill total"
                );
                return;
            }
            if (
                parseFloat(this.form_data.credit_pay) >
                parseFloat(this.total_order)
            ) {
                this.errorNotif(
                    "Credit Payment cannot be greater than bill total"
                );
                return;
            }
            if (parseFloat(this.form_data.receipt_balance) > 0) {
                this.swr("Pending payments...");
                return;
            }
            if (!parseFloat(this.form_data.receipt_total) > 0) {
                this.swr("Empty receipt totals...");
                return;
            }
            if (
                parseFloat(this.form_data.card_pay) > 0 &&
                this.form_data.card_terminal_account_id == null
            ) {
                this.swr("Select card terminal");
                return;
            }
            this.showLoader();
            this.btn_loading = true;
            this.load_printer_component = true;
            var combined_data = Object.assign(this.form_data, {
                order_data_offline: this.order_data_offline,
            });
            const res = await this.callApi(
                "post",
                "data/pos_sale/exchangeWithOriginalReceiptAndTopUpPayment",
                combined_data
            );

            this.hideLoader();

            if (res.status === 200) {
                this.s("Successfully saved");
                this.form_data.receipt_no = res.data;
                this.form_data.order_no = res.data;
                this.order_data_offline.forEach((obj) => {
                    obj.order_no = this.form_data.receipt_no;
                });
                if (print_receipt) {
                    setTimeout(() => {
                        //disable browser printing
                        this.$refs.ReceiptPrintComponent.printReceipt();
                        this.$emit("dismiss-diag");
                    }, 1000);
                } else {
                    this.$emit("dismiss-diag");
                }
            } else {
                this.btn_loading = false;
                this.form_error(res);
            }
        },
    },
};
</script>
