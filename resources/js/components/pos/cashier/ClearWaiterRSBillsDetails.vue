<template>
    <div class="row">
        <div class="row">
            <div class="col-10 p-4">
                <div>Room: {{ order_data[0].room.door_name }}</div>
                  <div>Guest: {{ order_data[0].guest.name }}</div>
                <div>Order No: {{ form_data.order_no }}</div>

                <br />
                  <order-details-without-total
                  :data="order_data"
                  />
               
                <div class="d-flex justify-content-end mt-2">
                    <span>
                        <b>
                            TOTAL:
                            {{ cashFormatter(total_order) }}</b
                        >
                    </span>
                </div>
                <div class="d-flex justify-content-end mt-1">
                    <span>
                        <b>
                            TAX:
                            {{ cashFormatter(total_tax) }}</b
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
                        class="btn btn-outline-secondary btn-lg"
                        style="width: 100%"
                        v-if="isDownloadPermitted"
                        @click="printReceipt()"
                    >
                        <v-icon medium>{{ icons.mdiPrinter }}</v-icon>
                        Clear Sales
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-9">
                <div id="printReceipt">
                    <receipt-header/>
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
                            <tr>
                                 <div>Room: {{ order_data[0].room.door_name }}</div>
                  
                            </tr>
                            <tr>
                                <div>Guest: {{ order_data[0].guest.name }}</div>
                            </tr>
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
                                    {{ sales_data_details.receipt_no }}
                                </td>
                            </tr>
                        </table>
                       <receipt-body-component
                       :data="order_data"
                       />
                      
                        <hr />

                        <table>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <p class="t-amount">
                                            TAX AMOUNT :
                                            {{
                                                cashFormatter(round(total_tax))
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
                                            {{ cashFormatter(total_order) }}
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
import BillPrint from "../menu_components/orders/BillPrint.vue";
import OrderDetailsWithoutTotal from '../menu_components/dinerscomponents/OrderDetailsWithoutTotal.vue';
import ReceiptHeader from '../menu_components/dinerscomponents/ReceiptHeader.vue';
import ReceiptBodyComponent from '../menu_components/dinerscomponents/ReceiptBodyComponent.vue';
export default {
    components: { BillPrint, OrderDetailsWithoutTotal, ReceiptHeader,ReceiptBodyComponent },
    props: ["sales_data_details"],
        
    data() {
        return {
            order_data: [],
            branchInfo: "",
            userInfo: "",
            total_order: 0,
            total_tax: 0,
            form_data: {
                order_no: "",
                company_name: "",
                table: "",
                cash_pay: 0,
                card_pay: 0,
                mpesa_pay: 0,
                credit_pay: 0,
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
        this.total_order = this.sales_data_details.receipt_total;
        this.total_tax = this.sales_data_details.total_vat;
        this.form_data.cash_pay = this.sales_data_details.cash_pay;
        this.form_data.mpesa_pay = this.sales_data_details.mpesa_pay;
        this.form_data.card_pay = this.sales_data_details.card_pay;
        this.form_data.credit_pay = this.sales_data_details.credit_pay;

        this.form_data.order_no = this.sales_data_details.order_no;
        this.fetchData();
    },
    methods: {
        async printReceipt() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_sale/clearWaiterSales",
                {
                    order_no: this.form_data.order_no,
                }
            );

            this.hideLoader();
            if (res.status == 200) {
                this.s("cleared successfully");
                printJS("printReceipt", "html");
                this.$emit("cleared-bill");
            } else {
                this.servo();
            }
        },

        async fetchData() {
            this.showLoader();
            const res = await this.getApi("data/room_sale/getSaleDetails", {
                params: {
                    order_no: this.sales_data_details.order_no,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
                console.log(JSON.stringify(this.order_data));
                (this.form_data.order_no = this.order_data[0].order_no),
                    (this.form_data.company_name = this.order_data[0].customer
                        ? this.order_data[0].customer.company_name
                        : "");
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
