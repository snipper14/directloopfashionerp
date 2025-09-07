<template>
    <div id="printMe">
        <receipt-header />
        <table width="100%" class="head">
            <tr>
                <td class="printer-header" style="font-size: 2.4rem">
                    <center>
                        <h3>
                            <b
                                >{{
                                    order_data[0].department
                                        ? order_data[0].department.department
                                        : ""
                                }}
                                ORDER</b
                            >
                        </h3>
                    </center>
                </td>
            </tr>
        </table>

        <div>
            <table width="90%">
                <tr>
                    <td>
                        Waiter :
                        {{ userInfo.name }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Order No :
                        {{ order_data[0].order_no }}
                    </td>
                </tr>
                <br />
                <tr>
                    <td v-if="order_data[0].delivery_time">
                        Delivery Time {{ order_data[0].delivery_time }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <span v-if="isTakeAway"><b>TAKE AWAY ORDER/S</b></span>
                     
                        <span v-else
                            >Table : {{ order_data[0].table.name }}</span
                        >
                    </td>
                </tr>
            </table>
            <br />
        </div>
        <div>
            <table width="90%">
                <tr>
                    <td>
                        <div class="top_row">
                            <div style="width: 80%">ITEM</div>

                            <div style="width: 20%">QTY</div>
                        </div>
                    </td>
                </tr>
                <tr
                    v-for="(data, i) in order_data"
                    :key="i"
                    v-if="data.orderPrinted == 0"
                >
                    <td>
                        <div class="top_row">
                            <div style="width: 80%">
                                <span>
                                    <b>{{ productNameUpper(data) }}</b>
                                </span>
                            </div>
                            <div style="width: 20%">X {{ data.qty }}</div>
                        </div>
                        <span>
                            <span v-if="data.notes">{{ data.notes }}</span>
                        </span>
                        <hr />
                    </td>
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
import print from "print-js";
import ReceiptHeader from "../dinerscomponents/ReceiptHeader.vue";
export default {
    components: { ReceiptHeader },
    props: ["order_data"],
    data() {
        return {
            branchInfo: "",
            userInfo: "",
            form_data: {
                order_no: "",
                order_total: 0,
                vat_paid: 0,
            },
            isTakeAway: false,
            isOnline:false,
        };
    },
    created() {
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.order_data[0].table_id
            ? (this.isTakeAway = false)
            : (this.isTakeAway = true);
    },
    methods: {
        setValue: function (value) {
            printJS("printMe", "html");
            // this.value = value;
        },
        productNameUpper(data) {
            var accompanimnent = data.accompaniment.product_name
                ? " Served With " + data.accompaniment.product_name
                : "";

            return data.stock
                ? data.stock.product_name.toUpperCase() +
                      accompanimnent.toUpperCase()
                : "";
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
#printMe {
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
