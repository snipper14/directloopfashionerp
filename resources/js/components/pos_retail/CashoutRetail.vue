<template>
    <v-app>
        <v-container>
            <v-row justify="center">
                <v-col cols="12">
                    <!-- Main Card -->
                    <v-card class="elevation-4">
                        <!-- Header with Gradient -->
                        <v-card-title
                            class="white--text text-h5 py-4 font-weight-bold"
                            style="
                                background: linear-gradient(
                                    90deg,
                                    #1434a4,
                                    #1e88e5
                                );
                            "
                        >
                            <v-icon left>mdi-cash-multiple</v-icon> Tender
                            Window
                        </v-card-title>

                        <!-- Card Content -->
                        <v-card-text>
                            <v-row>
                                <!-- Payments Section -->
                                <v-col md="6">
                                    <v-sheet
                                        elevation="2"
                                        class="pa-5 rounded-lg"
                                    >
                                        <h3
                                            class="text-center text-uppercase font-weight-bold mb-4 text-primary"
                                        >
                                            Payments
                                        </h3>
                                        <v-container>
                                            <v-row>
                                                <v-col>
                                                    <v-text-field
                                                        label="Cash Pay"
                                                        type="number"
                                                        v-model="
                                                            form_data.cash_pay
                                                        "
                                                        outlined
                                                        @focus="fillCashPay"
                                                        color="primary"
                                                        @keyup.enter="
                                                            printReceipt
                                                        "
                                                        ref="cashInput"
                                                        prepend-inner-icon="mdi-cash"
                                                        class="custom-input"
                                                    />
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col>
                                                    <v-text-field
                                                        label="M-Pesa"
                                                        type="number"
                                                        v-model="
                                                            form_data.mpesa_pay
                                                        "
                                                        outlined
                                                        @focus="fillMpesaPay"
                                                        color="success"
                                                        @keyup.enter="
                                                            printReceipt
                                                        "
                                                        ref="mpesaInput"
                                                        prepend-inner-icon="mdi-cellphone"
                                                        class="custom-input"
                                                    />
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col>
                                                    <treeselect
                                                        :load-options="
                                                            fetchCashierAccounts
                                                        "
                                                        :options="
                                                            terminal_select_data
                                                        "
                                                        :auto-load-root-options="
                                                            false
                                                        "
                                                        v-model="
                                                            form_data.card_terminal_account_id
                                                        "
                                                        :multiple="false"
                                                        :show-count="true"
                                                        placeholder="Select Terminal"
                                                        class="custom-input"
                                                    />
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col>
                                                    <v-text-field
                                                        label="Card"
                                                        type="number"
                                                        v-model="
                                                            form_data.card_pay
                                                        "
                                                        outlined
                                                        @focus="fillCardPay"
                                                        color="info"
                                                        @keyup.enter="
                                                            printReceipt
                                                        "
                                                        prepend-inner-icon="mdi-credit-card"
                                                        class="custom-input"
                                                    />
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col>
                                                    <v-text-field
                                                        label="Charge to Acc."
                                                        type="number"
                                                        v-model="
                                                            form_data.credit_pay
                                                        "
                                                        outlined
                                                        prepend-inner-icon="mdi-account-cash"
                                                        class="custom-input"
                                                    />
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col>
                                                    <v-text-field
                                                        label="Reference No"
                                                        type="text"
                                                        v-model="
                                                            form_data.payment_ref
                                                        "
                                                        outlined
                                                        prepend-inner-icon="mdi-tag"
                                                        class="custom-input"
                                                    />
                                                </v-col>
                                            </v-row>

                                            <v-row
                                                class="form-group d-flex flex-row"
                                            >
                                                <v-btn
                                                    @click="redeemPoint()"
                                                    :loading="
                                                        redeem_point_btn_loader
                                                    "
                                                    x-small
                                                    color="primary"
                                                    >Reedem Points</v-btn
                                                >
                                                <label for=""
                                                    >Points Reedemable Cash
                                                    Equiv.
                                                </label>
                                                <input
                                                    type="number"
                                                    v-model="
                                                        form_data.loyalty_points
                                                    "
                                                    placeholder="Loyalty Points"
                                                    class="form-control"
                                                />
                                            </v-row>
                                            <v-row>
                                                <v-col>
                                                    <v-col>
                                                        <v-btn
                                                            v-if="
                                                                branch.etims_type ==
                                                                'oscu'
                                                            "
                                                            color="info"
                                                            block
                                                            large
                                                            :loading="
                                                                btn_loading
                                                            "
                                                            @click="
                                                                printOnEtims
                                                            "
                                                        >
                                                            <v-icon left
                                                                >mdi-file-document</v-icon
                                                            >
                                                            Sale on E-TIMS </v-btn
                                                        ><br />
                                                        <v-btn
                                                            color="primary"
                                                            block
                                                            large
                                                            :loading="
                                                                btn_loading
                                                            "
                                                            @click="
                                                                printReceipt
                                                            "
                                                            v-shortkey.once="[
                                                                'f3',
                                                            ]"
                                                        >
                                                            <v-icon left
                                                                >mdi-printer</v-icon
                                                            >
                                                            Complete Sale
                                                        </v-btn>
                                                    </v-col>
                                                </v-col>
                                                <v-col>
                                                    <v-btn
                                                        v-if="
                                                            branch.etims_type ==
                                                            'vscu'
                                                        "
                                                        color="success"
                                                        block
                                                        large
                                                        :loading="btn_loading"
                                                        @click="
                                                            completeVSCUEtims
                                                        "
                                                    >
                                                        <v-icon left
                                                            >mdi-receipt"></v-icon
                                                        >
                                                        Sale on V-Etims
                                                    </v-btn>
                                                </v-col>
                                            </v-row>
                                        </v-container>
                                    </v-sheet>
                                </v-col>

                                <!-- Receipt Summary Section -->
                                <v-col md="6">
                                    <v-sheet
                                        elevation="2"
                                        class="pa-5 rounded-lg"
                                        style="
                                            background: linear-gradient(
                                                145deg,
                                                #b3e5fc,
                                                #81d4fa
                                            );
                                        "
                                    >
                                        <h3
                                            class="text-h6 font-weight-bold text-center text-primary mb-4"
                                        >
                                            Receipt Summary
                                        </h3>
                                        <v-divider class="mb-4"></v-divider>
                                        <v-alert
                                            type="info"
                                            outlined
                                            dense
                                            class="text-h5 font-weight-bold"
                                        >
                                            Receipt Total:
                                            {{
                                                cashFormatter(
                                                    form_data.receipt_total
                                                )
                                            }}
                                        </v-alert>
                                        <v-alert
                                            type="success"
                                            outlined
                                            dense
                                            class="text-h5 font-weight-bold"
                                        >
                                            Balance:
                                            {{
                                                cashFormatter(
                                                    form_data.receipt_balance
                                                )
                                            }}
                                        </v-alert>
                                        <v-alert
                                            type="info"
                                            outlined
                                            dense
                                            class="text-h6"
                                        >
                                            Tax Amount:
                                            {{
                                                cashFormatter(
                                                    round(computedVat)
                                                )
                                            }}
                                        </v-alert>
                                        <!-- promos -->
                                        <v-row>
                                            <v-col>
                                                <v-text-field
                                                    disabled
                                                    label="% Discount"
                                                    type="number"
                                                    v-model="
                                                        form_data.discount_percent
                                                    "
                                                    outlined
                                                    prepend-inner-icon="mdi-border-top"
                                                    class="custom-input"
                                                />
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col>
                                                <v-text-field
                                                    label=" Discount Amount"
                                                    type="number"
                                                    v-model="
                                                        form_data.discount_pay
                                                    "
                                                    :disabled="
                                                        b_setting.is_discount_allowed ===
                                                        'no'
                                                    "
                                                    outlined
                                                    prepend-inner-icon="mdi-tag"
                                                    class="custom-input"
                                                />
                                            </v-col>
                                        </v-row>
                                        <!-- end promos -->
                                        <!-- Breakdown Section -->
                                        <v-row v-if="form_data.cash_pay">
                                            <v-col>
                                                <p class="font-weight-bold">
                                                    Cash:
                                                    {{
                                                        cashFormatter(
                                                            round(
                                                                form_data.cash_pay
                                                            )
                                                        )
                                                    }}
                                                </p>
                                            </v-col>
                                        </v-row>
                                        <v-row v-if="form_data.mpesa_pay">
                                            <v-col>
                                                <p class="font-weight-bold">
                                                    M-Pesa:
                                                    {{
                                                        cashFormatter(
                                                            round(
                                                                form_data.mpesa_pay
                                                            )
                                                        )
                                                    }}
                                                </p>
                                            </v-col>
                                        </v-row>
                                        <v-row v-if="form_data.card_pay">
                                            <v-col>
                                                <p class="font-weight-bold">
                                                    Card:
                                                    {{
                                                        cashFormatter(
                                                            round(
                                                                form_data.card_pay
                                                            )
                                                        )
                                                    }}
                                                </p>
                                            </v-col>
                                        </v-row>
                                        <v-row v-if="form_data.credit_pay">
                                            <v-col>
                                                <p class="font-weight-bold">
                                                    Charge To Acc.
                                                    {{
                                                        cashFormatter(
                                                            round(
                                                                form_data.credit_pay
                                                            )
                                                        )
                                                    }}
                                                </p>
                                            </v-col>
                                        </v-row>
                                    </v-sheet>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Shortcuts Section -->
            <v-row class="mt-4">
                <v-col md="6">
                    <v-alert type="info" outlined>
                        <v-icon left>mdi-keyboard</v-icon>
                        <strong>Shortcut:</strong> Mpesa Payment (Ctrl + M)
                    </v-alert>
                </v-col>
                <v-col md="6">
                    <v-alert type="info" outlined>
                        <v-icon left>mdi-keyboard"></v-icon>
                        <strong>Shortcut:</strong> Cash Payment (Ctrl + C)
                    </v-alert>
                </v-col>
            </v-row>

            <!-- Receipt Printer Component -->
            <v-row>
                <v-col cols="4">
                    <PrintReceipt
                        ref="ReceiptPrintComponent"
                        v-if="load_printer_component"
                        :form_data="form_data"
                        :order_data="order_data_offline"
                        :etims_data="etims_data"
                        :etims_qrcode_data="etims_qrcode_data"
                    />
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>
<script>
import Swal from "sweetalert2/dist/sweetalert2.js";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import PrintReceipt from "../pos/menu_components/PrintReceipt.vue";

