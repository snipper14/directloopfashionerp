<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4><b>Cashout / Payment</b></h4>

                        <div class="  ">
                            <span>
                                <h3>
                                    <b>
                                        BILL TOTAL:
                                        {{ cashFormatter(total_order) }}</b
                                    >
                                </h3>
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div
                                    style="
                                        height: 300px;
                                        overflow-x: hidden;
                                        overflow-y: auto;
                                    "
                                >
                                    <order-details-without-total
                                        :data="order_data"
                                    />

                                    <div
                                        class="d-flex justify-content-end mt-2"
                                    >
                                        <span>
                                            <b>
                                                TOTAL:
                                                {{
                                                    cashFormatter(total_order)
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                    <div
                                        class="d-flex justify-content-end mt-1"
                                    >
                                        <span>
                                            <b>
                                                TAX:
                                                {{
                                                    cashFormatter(total_tax)
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                    <div
                                        class="d-flex justify-content-end mt-1"
                                    >
                                        <span>
                                            <b>
                                                Service Charge:
                                                {{
                                                    cashFormatter(
                                                        service_charge_total
                                                    )
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6" style="background: #d7ccc8">
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
                                            <label for="">ref No</label>
                                            <input
                                                type="text"
                                                v-model="form_data.payment_ref"
                                                placeholder="Reference No"
                                                class="form-control"
                                            />
                                        </div>
                                        <div
                                            class="d-flex flex-row justify-content-between"
                                        >
                                            <button
                                                v-if="isDownloadPermitted"
                                                class="btn btn-secondary"
                                                @click="printReceipt(true)"
                                            >
                                                SAVE & PRINT
                                            </button>
                                            <button
                                                v-if="isAdmin"
                                                class="btn btn-warning"
                                                @click="add_cust_modal = true"
                                            >
                                                Clear on Credit
                                            </button>
                                            <button
                                                class="btn btn-primary"
                                                @click="printReceipt(false)"
                                            >
                                                SAVE
                                            </button>
                                        </div>
                                    </div>
                                </fieldset>
                                <div
                                    class="col-md-11"
                                    style="background: #827717"
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
                                    style="background: #827717"
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
                                    style="background: #827717"
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
                                    style="background: #827717"
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-4">
                <PrintRoomServiceReceipt
                    ref="PrintRoomServiceReceipt"
                    v-if="load_printer_component"
                    :form_data="form_data"
                    :service_charge_rate="service_charge_rate"
                    :order_data="order_data"
                    :waiter="waiter"
                />
            </div>
        </div>
        <Modal v-model="add_cust_modal" width="800" title="Add Cust">
            <add-customer-to-order
                @customerSearchResult="customerSearchResult"
            />
        </Modal>
        <!-- <PrintReceipt :order_data="order_data" ref="printReceipt" /> -->
    </div>
</template>

<script>
import AddCustomerToOrder from "../menu_components/AddCustomerToOrder.vue";
import OrderDetailsWithoutTotal from "../menu_components/dinerscomponents/OrderDetailsWithoutTotal.vue";
import ReceiptBodyComponent from "../menu_components/dinerscomponents/ReceiptBodyComponent.vue";
import ReceiptHeader from "../menu_components/dinerscomponents/ReceiptHeader.vue";
import PrintRoomReserviceReceipt from "./PrintRoomReserviceReceipt.vue";
import PrintRoomServiceReceipt from "./room_service_components/PrintRoomServiceReceipt.vue";
export default {
    props: [
        "order_no",
        "order_data",
        "total_order",
        "total_tax",
        "service_charge_total",
        "service_charge_rate",
    ],
    data() {
        return {
            add_cust_modal: false,
            branchInfo: "",
            userInfo: "",
            waiter: "",
            bill_total: 0,
            tax_total: 0,
            load_printer_component: false,
            data: [],
            form_data: {
                service_charge_total: 0,
                customer_id: null,
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
                guest_id: null,
                user_id: null,
                order_date: "",
                order_date: "",
                order_type: "",
                qty: 0,
            },
        };
    },
    components: {
        PrintRoomReserviceReceipt,
        AddCustomerToOrder,
        OrderDetailsWithoutTotal,
        ReceiptHeader,
        ReceiptBodyComponent,
        PrintRoomServiceReceipt,
    },
    mounted() {
        this.form_data.order_no = this.order_no;
        this.form_data.receipt_total = this.total_order;
        this.form_data.total_vat = this.total_tax;
        this.form_data.service_charge_total = this.service_charge_total;
        this.form_data.user_id = this.order_data[0].user_id;
        this.form_data.order_date = this.order_data[0].order_date;
        this.form_data.order_type = this.order_data[0].order_type;
        this.form_data.guest_id = this.order_data[0].guest_id;

        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.data = this.order_data;
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
        async customerSearchResult(value) {
            this.form_data.customer_id = value.id;
            this.add_cust_modal = false;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_sale/completeCreditSale",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Transaction completed ");

                this.$emit("dismiss-diag");
            } else {
                this.form_error(res);
            }
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
            this.form_data.receipt_no = this.order_no; // this.getUniqueId();
        },
        async printReceipt(print_receipt) {
            this.generateReceiptNo();

            this.waiter = this.order_data[0].user.name;
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
            if (parseFloat(this.form_data.receipt_balance) > 0) {
                this.swr("Pending payments...");
                return;
            }
            if (!parseFloat(this.form_data.receipt_total) > 0) {
                this.swr("Empty receipt totals...");
                return;
            }
            this.showLoader();
            this.load_printer_component = true;
            const res = await this.callApi(
                "post",
                "data/room_sale/create",
                this.form_data
            );

            this.hideLoader();

            if (res.status === 200) {
                this.s("Successfully saved");
                if (print_receipt) {
                    // printJS("printMe", "html");
                    //    this.$refs.printReceipt.printCashier();
                    setTimeout(() => {
                        this.$refs.PrintRoomServiceReceipt.printReceipt();
                        this.$emit("dismiss-diag");
                    }, 1000);
                } else {
                    this.$emit("dismiss-diag");
                }

                // this.order_data = [];
                // this.filtered_data = [];
                // this.data = null;
                // Object.keys(this.form_data).forEach(
                //     (key) => (this.form_data[key] = 0)
                // );

                //
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
<style scoped>
.border {
    padding: 2rem !important;
}
.t-amount {
    color: #fff;
}
.receipt_header {
    display: flex;
    flex-direction: column;
}
.receipt-menu td {
    color: #000;
    font-weight: 700;
}
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
#printReceipt {
    visibility: hidden;
}
.printer-header {
    text-align: center;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
}
.t-amount {
    color: #000;
    font-weight: 600;
}
</style>
