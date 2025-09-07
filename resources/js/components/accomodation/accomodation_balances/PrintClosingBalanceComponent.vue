<template>
    <div id="print_receipt">
        <receipt-header />
        <table width="100%" class="head">
            <tr>
                <td>
                    <center>
                        <h3><b> RECEPTION SHIFT REPORT</b></h3>
                    </center>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td>
                    <b> ========================================= </b>
                </td>
            </tr>
        </table>
        <table width="100%" class="mt-3">
            <tr>
                <td class="room-details">Receptionist:</td>
                <td class="room-details">
                    {{ $store.state.user.name }}
                </td>
            </tr>

            <tr>
                <td class="room-details"><b>POINT</b></td>
                <td class="room-details">RECEPTION</td>
            </tr>
        </table>

        <table>
            <tr>
                <td>
                    <b> ========================================= </b>
                </td>
            </tr>
        </table>

        <table width="100%" class="mt-2">
           

            <tr>
                <td class="room-details">Shift Closing Time</td>
                <td class="room-details">
                    {{ formatDateTime(this.form_data.closing_time) }}
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td>
                    <b> ========================================= </b>
                </td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> Cash Sales</b>
                    </div>
                    <div style="width: 20%">
                        <b>
                            {{ cashFormatter(form_data.cash_sold_amount) }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> Mobile Money</b>
                    </div>
                    <div style="width: 20%">
                        <b>
                            {{
                                cashFormatter(
                                    form_data.mobile_money_sold_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> Card Payments</b>
                    </div>
                    <div style="width: 20%">
                        <b>
                            {{ cashFormatter(form_data.card_sold_amount) }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> Bank Transfer Payments</b>
                    </div>
                    <div style="width: 20%">
                        <b>
                            {{
                                cashFormatter(
                                    form_data.bank_transfer_sold_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
             <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> Cheque Payments</b>
                    </div>
                    <div style="width: 20%">
                        <b>
                            {{
                                cashFormatter(
                                    form_data.cheque_sold_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> Online Payments</b>
                    </div>
                    <div style="width: 20%">
                        <b>
                            {{
                                cashFormatter(
                                    form_data.online_sold_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> TOTAL SALES </b>
                    </div>
                    <div style="width: 20%">
                        <b> {{ cashFormatter(form_data.sold_amount) }}</b>
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td>
                    <b> ========================================= </b>
                </td>
            </tr>
        </table>
        <table width="100%">
           
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> SUBMISSION AMOUNT </b>
                    </div>
                    <div style="width: 20%">
                        <b> {{ cashFormatter(form_data.closing_amount) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 20%">
                        <b> DEFICIT </b>
                    </div>
                    <div style="width: 20%">
                        <b>
                            {{
                                cashFormatter(
                                    form_data.closing_amount -
                                        form_data.sold_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td>
                    <b> ========================================= </b>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td>
                    <b>Printed By {{ userInfo.name }} </b>
                </td>
            </tr>
        </table>

        <br /><br /><br />
    </div>
</template>

<script>
import print from "print-js";
import ReceiptHeader from "../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    components: {
        ReceiptHeader,
    },
    props: ["form_data", "balance_data"],
    data() {
        return {};
    },

    created() {
        this.userInfo = this.$store.state.user;
    },
    methods: {
        printCloseShift: function () {
            console.log(">>>>>>>");
            printJS("print_receipt", "html");
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
#print_receipt {
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
