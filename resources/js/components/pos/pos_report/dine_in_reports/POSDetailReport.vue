<template>
    <div class="row">
        <div class="row">
            <div class="col-10 p-4">
                <div>Customer: {{ form_data.company_name }}</div>
                <div>Order No: {{ form_data.order_no }}</div>
                <div>Location: {{ form_data.location }}</div>
                <div>Table: {{ form_data.table }}</div>
                <br />

                <div
                    class="d-flex flex-column mt-2 mb-2"
                    v-for="(value, i) in order_data"
                    :key="i"
                    @click="cartClck(value)"
                >
                    <div class="row">
                        <div class="col-5">
                            <item-particulars-component :data="value" />
                        </div>
                        <div class="col-2">
                            <b> X </b> <b> {{ value.qty }}</b>
                        </div>
                        <div class="col-1">
                            <b> {{ cashFormatter(value.row_total) }}</b>
                        </div>
                    </div>
                    <div class="d-flex justify-content-start">
                        <p class="mr-2">Unit Price /</p>

                        <p>{{ cashFormatter(value.price) }}</p>
                        <p class="ml-2">
                            Guest
                            <span class="badge badge-secondary">{{
                                value.no_guest
                            }}</span>
                        </p>
                        <p class="ml-2">
                            {{ value.notes }}
                        </p>
                    </div>
                    <hr />
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <span>
                        <b>
                            TOTAL:
                            {{ cashFormatter(form_data.receipt_total) }}</b
                        >
                    </span>
                </div>
                <div class="d-flex justify-content-end mt-1">
                    <span>
                        <b>
                            TAX:
                            {{ cashFormatter(this.form_data.total_tax) }}</b
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
                        v-if="isAdmin"
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
                        v-if="isAdmin"
                        @click="printReceipt()"
                    >
                        <v-icon medium>{{ icons.mdiPrinter }}</v-icon>
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>

        <BillPrint
            v-if="order_data.length > 0"
            :order_data="order_data"
            :bill_total="total_order"
            ref="printBillComponent"
        />
        <div class="row">
            <div class="col-9">
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

                    <div>
                        <table width="100%">
                            <tr v-if="order_data.length > 0">
                                <td>
                                    Served By :
                                    {{
                                        order_data[0].user
                                            ? order_data[0].user.name
                                            : ""
                                    }}
                                </td>
                            </tr>

                            <tr>
                                <td v-if="order_data[0]">
                                    receipt no :
                                    {{ order_data_details.receipt_no }}
                                </td>
                            </tr>
                            <tr>
                                <td v-if="order_data[0]">
                                    Location:
                                    {{ order_data[0].location.name }}
                                </td>
                            </tr>
                            <tr>
                                <td v-if="order_data[0]">
                                    Table :
                                    {{ order_data[0].table.name }}
                                </td>
                            </tr>
                        </table>

                        <b>REPRINT</b>
                        <receipt-body-component :data="order_data" />

                        <hr />

                        <table>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <p class="t-amount">
                                            TAX AMOUNT :
                                            {{
                                                cashFormatter(round(this.form_data.total_tax))
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
                                            {{ cashFormatter(this.form_data.receipt_total) }}
                                        </h3>
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.cash_pay > 0"
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
                                        v-if="form_data.mpesa_pay > 0"
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
                                        v-if="form_data.card_pay > 0"
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
                                    Thank you for dining with us please welcome
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
import BillPrint from "../../menu_components/orders/BillPrint.vue";
import ItemParticularsComponent from "../../menu_components/dinerscomponents/ItemParticularsComponent.vue";
import ReceiptHeader from '../../menu_components/dinerscomponents/ReceiptHeader.vue';
import ReceiptBodyComponent from '../../menu_components/dinerscomponents/ReceiptBodyComponent.vue';
export default {
    components: { BillPrint, ItemParticularsComponent, ReceiptHeader,ReceiptBodyComponent },
    props: ["order_data_details", ,
        "total_order", "total_tax"],
    data() {
        return {
            order_data: [],
            branchInfo: "",
            userInfo: "",
            form_data: {
                order_no: "",
                company_name: "",
                table: "",
                cash_pay: 0,
                card_pay: 0,
                mpesa_pay: 0,
                credit_pay: 0,
                recipt_total:0,
                total_tax:0
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
  
  this.form_data.receipt_total=this.order_data_details.receipt_total
  this.form_data.total_tax=this.order_data_details.total_vat
        this.form_data.cash_pay = this.order_data_details.cash_pay;
        this.form_data.mpesa_pay = this.order_data_details.mpesa_pay;
        this.form_data.card_pay = this.order_data_details.card_pay;
        this.form_data.credit_pay = this.order_data_details.credit_pay;
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
            const res = await this.getApi("data/pos_sale/getSaleDetails", {
                params: {
                    order_no: this.order_data_details.order_no,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
                (this.form_data.order_no = this.order_data[0].order_no),
                    (this.form_data.company_name = this.order_data[0].customer
                        ? this.order_data[0].customer.company_name
                        : "");
                this.form_data.table = this.order_data[0].table
                    ? this.order_data[0].table.name
                    : "";
                this.form_data.location = this.order_data[0].location
                    ? this.order_data[0].location.name
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