export default {
    props: ["order_data_offline", "total_order", "total_tax"],
    data() {
        return {
            redeem_point_btn_loader: false,
            fiscal_data: {
                cu_invoice_number: null,
                description: null,
                cu_serial_number: null,
                cu_timestamp: null,
                qr_link: null,
            },
            b_setting: this.$store.state.branch,
            branch: this.$store.state.branch,
            load_printer_component: false,
            btn_loading: false,
            etims_data: {},
            etims_qrcode_data: {},
            redeemable_points: 0,
            promo_data: null,
            isUpdatingDiscount: false,
   isSubmitting: false,
            form_data: {
                loyalty_points: 0,
                card_terminal_account_id: null,
                card_pay: 0,
                mpesa_pay: 0,
                cash_pay: 0,
                credit_pay: 0,
                discount_pay: 0,
                discount_percent: 0,
                payment_ref: "",
                order_no: "",
                receipt_no: "",
                receipt_balance: 0,
                paid_date: "",
                receipt_total: 0,
                total_vat: 0,

                order_date: "",
                customer_id: null,
                customer_name: "",
                customer_pin: "",
            },
        };
    },
    mounted() {
        this.promo_data = this.$store.state.promo_data;

        this.form_data.receipt_total = this.total_order;
        this.form_data.total_vat = this.total_tax;
        this.form_data.order_no = this.order_data_offline[0].order_no;
        this.form_data.receipt_no = this.order_data_offline[0].order_no;
        this.form_data.customer_id = this.order_data_offline[0].customer_id;
        this.form_data.customer_name = this.order_data_offline[0].customer_name;
        this.form_data.customer_pin = this.order_data_offline[0].customer_pin;
        if (this.promo_data.length > 0) {
            for (let range of this.promo_data) {
                if (
                    range.status === "active" && // Only consider active ranges
                    this.form_data.receipt_total >= range.lower_limit &&
                    this.form_data.receipt_total <= range.upper_limit
                ) {
                    this.form_data.discount_percent = range.discount;
                    break; // Stop checking once a match is found
                } else {
                }
            }
        }
    },
    watch: {
        "form_data.discount_percent"(newValue) {
            if (this.isUpdatingDiscount || newValue < 0) return;
            this.isUpdatingDiscount = true;

            // this.calculateDiscount();

            this.isUpdatingDiscount = false;
        },
        "form_data.discount_pay"(newValue) {
            if (this.isUpdatingDiscount || newValue < 0) return;
            this.isUpdatingDiscount = true;
            this.isDirectDiscount = true;
            this.calculateDirectDiscount();
            this.isUpdatingDiscount = false;
        },
        form_data: {
            handler() {
                this.calculateBalances();
            },
            deep: true,
        },
    },
    components: { Treeselect, PrintReceipt, Swal },
    methods: {
        generateUUID() {
            return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
                /[xy]/g,
                function (c) {
                    const r = (Math.random() * 16) | 0;
                    const v = c === "x" ? r : (r & 0x3) | 0x8;
                    return v.toString(16);
                }
            );
        },
        async redeemPoint() {
            if (this.form_data.customer_id) {
                this.redeem_point_btn_loader = true;
                const res = await this.callApi(
                    "post",
                    "data/customers/getRedeemablePoints",
                    { customer_id: this.form_data.customer_id }
                );
                this.redeem_point_btn_loader = false;
                if (res.status === 200) {
                    this.form_data.loyalty_points = Math.trunc(res.data);
                    this.redeemable_points = Math.trunc(res.data);
                }
            } else {
                this.errorNotif("Select customer");
            }
        },
        calculateDiscount() {
            // Calculate discount_pay based on discount_percent
            const originalTotal =
                this.form_data.original_receipt_total ||
                this.form_data.receipt_total;

            // Calculate discount_pay based on discount_percent
            const discount =
                (originalTotal * this.form_data.discount_percent) / 100;

            // Update discount_pay and calculate the new receipt_total
            this.form_data.discount_pay = discount;
            this.form_data.receipt_total = originalTotal - discount;

            // Optionally, store the original receipt total if it is not already set
            if (!this.form_data.original_receipt_total) {
                this.form_data.original_receipt_total = originalTotal;
            }
        },
        calculateDirectDiscount() {
            let originalTotal = this.form_data.original_receipt_total;

            // Store the original total if not already set
            if (!originalTotal) {
                originalTotal = this.form_data.receipt_total;
                this.form_data.original_receipt_total = originalTotal;
            }

            const discountPay = parseFloat(this.form_data.discount_pay) || 0;

            // Prevent division by zero
            if (!originalTotal || isNaN(discountPay)) return;

            // Calculate discount percent and update form
            this.form_data.discount_percent = (
                (discountPay * 100) /
                originalTotal
            ).toFixed(2);
            this.form_data.receipt_total = originalTotal - discountPay;
        },
        fillCashPay() {
            // Set cash_pay to the value of receipt_balance
            this.form_data.cash_pay = this.form_data.receipt_balance;
        },
        fillMpesaPay() {
            // Set cash_pay to the value of receipt_balance
            this.form_data.mpesa_pay = this.form_data.receipt_balance;
        },
        fillCardPay() {
            this.form_data.card_pay = this.form_data.receipt_balance;
        },
        focusMpesaInput() {
            this.$refs.mpesaInput.focus();
        },
        focusCashInput() {
            this.$refs.cashInput.focus();
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
            if (this.form_data.credit_pay == "") {
                this.form_data.credit_pay = 0;
            }
            if (this.form_data.loyalty_points == "") {
                this.form_data.loyalty_points = 0;
            }
            var cash = parseFloat(this.form_data.cash_pay);
            var card = parseFloat(this.form_data.card_pay);
            var mpesa = parseFloat(this.form_data.mpesa_pay);
            var credit = parseFloat(this.form_data.credit_pay);
            var redeemable_points = parseFloat(this.form_data.loyalty_points);
            var total = parseFloat(this.form_data.receipt_total);
            if (redeemable_points > this.redeemable_points) {
                this.errorNotif("You can't surpass redeemable points");
                return;
            }
            this.form_data.receipt_balance =
                total - (cash + card + mpesa + credit + redeemable_points);
        },
        async validateAndCompleteEtimsSale() {
            this.btn_loading = true;
            let combined_data = Object.assign(
                {},
                this.etims_data,
                this.form_data,
                {
                    order_data_offline: this.order_data_offline,
                }
            );

            const res = await this.callApi(
                "post",
                "data/pos_sale/validateAndCompleteEtimsSale",
                combined_data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.$store.commit("setPromoData", []);
                if (res.data.queue_status == "completed") {
                    this.s("sale validated on etims");

                    this.etims_qrcode_data = res.data;

                    this.form_data.receipt_no =
                        this.etims_qrcode_data.trader_invoice_number;
                    this.form_data.order_no =
                        this.etims_qrcode_data.trader_invoice_number;
                    this.order_data_offline.forEach((obj) => {
                        obj.order_no = this.form_data.receipt_no;
                    });
                    this.s("Successfully saved");
                    if (this.branch.print_type == "dialogue") {
                        this.load_printer_component = true;
                        setTimeout(() => {
                            this.$refs.ReceiptPrintComponent.printReceipt();
                            this.$emit("dismiss-diag");
                        }, 1000);
                    }
                } else if (res.data.queue_status == "failed") {
                    this.errorNotifN(
                        "Transaction Failed on etims please try again later"
                    );
                } else {
                    //DIGITAX DID NOT COMPLETE
                    this.s("Successfully saved");
                    this.load_printer_component = true;
                    console.log(">>>>>" + JSON.stringify(res.data));

                    this.form_data.receipt_no = res.data.trader_invoice_number;
                    this.form_data.order_no = res.data.trader_invoice_number;
                    this.order_data_offline.forEach((obj) => {
                        obj.order_no = this.form_data.receipt_no;
                    });

                    if (this.branch.print_type == "dialogue") {
                        setTimeout(() => {
                            //disable browser printing

                            this.$refs.ReceiptPrintComponent.printReceipt();
                            this.$emit("dismiss-diag");
                        }, 1000);
                    } else {
                        this.$emit("dismiss-diag");
                    }
                 
                }
            } else {
                this.errorNotif("Error occured");
            }
        },
        async completeVSCUEtims() {

              if (this.isSubmitting) return; // Prevent re-entry
    this.isSubmitting = true;
            this.form_data.external_ref = this.generateUUID();
            this.form_data.paid_date = this.getCurrentDate();
            this.form_data.total_vat = this.computedVat;
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
            if (this.form_data.loyalty_points > this.redeemable_points) {
                this.errorNotif("You can't surpass redeemable points");
                return;
            }
            if (
                parseFloat(this.form_data.card_pay) > 0 &&
                this.form_data.card_terminal_account_id == null
            ) {
                this.swr("Select card terminal");
                return;
            }
            if (!parseFloat(this.form_data.receipt_total) > 0) {
                this.swr("Empty receipt totals...");
                return;
            }

            this.btn_loading = true;
            this.load_printer_component = true;
            this.form_data.fiscal_type = "is_etims";

            var combined_data = Object.assign(this.form_data, {
                order_data_offline: this.order_data_offline,
            });
            var mearged_data = Object.assign(combined_data, this.fiscal_data);

            const res = await this.callApi(
                "post",
                "data/pos_sale/vscuEtimsSale",
                mearged_data
            );

            this.btn_loading = false;
            if (res.status === 200) {
                this.$store.commit("setPromoData", []);
                this.s("Successfully saved");
                this.form_data.receipt_no = res.data;
                this.form_data.order_no = res.data;
                this.order_data_offline.forEach((obj) => {
                    obj.order_no = this.form_data.receipt_no;
                });

                // if (print_receipt) {
                //   setTimeout(() => {
                //disable browser printing
                //this.$refs.ReceiptPrintComponent.printReceipt();
                this.$emit("dismiss-diag");
                // }, 1000);
                localStorage.setItem("customer_pin", "");
                // } else {
                //     this.$emit("dismiss-diag");
                // }
            } else if (res.status === 400) {
                this.errorNotifN(res.data.error);
            } else {
                this.btn2_loading = false;
                this.form_error(res);
            }
        },
        async printOnEtims() {
              if (this.isSubmitting) return; // Prevent re-entry
    this.isSubmitting = true;
            const isValid = this.order_data_offline.every(
                (item) => item.item_id !== null && item.item_id !== undefined
            );

            this.form_data.paid_date = this.getCurrentDate();
            this.form_data.total_vat = this.computedVat;
this.form_data.external_ref = this.generateUUID();
            if (
                parseFloat(this.form_data.mpesa_pay) >
                parseFloat(this.total_order)
            ) {
                this.errorNotif(
                    "Mobile Money Payment cannot be greater than bill total"
                );
                return;
            }
            if (this.form_data.loyalty_points > this.redeemable_points) {
                this.errorNotif("You can't surpass redeemable points");
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
            this.showLoader();
            this.btn_loading = true;

            var combined_data = Object.assign(this.form_data, {
                order_data_offline: this.order_data_offline,
            });
            const res = await this.callApi(
                "post",
                "data/pos_sale/etimsCompleteSale",
                combined_data
            );

            this.hideLoader();
            ///     this.btn_loading = false;
            console.log("code" + res.status);
            console.log("records" + JSON.stringify(res.data));

            this.etims_data = res.data;
            if (res.status === 201) {
                setTimeout(() => {
                    this.validateAndCompleteEtimsSale();
                }, 2000);

                return;
            } else if (res.status == 409) {
                this.errorNotifN(res.data[0].message);
            } else {
                this.btn_loading = false;
                if (res.status == 400) {
                    this.errorNotifN(res.data[0].message);
                } else {
                    this.form_error(res);
                }
            }
        },
        async printReceipt() {
              if (this.isSubmitting) return; // Prevent re-entry
    this.isSubmitting = true;  
            this.form_data.external_ref = this.generateUUID();
            this.form_data.paid_date = this.getCurrentDate();
            this.form_data.total_vat = this.computedVat;
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
            if (this.form_data.loyalty_points > this.redeemable_points) {
                this.errorNotif("You can't surpass redeemable points");
                return;
            }
            this.showLoader();
            this.btn_loading = true;
            this.load_printer_component = true;
            // var combined_data = Object.assign(this.form_data, {
            //     order_data_offline: this.order_data_offline,
            // });
            let combined_data = Object.assign(
                {},
                this.etims_data,
                this.form_data,
                {
                    order_data_offline: this.order_data_offline,
                }
            );
            const res = await this.callApi(
                "post",
                "data/pos_sale/create",
                combined_data
            );

            this.hideLoader();

            if (res.status === 200) {
                this.$store.commit("setPromoData", []);
                this.s("Successfully saved");
                if (this.branch.print_type == "offline") {
                    const assignedJson = Object.assign(
                        {},
                        { order_info: res.data.res_details },
                        { res_items: res.data.res_total }
                    );
                    this.testPrint(assignedJson);
                }
                this.form_data.receipt_no = res.data;
                this.form_data.order_no = res.data;
                this.order_data_offline.forEach((obj) => {
                    obj.order_no = this.form_data.receipt_no;
                });

                if (this.branch.print_type == "dialogue") {
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
        async testPrint(combined_data) {
            //const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            //var url="http://127.0.0.1:5000/api/testPrint";
            var url = "http://localhost:8030/api/testPrint";
            try {
                const response = await axios.post(url, combined_data, {
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        //   "X-CSRF-TOKEN": csrfToken,
                    },
                });
            } catch (error) {
                console.log(JSON.stringify(error));
            }
            // console.log("reached");

            // this.showLoader();
            // const res = await this.callApi(
            //     "post",
            //     "http://localhost:5000/api/testPrint",
            //     combined_data
            // );
            // this.hideLoader();
        },
    },
    computed: {
        computedVat() {
            const baseVat = parseFloat(this.total_tax || 0);
            const discountVat = this.calculateTax(
                16,
                parseFloat(this.form_data.discount_pay) || 0
            );
            return baseVat - discountVat;
        },
    },
};
</script>
<style scoped>
.custom-input {
    margin-bottom: 2px !important; /* Reduces space between input fields */
    font-family: "Poppins", sans-serif; /* Set a custom font family */
    font-weight: 600; /* Make the font bolder */
}

/* If you want to reduce the line height specifically between inputs */
.v-text-field .v-input__control {
    line-height: 0.2 !important; /* Adjust line height as needed */
}
</style>
