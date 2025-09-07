<template>
    <div id="printBill" v-if="order_data.length > 0">
        <receipt-header />
        <table width="100%" class="head">
            <tr>
                <td class="printer-header" style="font-size: 2.4rem">
                    <center>
                        <h3>
                            <b>CUSTOMER BILL </b
                            ><b v-if="order_data[0].order_type == 'takeaway'"
                                >TAKE AWAY</b
                            >
                        </h3>
                    </center>
                </td>
            </tr>
        </table>

        <div>
            <table width="100%">
                <tr>
                    <td>
                        Served By :
                        {{ userInfo.name }}
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
            </table>
            <receipt-body-component :data="order_data" />

            <hr />
            <table>
                <tr>
                    <td><b> ========================================= </b></td>
                </tr>
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
                    <td><b> ========================================= </b></td>
                </tr>
                <br />
                <tr>
                    <td class="printer-footer">
                        <center>
                            <p v-if="order_data[0].customer_name">
                                <b> Customer Info</b>
                            </p>
                            <span>{{ order_data[0].customer_name }}</span>
                            <span>{{ order_data[0].customer_phone }}</span
                            ><br />
                            <span>{{ order_data[0].customer_address }}</span>
                        </center>
                    </td>
                </tr>
                <br />
                <tr>
                    <td class="printer-footer">
                        <center>
                            <b class="t-amount">
                                <b>BUY GOODS TILL NO: {{ till_no }}</b>
                            </b>
                        </center>
                    </td>
                </tr>
                <br />
                <tr>
                    <td>
                        <bar-code-component :data="order_data[0].order_no" />
                    </td>
                </tr>
            </table>

            <hr />
        </div>
    </div>
</template>

<script>
import print from "print-js";
import ItemParticularsComponent from "../dinerscomponents/ItemParticularsComponent.vue";
import ReceiptHeader from "../dinerscomponents/ReceiptHeader.vue";
import ReceiptBodyComponent from "../dinerscomponents/ReceiptBodyComponent.vue";

import BarCodeComponent from "../dinerscomponents/BarCodeComponent.vue";
export default {
    components: {
        ItemParticularsComponent,
        ReceiptHeader,
        ReceiptBodyComponent,

        BarCodeComponent,
    },
    props: ["order_data"],
    data() {
        return {
            height: 80,
            branchInfo: "",
            userInfo: "",
            bill_total: 0,
            data: null,
            form_data: {
                order_no: "",
                order_total: 0,
                vat_paid: 0,
            },
        };
    },

    created() {
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.data = this.order_data;

        this.bill_total = this.data.reduce(function (sum, val) {
            return sum + val.row_total;
        }, 0);
    },
    methods: {
        printBill: function () {
            printJS("printBill", "html");
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
