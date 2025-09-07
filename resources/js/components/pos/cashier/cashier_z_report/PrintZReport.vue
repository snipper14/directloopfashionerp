<template>
    <div id="printReceipt"    ref="contentToPrint" >
        <div  style="max-width: 300px;float-left">
        <receipt-header />
        <table width="100%" class="head">
            <tr>
                <td>
                    <center>
                        <h3><b> CASHIER Z-REPORT</b></h3>
                    </center>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>

        <table width="100%" class="mt-3">
            <tr>
                <td class="room-details">Cashier Name</td>
                <td class="room-details">
                    {{ form_data.user.name }}
                </td>
            </tr>

            <tr>
                <td class="room-details"><b>POINT</b></td>
                <td class="room-details">CASHIER POINT</td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%" class="mt-2">
            <tr>
                <td class="room-details">Shift Opening Time</td>
                <td class="room-details">
                    {{ reprint_data.opened_time }}
                </td>
            </tr>
            <tr>
                <td class="room-details">Shift Closing Time</td>
                <td class="room-details">
                    {{ reprint_data.created_at }}
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>OPENINGS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Opening Mpesa Amount: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(reprint_data.opening_mpesa_amount)
                            }}</b
                        >
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Opening Cash Amount: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(reprint_data.opening_cash_amount)
                            }}</b
                        >
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>OPENING TOTALS: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.opening_mpesa_amount +
                                        reprint_data.opening_cash_amount
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>SALES</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Cash Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.cash_payments) }}</b>
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Mpesa Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.mpesa_payments) }}</b>
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Card Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.card_payments) }}</b>
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Loyalty Cashouts: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(reprint_data.loyalty_payments)
                            }}</b
                        >
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Credit Cashout: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{ cashFormatter(reprint_data.credit_payments) }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>

        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>RETURNS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Cash Returns Amount: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(reprint_data.return_cash_sales)
                            }}</b
                        >
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Mpesa Returns Amount: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(reprint_data.return_mpesa_sales)
                            }}</b
                        >
                    </div>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> TOTAL RETURNS: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.return_mpesa_sales +
                                        reprint_data.return_cash_sales
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>DROPS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Cash Drops: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.cash_drops) }}</b>
                    </div>
                </td>
                 <td class="top_row">
                    <div style="width: 15%">
                        <b>Mpesa Drops: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.mpesa_drops) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>SUPPLIER PAYMENTS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Cash Supplier Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.cash_supplier_payments
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Mpesa Supplier Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.mpesa_supplier_payments
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>EXPENSES PAYMENTS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Cash Expenses Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.cash_expenses_payments
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Mpesa Expenses Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.mpesa_expenses_payments
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>CUSTOMER PAYMENTS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Cash Customer Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.cash_customer_payments
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Mpesa Customer Payments: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{
                                cashFormatter(
                                    reprint_data.mpesa_customer_payments
                                )
                            }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>EXPECTED TOTALS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Expected Cash: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.expected_cash) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Expected Mpesa: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.expected_mpesa) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Expected Card: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.expected_card) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>CLOSING TOTALS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Closing Cash: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.closing_cash) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Closing Mpesa: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.closing_mpesa) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b>Closing Card: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.closing_card) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>

        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>SHORT/OVERS</b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Cash: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.cash_shortover) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Mpesa: </b>
                    </div>
                    <div style="width: 15%">
                        <b>
                            {{ cashFormatter(reprint_data.mpesa_shortover) }}</b
                        >
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Card: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.card_shortover) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>

        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>TAX </b>
                </td>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Tax Collected: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(reprint_data.tax_collected) }}</b>
                    </div>
                </td>
            </tr>

            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <b>CATEGORY SALES</b>
                </td>
            </tr>

            <tr
                v-for="(data, i) in reprint_data.close_register_categories"
                :key="i"
            >
                <td class="top_row">
                    <div style="width: 15%">
                        {{ data.product_category.name }}
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(data.amount) }}</b>
                    </div>
                </td>
            </tr>

            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
   <table width="100%">
            <tr>
                <td class="top_row">
                    <b>HRLY SALES</b>
                </td>
            </tr>

            <tr
                v-for="(data, i) in reprint_data.close_register_hourly_sales"
                :key="i"
            >
                <td class="top_row">
                    <div style="width: 15%">
                        {{ data.hours }}
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(data.amount) }}</b>
                    </div>
                </td>
            </tr>

            <tr>
                <td><b> =========================== </b></td>
            </tr>
        </table>
        <table>
            <tr>
                <td>
                    <b>Printed By {{ userInfo.name }} </b>
                </td>
            </tr>
        </table>
        <br />
        <br /><br />
    </div>
    </div>
</template>

<script>
import print from "print-js";
import ReceiptHeader from '../../menu_components/dinerscomponents/ReceiptHeader.vue';


export default {
    components: {
        ReceiptHeader
   
    },
    props: ["reprint_data"],
    data() {
        return {
            deficit_amount: 0,
            form_data: null,
        };
    },

    created() {
        this.form_data = this.reprint_data;

        this.deficit_amount =
            parseFloat(this.form_data.cashier_counted_cash) -
            (parseFloat(this.form_data.system_total_cash) +
                parseFloat(this.form_data.cashier_opening_cash));
        this.userInfo = this.$store.state.user;
    },
    methods: {
        printReceipt: function () {
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
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
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
