<template>
    <div id="printReceipt">
        <table width="100%" class="head">
            <tr>
                <td class="printer-header" style="font-size:2.4rem;">
                    <center>
                        <h3><b>Sales Receipt</b></h3>
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
                        {{waiter }}
                    </td>
                </tr>
                <tr>
                    <td v-if="order_data[0]">
                        Order No :
                        {{ order_data[0].order_no }}
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
                            <p class="t-amount">
                                TAX AMOUNT  :
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
                        Thank you for dining with us please 
                        welcome 
                    </td>
                </tr>
                <tr>
                    <td>Cashier : {{  userInfo.name }}</td>
                </tr>
                <tr>
                    ================================================
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
import print from "print-js";
export default {
    props: ["order_data"],
    data() {
        return {
            branchInfo: "",
            userInfo: "",
            waiter:'',
            bill_total: 0,
            tax_total: 0,
            data: null,
            form_data: {
                order_no: "",
                order_total: 0,
                vat_paid: 0
            }
        };
    },
    watch: {
        data: {
            deep: true,
            handler(val, old) {
                this.bill_total = this.data.reduce(function(sum, val) {
                    return sum + val.row_total;
                }, 0);
                this.tax_total = this.data.reduce(function(sum, val) {
                    return sum + val.row_vat;
                }, 0);
            }
        }
    },
    created() {
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.data = this.order_data;
    //    this.waiter=this.order_data[0].user.name
    },
    methods: {
        printCashier: function() {
          
            printJS("printReceipt", "html");
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
/* #printReceipt {
    visibility: hidden;
} */
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
