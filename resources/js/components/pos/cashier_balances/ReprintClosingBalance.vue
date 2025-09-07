<template>
    <div id="printReceipt">
        <receipt-header />
        <table width="100%" class="head">
            <tr>
                <td>
                    <center>
                        <h3><b>REPRINT CASHIER SHIFT REPORT</b></h3>
                    </center>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>

        <table width="100%" class="mt-3">
            <tr>
                <td class="room-details">Cashier Name</td>
                <td class="room-details">
                    {{ details_data.user.name }}
                </td>
            </tr>

            <tr>
                <td class="room-details"><b>POINT</b></td>
                <td class="room-details">CASHIER POINT</td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>
        <table width="100%" class="mt-2">
            <tr>
                <td class="room-details">Shift Closing Time</td>
                <td class="room-details">
                    {{ formatDateTime(details_data.closing_time) }}
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Cash Sales Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(details_data.cash_sales_amount)
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Invoice Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(details_data.invoice_payments)
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Expenses Payments </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            -{{
                                cashFormatter(details_data.expense_payments)
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Mobile Money: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    details_data.mobile_money_sold_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Card Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(details_data.card_sold_amount)
                            }}</b
                        >
                    </div>
                </td>
            </tr>

            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>System Cashdrawer Amount: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(details_data.cash_sold_amount)
                            }}</b
                        >
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>CASH COUNTED AMOUNT (Closing) </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(details_data.closing_cash_amount)
                            }}</b
                        >
                    </div>
                </td>
            </tr>

            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>CASH OPENING AMOUNT </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(details_data.opening_amount) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>CASH COLLECTED AMOUNT </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    details_data.closing_cash_amount -
                                        details_data.opening_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b v-if="cash_deficit < 0">CASH SURPLUS: </b
                        ><b v-else>CASH DEFICIT:</b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    cash_deficit.toString().replace("-", "")
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>
        <table>
            <tr>
                <td>
                    <b>Printed By {{ userInfo.name }} </b>
                </td>
            </tr>
        </table>
        <br />
        <br /><br />
    </div>
</template>

<script>
import print from "print-js";
import ReceiptHeader from "../menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    components: {
        ReceiptHeader,
    },
    props: ["details_data"],
    data() {
        return {
            cash_deficit: 0,
        };
    },

    created() {
        this.cash_deficit =
            this.details_data.cash_sold_amount -
            (this.details_data.closing_cash_amount -
                this.details_data.opening_amount);
        this.userInfo = this.$store.state.user;
    },
    methods: {
        printReceipt: function () {
            printJS("printReceipt", "html");
            // this.value = value;
        },
    },
};
</script>
<style scoped>
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
