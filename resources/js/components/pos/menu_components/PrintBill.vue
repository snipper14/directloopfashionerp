<template>
    <div id="printReceipt">
        <div>
            <center><receipt-header /></center>
            <table>
                <tr>
                    <td style="font-size: 2.4rem">
                        <center>
                            <h3>
                                <b>CUSTOMER BILL</b>
                            </h3>
                        </center>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td v-if="order_data[0]">
                        <center>
                            order no :
                            {{ form_data.order_no }}
                        </center>
                    </td>
                </tr>
                <tr>
                    ================================================
                </tr>
            </table>
            <receipt-body-component :data="order_data" />

            <table>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <h3 class="t-amount">
                                RECEIPT TOTAL:
                                {{ cashFormatter(parseFloat(receipt_total).toFixed(2)) }}
                            </h3>
                        </center>
                    </td>
                </tr>

                <tr>
                    ================================================
                </tr>
                <tr></tr>

                <tr>
                    <td>Cashier : {{ this.$store.state.user.name }}</td>
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

import ReceiptBodyComponent from "./dinerscomponents/ReceiptBodyComponent.vue";
import ReceiptHeader from "./dinerscomponents/ReceiptHeader.vue";

export default {
    components: {
        ReceiptBodyComponent,
        ReceiptHeader,
    },
    props: ["form_data", "order_data"],
    data() {
        return {
            user: null,
            receipt_total: 0,
            total_vat: 0,
        };
    },

    created() {
        this.user = this.$store.state.name;
        this.receipt_total = this.order_data.reduce(function (sum, val) {
            return sum + val.row_amount;
        }, 0);
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
