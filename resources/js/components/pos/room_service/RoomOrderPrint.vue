<template>
    <div id="printMe">
        <receipt-header/>
        <table width="100%" class="head">
            <tr>
                <td class="printer-header" style="font-size: 2.4rem">
                    <center>
                        <h3>
                            <b
                                >{{
                                    order_data[0].department.department
                                }}
                                ORDER</b
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
                <tr>
                    <td>
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

            <table width="100%">
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
                                <item-particulars-component :data="data" />
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
import ItemParticularsComponent from "../menu_components/dinerscomponents/ItemParticularsComponent.vue";
import ReceiptHeader from '../menu_components/dinerscomponents/ReceiptHeader.vue';

export default {
    components: {
        ItemParticularsComponent,
        ReceiptHeader,
    },
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
        };
    },
    created() {
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
    },
    methods: {
        setValue: function (value) {
            printJS("printMe", "html");
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
