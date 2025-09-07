<template>
    <div id="printReceipt" ref="contentToPrint">
        <div style="max-width: 300px;float-left">
            <receipt-header-2 />

            <table style="padding-top: 2rem">
                <tr>
                    <td v-if="order_data[0]">
                        Reprint Receipt Date:
                        {{ formatMonthDateTime(form_data.created_at) }}
                    </td>
                </tr>
                <tr>
                    <td v-if="order_data[0]">
                        receipt no :
                        {{ form_data.receipt_no }}
                    </td>
                </tr>
                <tr>
                    <td v-if="form_data.customer_name">
                        Customer Name:{{
                            form_data.customer_name.replace("-null", "")
                        }}
                    </td>
                </tr>
                <tr>
                    <td v-if="form_data.customer_name">
                        Cust_PIN : {{ order_data[0].customer.pin }}
                    </td>
                </tr>
            </table>
            <table>
                <tr></tr>
            </table>
            <table
                style="
                    width: 100%;
                    font-size: 12px;
                    margin-top: 1rem;
                    table-layout: fixed;
                "
            >
                <tr>
                    <td>
                        <div class="top_row">
                            <div style="width: 15%; text-align: left">
                                <b>Qty</b>
                            </div>
                            <div style="width: 20%; text-align: right">
                                <b>@Cost</b>
                            </div>
                            <div style="width: 20%; text-align: right">
                                <b>@Amount</b>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>===============================</td>
                </tr>
                <tr v-for="(data, i) in order_data" :key="i">
                    <td>
                        <div class="top_row">
                            <div style="width: 100%; text-align: left">
                                <b>{{ data.stock.product_name }}</b>
                                <!-- Product name on its own line -->
                            </div>
                            <div style="width: 20%; text-align: left">
                                {{ data.stock.hs_code }}
                            </div>
                            <div
                                style="
                                    width: 5%;
                                    text-align: left;
                                    margin-left: 2px;
                                "
                            >
                                {{ formatNumber(data.qty) }}
                            </div>
                            <div style="width: 15%; text-align: left">
                                {{ formatNumber(data.price) }}
                            </div>
                            <div style="width: 15%; text-align: left">
                                {{ formatNumber(data.row_total) }}
                            </div>
                        </div>

                        <div
                            v-if="data.discount > 0"
                            style="
                                margin-left: 15%;
                                font-size: 10px;
                                color: gray;
                            "
                        >
                            Discount: {{ data.discount }}%
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>----------------------------</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td class="printer-footer">
                        <p class="t-amount">
                            TAX AMOUNT :
                            {{ cashFormatter(round(form_data.total_vat)) }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="printer-footer">
                        <b class="t-amount">
                            RECEIPT TOTAL:
                            {{ cashFormatter(form_data.receipt_total) }}
                        </b>
                    </td>
                </tr>
                <tr v-if="form_data.discount_percent > 0">
                    <td class="printer-footer">
                        <b class="t-amount">
                            Discount:
                            {{ cashFormatter(form_data.discount_percent) }}%
                        </b>
                    </td>
                </tr>
                <tr v-if="form_data.discount_pay > 0">
                    <td class="printer-footer">
                        <b class="t-amount">
                            Discount Amount:
                            {{ cashFormatter(form_data.discount_pay) }}
                        </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span
                            class="t-amount"
                            v-if="form_data.cash_pay.length > 0"
                        >
                            CASH:
                            {{ cashFormatter(round(form_data.cash_pay)) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span
                            class="t-amount"
                            v-if="form_data.mpesa_pay.length > 0"
                        >
                            M-PESA:
                            {{
                                cashFormatter(round(form_data.mpesa_pay))
                            }}</span
                        >
                    </td>
                </tr>
                <tr>
                    <td>
                        <span
                            class="t-amount"
                            v-if="form_data.card_pay.length > 0"
                        >
                            Card:
                            {{ cashFormatter(round(form_data.card_pay)) }}</span
                        >
                    </td>
                </tr>

                <tr>
                    <td>{{ this.$store.state.branch.footer_text }}</td>
                </tr>
                <tr>
                    <td>Cashier : {{ this.$store.state.user.name }}</td>
                </tr>
                <tr></tr>
            </table>
            <div><POSCreditEtimsFooter :etims_data="etims_data" /></div>
        </div>
    </div>
</template>

<script>
import print from "print-js";

import EtimsFooter from "../../pos_retail/EtimsFooter.vue";
import ReceiptHeader from "../menu_components/dinerscomponents/ReceiptHeader.vue";
import ReceiptBodyComponent from "../menu_components/dinerscomponents/ReceiptBodyComponent.vue";
import POSCreditEtimsFooter from "../../pos_retail/credit_pos/POSCreditEtimsFooter.vue";
import ReceiptHeader2 from "../menu_components/dinerscomponents/ReceiptHeader2.vue";

export default {
    components: {
        EtimsFooter,
        ReceiptHeader,
        ReceiptBodyComponent,
        POSCreditEtimsFooter,
        ReceiptHeader2,
    },
    props: ["form_data", "order_data", "etims_data"],
    data() {
        return {
            user: null,
        };
    },

    created() {
        this.user = this.$store.state.name;
        console.log(JSON.stringify(this.form_data));
    },
    methods: {
        printReceipt: function () {
            // Get the content to print
            const contentToPrint = this.$refs.contentToPrint;

            // Create an invisible iframe
            const iframe = document.createElement("iframe");
            iframe.style.position = "absolute";
            iframe.style.top = "-10000px"; // Hide the iframe from the viewport
            document.body.appendChild(iframe);

            // Access the iframe document
            const doc = iframe.contentWindow.document;

            // Write the content into the iframe
            doc.open();
            doc.write(`
      <html>
        <head>
          <title>Print</title>
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
          <style>
            .top-image {
              margin-top: 28px;
            }
          .particulars{
           width: 45%;
            text-align: left;
            word-wrap: break-word;
            font-size: 12px;
               } 
                    .top_row{   margin-inline-start: 0px;
            display: flex;
                justify-content:   space-around;
                flex-wrap: wrap;
                width:80%;
                }
          
          </style>
        </head>
        <body>
          ${contentToPrint.innerHTML}
        </body>
      </html>`);
            doc.close();

            // Wait for the content to load and then print
            iframe.contentWindow.focus();
            setTimeout(() => {
                iframe.contentWindow.print();
                document.body.removeChild(iframe); // Clean up the iframe after printing
            }, 500);
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
