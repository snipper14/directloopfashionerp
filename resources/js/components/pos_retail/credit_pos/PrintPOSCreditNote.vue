<template>
    <div id="printReceipt">
        <div>
            <center><receipt-header /></center>
            <table>
                <tr>
                    <td style="font-size: 2.4rem">
                        <center>
                            <h3>
                                <b>CREDIT NOTE</b>
                            </h3>
                        </center>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td>
                        <center>
                            receipt no :
                            {{ order_data[0].receipt_no }}
                        </center>
                    </td>
                </tr>
                <tr>
                    <td v-if="form_data.cu_invoice_no">
                        <center>
                            cu invoice no :
                            {{ form_data.cu_invoice_no }}
                        </center>
                    </td>
                </tr>
                <tr>
                    <td>
                        <center>
                            credit no :
                            {{ form_data.credit_no }}
                        </center>
                    </td>
                </tr>
                <tr>
                    ================================================
                </tr>
            </table>
            <table width="90%">
                <tr>
                    <td>
                        <div class="top_row">
                            <div style="width: 5%"><b> Qty</b></div>
                            <div style="max-width: 70%">
                                <b> Particulars</b>
                            </div>
                            <div style="max-width: 25%">
                                <table>
                                    <tr>
                                        <td><b> @cost</b></td>

                                        <td><b> Amt</b></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    ================================================
                </tr>
                <tr v-for="(data, i) in order_data" :key="i">
                    <td>
                        <div class="top_row">
                            <div style="width: 5%">
                                <b> {{ data.qty }}</b>
                            </div>
                            <div style="max-width: 70%">
                                {{ data.stock.name }}
                            </div>
                            <div style="max-width: 25%">
                                <table>
                                    <tr>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.credit_sp
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>

                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.credit_amount
                                                    )
                                                }}</b
                                            >
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    ================================================
                </tr>
            </table>

            <table>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <p class="t-amount">
                                TAX AMOUNT :
                                {{
                                    cashFormatter(round(form_data.total_vat_cr))
                                }}
                            </p>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <h3 class="t-amount">
                                CREDIT TOTAL:
                                {{ cashFormatter(form_data.total_cr) }}
                            </h3>
                        </center>
                    </td>
                </tr>
                <!-- <printer-replacement-component
                    :sale_details="order_data.all_sale_details_report"
                /> -->
                <tr>
                    ================================================
                </tr>
                <tr></tr>

                <tr>
                    <td>Thank you for shopping from us please welcome</td>
                </tr>
                <tr>
                    <td>Cashier : {{ this.$store.state.user.name }}</td>
                </tr>
            </table>
            <div>
                <POSCreditEtimsFooter
                    v-if="etims_data"
                    :etims_data="etims_data"
                />
            </div>
        </div>
    </div>
</template>

<script>
import print from "print-js";

import ReceiptBodyComponent from "../../pos/menu_components/dinerscomponents/ReceiptBodyComponent.vue";
import ReceiptHeader from "../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";
import POSCreditEtimsFooter from "./POSCreditEtimsFooter.vue";
import PrinterReplacementComponent from "./PrinterReplacementComponent.vue";

export default {
    components: {
        ReceiptBodyComponent,
        ReceiptHeader,
        POSCreditEtimsFooter,
        PrinterReplacementComponent,
    },

    props: ["form_data", "order_data"],
    data() {
        return {
            user: null,
            etims_data: null,
        };
    },

    created() {
        this.etims_data = this.order_data[0];
        console.log(JSON.stringify(this.order_data));
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
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
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
