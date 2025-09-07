<template>
    <div class="row">
        <div class="row">
            <div class="col-10 p-4">
                <div>Guest: {{ form_data.guest }}</div>
                <div>Order No: {{ form_data.order_no }}</div>
                <div>Room: {{ form_data.room }}</div>
                <div>Package: {{ form_data.package }}</div>
                <br />
                <order-details-without-total :data="order_data" />

                <div class="d-flex justify-content-end mt-2">
                    <span>
                        <b>
                            TOTAL:
                            {{ cashFormatter(this.form_data.receipt_total) }}</b
                        >
                    </span>
                </div>
                <div class="d-flex justify-content-end mt-1">
                    <span>
                        <b>
                            TAX:
                            {{ cashFormatter(this.form_data.total_vat) }}</b
                        >
                    </span>
                </div>

                <div
                    v-if="form_data.cash_pay > 0"
                    class="d-flex justify-content-end mt-1"
                >
                    <span>
                        <b>
                            CASH PAY:
                            {{ cashFormatter(form_data.cash_pay) }}</b
                        >
                    </span>
                </div>
                <div
                    v-if="form_data.mpesa_pay > 0"
                    class="d-flex justify-content-end mt-1"
                >
                    <span>
                        <b>
                            MPESA PAY:
                            {{ cashFormatter(form_data.mpesa_pay) }}</b
                        >
                    </span>
                </div>
                <div class="d-flex justify-content-end mt-1">
                    <button
                        class="btn btn-outline-primary btn-lg"
                        style="width: 100%"
                        v-if="isDownloadPermitted"
                        @click="printBill()"
                    >
                        <v-icon medium>{{ icons.mdiPrinter }}</v-icon>
                        Print Bill
                    </button>
                </div>
                <div class="d-flex justify-content-end mt-1">
                    <button
                        class="btn btn-outline-secondary btn-lg"
                        style="width: 100%"
                        v-if="isDownloadPermitted"
                        @click="printReceipt()"
                    >
                        <v-icon medium>{{ icons.mdiPrinter }}</v-icon>
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>

        <RoomServiceBillPrint
            v-if="order_data.length > 0"
            :order_data="order_data"
            :bill_total="total_order"
            :tax_total="total_tax"
            :sub_total="sub_total"
            :service_charge_total="service_charge_total"
            ref="printBillComponent"
        />
        <div class="row">
            <div class="col-10">
                <div id="printReceipt">
                    <receipt-header />
                    <table width="100%" class="head">
                        <tr>
                            <td
                                class="printer-header"
                                style="font-size: 2.4rem"
                            >
                                <center>
                                    <h3><b>Sales Receipt</b></h3>
                                </center>
                            </td>
                        </tr>
                    </table>
                    <b>REPRINT</b>
                    <div>
                        <table width="100%">
                            <tr>
                                <td>
                                    Served By :
                                    {{ order_data[0].user.name }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Guest :
                                    {{ order_data[0].guest.name }}
                                </td>
                            </tr>

                            <tr>
                                <td v-if="order_data[0]">
                                    receipt no :
                                    {{ form_data.receipt_no }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Room Package :
                                    {{ order_data[0].room_package.name }}
                                </td>
                            </tr>
                            <tr v-if="order_data[0].room_package.details">
                                <td>
                                    Room Package Details :
                                    {{ order_data[0].room_package.details }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Room Name :
                                    {{ order_data[0].room.door_name }}
                                </td>
                            </tr>
                        </table>
                        <receipt-body-component :data="order_data" />

                        <hr />

                        <table>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <p class="t-amount">
                                            TAX AMOUNT :
                                            {{
                                                cashFormatter(
                                                    round(form_data.total_vat)
                                                )
                                            }}
                                        </p>
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <p class="t-amount">
                                            SERVICE CHARGE ({{
                                                service_charge_rate
                                            }}%):
                                            {{
                                                cashFormatter(
                                                    round(service_charge_total)
                                                )
                                            }}
                                        </p>
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <h3 class="t-amount">
                                            RECEIPT TOTAL:
                                            {{
                                                cashFormatter(
                                                    form_data.receipt_total
                                                )
                                            }}
                                        </h3>
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.cash_pay.length > 0"
                                    >
                                        CASH:
                                        {{
                                            cashFormatter(
                                                round(form_data.cash_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.mpesa_pay.length > 0"
                                    >
                                        M-PESA:
                                        {{
                                            cashFormatter(
                                                round(form_data.mpesa_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.card_pay.length > 0"
                                    >
                                        Card:
                                        {{
                                            cashFormatter(
                                                round(form_data.card_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                ================================================
                            </tr>
                            <tr>
                                <td>
                                    Thank you for trusting in us please welcome
                                </td>
                            </tr>
                            <tr>
                                <td>Cashier : {{ userInfo.name }}</td>
                            </tr>
                            <tr>
                                ================================================
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {
    mdiBackburger,
    mdiNotebookEdit,
    mdiArrowRightBold,
    mdiPrinter,
    mdiArrowSplitVertical,
    mdiBookmarkOutline,
    mdiTrashCanOutline,
    mdiPlusThick,
    mdiMinusThick,
    mdiHome,
    mdiCashPlus,
    mdiAccountPlus,
} from "@mdi/js";
import BillPrint from "../menu_components/orders/BillPrint.vue";
import RoomServiceBillPrint from "../room_service/RoomServiceBillPrint.vue";
import OrderDetailsWithoutTotal from "../menu_components/dinerscomponents/OrderDetailsWithoutTotal.vue";
import ReceiptHeader from "../menu_components/dinerscomponents/ReceiptHeader.vue";
import ReceiptBodyComponent from "../menu_components/dinerscomponents/ReceiptBodyComponent.vue";
export default {
    components: {
        BillPrint,
        RoomServiceBillPrint,
        OrderDetailsWithoutTotal,
        ReceiptHeader,
        ReceiptBodyComponent,
    },
    props: ["order_data_details", "total_order", "total_tax"],
    data() {
        return {
            order_data: [],
            branchInfo: "",
            userInfo: "",
            sub_total: 0,
            service_charge_total: 0,
            form_data: {
                order_no: "",
                guest: "",
                room: "",
                cash_pay: 0,
                card_pay: 0,
                mpesa_pay: 0,
                credit_pay: 0,
                package: "",
                total_vat: 0,
                receipt_total: 0,
            },

            icons: {
                mdiBackburger,
                mdiNotebookEdit,
                mdiArrowRightBold,
                mdiPrinter,
                mdiArrowSplitVertical,
                mdiBookmarkOutline,
                mdiTrashCanOutline,
                mdiPlusThick,
                mdiMinusThick,
                mdiHome,
                mdiCashPlus,
                mdiAccountPlus,
            },
        };
    },

    mounted() {
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;

        this.form_data.cash_pay = this.order_data_details.cash_pay;
        this.form_data.mpesa_pay = this.order_data_details.mpesa_pay;
        this.form_data.card_pay = this.order_data_details.card_pay;
        this.form_data.credit_pay = this.order_data_details.credit;
        this.service_charge_total =
            this.order_data_details.service_charge_total;
        this.form_data.total_vat = this.order_data_details.total_vat;
        this.form_data.receipt_total = this.order_data_details.receipt_total;
        this.fetchData();
    },
    methods: {
        printReceipt() {
            printJS("printReceipt", "html");
        },
        printBill() {
            this.$refs.printBillComponent.printBill();
        },
        async fetchData() {
            this.showLoader();
            const res = await this.getApi("data/room_sale/getSaleDetails", {
                params: {
                    order_no: this.order_data_details.order_no,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;

                (this.form_data.order_no = this.order_data[0].order_no),
                    (this.form_data.guest = this.order_data[0].guest
                        ? this.order_data[0].guest.name
                        : "");
                this.form_data.table = this.order_data[0].table
                    ? this.order_data[0].table.name
                    : "";
                this.form_data.room = this.order_data[0].room
                    ? this.order_data[0].room.door_name
                    : "";
                this.form_data.package = this.order_data[0].room_package
                    ? this.order_data[0].room_package.name
                    : "";
            } else {
                this.servo();
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
