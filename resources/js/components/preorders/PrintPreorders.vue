<template>
    <div id="printReceipt" ref="contentToPrint">
        <div
            class="receipt-container"
            style="
                font-family: 'Courier New', Courier, monospace;
                font-size: 12px;
                line-height: 1.4;
                max-width: 290px;float-left
            "
        >
            <!-- Header Section -->
            <receipt-header-2 />
            <table width="100%" class="head">
                <tr>
                    <td >
                        {{ new Date() | moment("YYYY-MM-DD, h:mm A") }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b
                         
                        >
                            Preorder Sales Receipt
                        </b>
                    </td>
                </tr>
                <tr>
                    <td>Customer: {{ form_data1.customer_name || "N/A" }}</td>
                </tr>
                <tr>
                    <td>
                        Customer Phone: {{ form_data1.customer_phone || "N/A" }}
                    </td>
                </tr>
            </table>

            <!-- Separator -->

            <!-- Items Section -->
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
                            <div style="width: 20%; text-align: left">
                                <b>@Cost</b>
                            </div>
                            <div style="width: 20%; text-align: left">
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
                                <b>{{  data.stock.product_name }}</b>
                                <!-- Product name on its own line -->
                            </div>
                           
                            <div style="width: 5%; text-align: left">
                                {{ cashRounded(data.qty.toFixed(2)) }}
                            </div>
                            <div style="width: 15%; text-align: left">
                                {{ cashRounded(data.selling_price.toFixed(2)) }}
                            </div>
                            <div style="width: 15%; text-align: left">
                                {{ cashRounded(data.row_total.toFixed(2)) }}
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>----------------------------</td>
                </tr>
                </table>
       

            <!-- Separator -->
            <div class="separator">--------------------------------</div>

            <!-- Summary Section -->
            <div class="receipt-summary">
                <table>
                    <tr>
                        <td>TAX Amount:</td>
                        <td class="right">
                            {{ cashFormatter(round(form_data.total_vat)) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="bold">Receipt Total:</td>
                        <td class="right bold">
                            {{ cashFormatter(form_data.receipt_total) }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Payments Section -->
            <div class="receipt-payments">
                <table>
                    <tr v-if="form_data.cash_pay.length > 0">
                        <td>Cash:</td>
                        <td class="right">
                            {{ cashFormatter(round(form_data.cash_pay)) }}
                        </td>
                    </tr>
                    <tr v-if="form_data.mpesa_pay.length > 0">
                        <td>M-PESA:</td>
                        <td class="right">
                            {{ cashFormatter(round(form_data.mpesa_pay)) }}
                        </td>
                    </tr>
                    <tr v-if="form_data.card_pay.length > 0">
                        <td>Card:</td>
                        <td class="right">
                            {{ cashFormatter(round(form_data.card_pay)) }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer Section -->
            <div class="separator">--------------------------------</div>
            <div class="receipt-footer">
                <p>{{ this.$store.state.branch.footer_text }}</p>
                <p>Cashier: {{ this.$store.state.user.name }}</p>
                <p>{{ new Date() | moment("YYYY-MM-DD, h:mm") }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import print from "print-js";
import ReceiptHeader2 from "../pos/menu_components/dinerscomponents/ReceiptHeader2.vue";

export default {
    components: {
        ReceiptHeader2,
    },
    data() {
        return {
            branchInfo: this.$store.state.branch,
            branch_name: "",
        };
    },
    props: ["form_data", "form_data1", "order_data"],
    methods: {
        printReceipt() {
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
        cashFormatter(amount) {
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "KES",
            }).format(amount);
        },
    },
};
</script>
