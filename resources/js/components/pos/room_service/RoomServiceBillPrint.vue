<template>
    <div id="printBill" v-if="order_data.length > 0">
        <receipt-header />
        <table width="100%" class="head">
            <tr>
                <td class="printer-header" style="font-size: 2.4rem">
                    <center>
                        <h3><b>CUSTOMER BILL</b></h3>
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
                <tr>
                    <td v-if="order_data[0]">
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
                            <h3 class="t-amount">
                                Service Charge
                                {{ order_data[0].service_charge_rate }}%:
                                {{ cashFormatter(service_charge_total) }}
                            </h3>
                        </center>
                    </td>
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
                    <td class="printer-footer">
                        <center>
                            <b class="t-amount">
                                <b>BUY GOODS TILL NO: 122716</b>
                            </b>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td>
                        <bar-code-component  :data="order_data[0].order_no"/>
                    </td>
                </tr>
            </table>

            <hr />
        </div>
    </div>
</template>

<script>
import print from "print-js";
import ReceiptHeader from "../menu_components/dinerscomponents/ReceiptHeader.vue";
import ReceiptBodyComponent from "../menu_components/dinerscomponents/ReceiptBodyComponent.vue";
import BarCodeComponent from '../menu_components/dinerscomponents/BarCodeComponent.vue';
export default {
    components: { ReceiptHeader, ReceiptBodyComponent, BarCodeComponent },
    props: [
        "order_data",
        
        "tax_total",
        "service_charge_total",
        "sub_total",
    ],
    data() {
        return {
            branchInfo: "",
            userInfo: "",
            data: null,
            bill_total:0,
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
           this.bill_total = this.data.reduce(function(sum, val) {
                    return sum + val.row_total;
                }, 0);
                console.log(this.bill_total);
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
