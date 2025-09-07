<template>
    <div id="printReceipt" ref="contentToPrint">
        <div
            class="receipt-container"
            style="
               
                  font-weight: 700;
                  color: #000;
                font-size: 12px;
                line-height: 1.4;
                max-width: 290px;float-left
            "
        >
            <!-- Header Section -->
            <PrintHeader />
            <table width="100%" class="head">
                <tr>
                    <td>
                        {{ new Date() | moment("YYYY-MM-DD, h:mm A") }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b> Direct Credit Note </b>
                    </td>
                </tr>
                <tr>
                    <td>Customer: {{ array_data[0].company_name || "N/A" }}</td>
                </tr>
                <tr>
                    <td v-if="array_data[0].company_phone">
                        Customer Phone:
                        {{ array_data[0].company_phone || "N/A" }}
                    </td>
                </tr>
                <tr>
                    <td v-if="array_data[0].ref_no">
                        Credit No: {{ array_data[0].credit_no || "N/A" }}
                    </td>
                </tr>
                <tr>
                    <td v-if="array_data[0].ref_no">
                        RefNo: {{ array_data[0].ref_no || "N/A" }}
                    </td>
                </tr>
            </table>

            <!-- Separator -->

            <!-- Items Section -->

            <b> Original Credit Items</b>
            <table style="">
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
               

                <tr v-for="(data, i) in array_data" :key="i">
                    <td>
                        <div class="top_row">
                            <div style="width: 100%; text-align: left">
                                <b>{{ data.stock.product_name }}</b>
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
                    <td>
                        <div class="top_row">
                            <div style="width: 35%; text-align: left"><b>Total Amount</b></div>
                            <div style="width: 35%; text-align: left">
                                <b>{{ cashFormatter(credit_total) }}</b>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
 
                    <div>===============================</div>
              
            <div v-if="replacement_array.length > 0">
                <b> Replaced Items</b>
                <table>
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
                  
                    <tr v-for="(data, i) in replacement_array" :key="i">
                        <td>
                            <div class="top_row">
                                <div style="width: 100%; text-align: left">
                                    <b>{{ data.stock.product_name }}</b>
                                    <!-- Product name on its own line -->
                                </div>

                                <div style="width: 5%; text-align: left">
                                    {{ cashRounded(data.qty.toFixed(2)) }}
                                </div>
                                <div style="width: 15%; text-align: left">
                                    {{
                                        cashRounded(
                                            data.selling_price.toFixed(2)
                                        )
                                    }}
                                </div>
                                <div style="width: 15%; text-align: left">
                                    {{ cashRounded(data.row_total.toFixed(2)) }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div class="top_row">
                                <div style="width: 35%; text-align: left"><b> Replacement Amount</b></div>
                                <div style="width: 35%; text-align: left">
                                    <b>{{
                                        cashFormatter(replacement_total)
                                    }}</b>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- Separator -->
            <div class="separator">--------------------------------</div>
            <table>
                <tr>
                    <td>
                        <div class="top_row">
                            <div  style="width: 35%; text-align: left"><b>Pending Balance:</b></div>
                            <div style="width: 35%; text-align: left">
                                <b> {{ cashFormatter(pending_balance) }}</b>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <!-- Summary Section -->
            <div class="receipt-summary" v-if="form_data">
                <table>
                    <tr>
                        <td>TAX Amount:</td>
                        <td class="right">
                            {{ cashFormatter(form_data.total_vat) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="bold">Topup Total:</td>
                        <td class="right bold">
                            {{ cashFormatter(form_data.receipt_total) }}
                        </td>
                    </tr>
                </table>
            </div>

            Payments Section
            <div class="receipt-payments" v-if="form_data">
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
                <p>Cashier: {{ this.$store.state.user.name }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import print from "print-js";
import PrintHeader from "../../pos/menu_components/dinerscomponents/PrintHeader.vue";

export default {
    components: {
        PrintHeader,
    },
    data() {
        return {
            branchInfo: this.$store.state.branch,
            branch_name: "",
        };
    },
    props: [
        "form_data",
        "credit_total",
        "total_tax",
        "replacement_total",
        "array_data",
        "replacement_array",
        "pending_balance",
    ],
    mounted() {
        console.log(JSON.stringify(this.array_data));
    },
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
        // cashFormatter(amount) {
        //     return new Intl.NumberFormat("en-US", {
        //         style: "currency",
        //         currency: "KES",
        //     }).format(amount);
        // },
    },
};
</script>
