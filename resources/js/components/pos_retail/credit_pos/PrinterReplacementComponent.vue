<template>
   
        <div>
            
            <table>
                <tr>
                    <td style="font-size: 2.4rem">
                        <center>
                            <h3>
                                <b>Replacement</b>
                            </h3>
                        </center>
                    </td>
                </tr>
            </table>

            <table>
              
             
            
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
                <tr v-for="(data, i) in sale_details" :key="i">
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
                                                        data.price
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
                <POSCreditEtimsFooter v-if="etims_data" :etims_data="etims_data"/>
            </div>
        </div>
   
</template>

<script>
import print from "print-js";

import ReceiptBodyComponent from "../../pos/menu_components/dinerscomponents/ReceiptBodyComponent.vue";
import ReceiptHeader from "../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";
import POSCreditEtimsFooter from './POSCreditEtimsFooter.vue';


export default {
    components: {
        ReceiptBodyComponent,
        ReceiptHeader,
       POSCreditEtimsFooter,
    },
        
    props: ["sale_details"],
    data() {
        return {
            user: null,
            etims_data:null
        };
    },

    created() {
       
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
