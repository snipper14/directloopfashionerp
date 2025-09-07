<template>
    <div id="printBill" v-if="order_data.length > 0">
        <table width="100%" class="head">
            <tr>
                <td class="printer-header" style="font-size:2.4rem;">
                    <center>
                        <h3><b>CUSTOMER BILL</b></h3>
                    </center>
                </td>
            </tr>
            <tr>
                <td class="printer-header" style="font-size:1.4rem;">
                    <center>
                        {{ branchInfo.business_name }}
                    </center>
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    <center>
                        {{ branchInfo.name }}
                    </center>
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    <center>
                        {{
                            branchInfo.address
                                ? "Address: " + branchInfo.address
                                : ""
                        }}
                    </center>
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    <center>
                        {{ branchInfo.postal_address }}
                    </center>
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    <center>
                        {{
                            branchInfo.phone_no
                                ? "TEL: " + branchInfo.phone_no
                                : ""
                        }}
                    </center>
                </td>
            </tr>
            <tr>
                <td class="printer-header">
                    <center>
                        {{ new Date() | moment(" YYYY-MM-DD, h:mm") }}
                    </center>
                </td>
            </tr>
        </table>

        <div>
            <table width="100%">
                <tr>
                    <td>
                        Waiter :
                        {{ userInfo.name }}
                    </td>
                </tr>
                <tr>
                    <td v-if="order_data[0]">
                        Order No :
                        {{ order_data[0].order_no }}
                    </td>
                </tr>
            </table>

            <table width="100%">
                <tr>
                    <td>
                        <div class="top_row">
                            <div style="width:20%">
                                ITEM
                            </div>
                            <div style="width:20%">
                                SIZE
                            </div>
                            <div style="width:20%">
                                QTY
                            </div>
                            <div style="width:20%">
                                AMOUNT
                            </div>
                        </div>
                        <hr />
                    </td>
                </tr>
                <tr v-for="(data, i) in order_data" :key="i">
                    <td>
                        <div class="top_row">
                            <div style="width:80%">
                                {{ data.stock ? data.stock.product_name : "" }}
                            </div>
                            <div style="width:20%">
                                {{ cashFormatter(data.row_total) }}
                            </div>
                        </div>
                        <div class="top_row">
                            <div style="width:20%">
                                {{ data.stock ? data.stock.code : "" }}
                            </div>

                            <div style="width:20%">{{ data.qty }} X</div>
                            <div style="width:20%">
                                {{ data.price }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <hr />
            <table>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <b class="t-amount">
                                BILL TOTAL:
                                {{ cashFormatter(bill_total) }}
                            </b>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <b class="t-amount">
                                <b>BUY GOODS TILL NO: 122716</b>
                            </b>
                        </center>
                    </td>
                </tr>
            </table>

            <hr />
        </div>
    </div>
</template>

<script>
import print from "print-js";
export default {
    props: ["order_data", "bill_total"],
    data() {
        return {
            branchInfo: "",
            userInfo: "",

            data: null,
            form_data: {
                order_no: "",
                order_total: 0,
                vat_paid: 0
            }
        };
    },

    created() {
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.data = this.order_data;
    },
    methods: {
        printBill: function() {
            printJS("printBill", "html");
            // this.value = value;
        }
    }
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
#printBill {
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
