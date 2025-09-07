<template>
    <div id="printReceipt" ref="contentToPrint">
        <div style="max-width: 300px;float-left">
            <receipt-header-2 />
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
                    =========================
                </tr>
            </table>
            <table width="90%">
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
                    ===========================
                </tr>
                <tr v-for="(data, i) in order_data" :key="i">
                    <td>
                        <div class="top_row">
                            <div style="width: 100%; text-align: left">
                                <b>{{ data.product_name }}</b>
                            </div>
                            <div style="width: 20%; text-align: left">
                                {{ data.hs_code }}
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
                                {{ formatNumber(data.selling_price) }}
                            </div>
                            <div style="width: 15%; text-align: left">
                                {{ formatNumber(data.row_amount) }}
                            </div>

                            <div
                                v-if="data.discount > 0"
                                style="width: 60%; margin-top: -4"
                            >
                                <b>Discount {{ data.discount }}(%)</b>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    ============================
                </tr>
            </table>

            <table>
                <tr>
                    <td class="printer-footer">
                        <center>
                            <h3 class="t-amount">
                                RECEIPT TOTAL:
                                {{
                                    cashFormatter(
                                        parseFloat(receipt_total).toFixed(2)
                                    )
                                }}
                            </h3>
                        </center>
                    </td>
                </tr>

                <tr>
                    =========================
                </tr>
                <tr></tr>

                <tr>
                    <td>Cashier : {{ this.$store.state.user.name }}</td>
                </tr>
                <tr>
                    ============================
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
import print from "print-js";

import ReceiptBodyComponent from "./dinerscomponents/ReceiptBodyComponent.vue";
import ReceiptHeader from "./dinerscomponents/ReceiptHeader.vue";
import ReceiptHeader2 from './dinerscomponents/ReceiptHeader2.vue';



export default {
    components: {
        ReceiptBodyComponent,
        ReceiptHeader,
        ReceiptHeader2,
       
      
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
