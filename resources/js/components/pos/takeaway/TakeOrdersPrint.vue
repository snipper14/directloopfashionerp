<template>
    <div id="printMe">
        <table width="100%" class="head">
            <tr>
                <td class="printer-header" style="font-size:2.4rem;">
                    <center>
                        <h3><b>KITCHEN RECEIPT</b></h3>
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
                    <td>
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
                                QTY
                            </div>
                            <div style="width:20%">
                                PRICE
                            </div>
                        </div>
                    </td>
                </tr>
                <tr v-for="(data, i) in order_data" :key="i">
                    <td>
                        <div class="top_row">
                            <div style="width:80%">
                                <span
                                    ><b>{{
                                        data.stock
                                            ? data.stock.product_name
                                            : ""
                                    }}</b>
                                </span>
                            </div>
                            <div style="width:20%">{{ data.qty }} X</div>
                            <div style="width:20%">
                                {{ cashFormatter(data.price) }}
                            </div>
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
export default {
    props: ["order_data"],
    data() {
        return {
            branchInfo: "",
            userInfo: "",
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
    },
    methods: {
        setValue: function(value) {
            printJS("printMe", "html");
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
