<template>
    <div id="printReceipt">
        <div>
            <center><receipt-header /></center>

            <table>
                <tr>
                    <td v-if="order_data[0]">
                        <center>
                            receipt no :
                            {{ form_data.receipt_no }}
                        </center>
                    </td>
                </tr>
                <tr>
                    <td v-if="form_data.customer_name">
                        <center>
                            Customer Name :
                            {{ form_data.customer_name.replace("-null", "") }}
                            <br />
                            <span v-if="form_data.customer_pin"
                                >Customer P.I.N No:
                                {{ form_data.customer_pin }}</span
                            >
                        </center>
                    </td>
                </tr>
            </table>
            <table>
                <tr></tr>
                <!-- <tr>
                    ================================================
                </tr> -->
            </table>
            <receipt-body-component :data="order_data" />

            <table>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <p class="t-amount">
                                TAX AMOUNT :
                                {{ cashFormatter(round(form_data.total_vat)) }}
                            </p>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <h3 class="t-amount">
                                RECEIPT TOTAL:
                                {{ cashFormatter(form_data.receipt_total) }}
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
                            {{ cashFormatter(round(form_data.cash_pay)) }}
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
                            {{ cashFormatter(round(form_data.mpesa_pay)) }}
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
                            {{ cashFormatter(round(form_data.card_pay)) }}
                        </center>
                    </td>
                </tr>

                <tr>
                    ================================================
                </tr>
                <tr></tr>

                <tr>
                    <td>{{ this.$store.state.branch.footer_text }}</td>
                </tr>
                <tr>
                    <td>Cashier : {{ this.$store.state.user.name }}</td>
                </tr>
                <tr></tr>
            </table>
            <etims-footer
                :etims_data="etims_data"
                :etims_qrcode_data="etims_qrcode_data"
            />
        </div>
    </div>
</template>

<script>
import print from "print-js";

import ReceiptBodyComponent from "./dinerscomponents/ReceiptBodyComponent.vue";
import ReceiptHeader from "./dinerscomponents/ReceiptHeader.vue";
import EtimsFooter from "../../pos_retail/EtimsFooter.vue";

export default {
    components: {
        ReceiptBodyComponent,
        ReceiptHeader,
        EtimsFooter,
    },
    props: ["form_data", "order_data", "etims_data", "etims_qrcode_data"],
    data() {
        return {
            user: null,
        };
    },

    created() {
        this.user = this.$store.state.name;
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
