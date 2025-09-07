<template>
    <div id="printReceipt" ref="contentToPrint">
         <div style="max-width: 300px;float-left">
        <receipt-header />
        <table width="100%" class="head">
            <tr>
                <td>
                    <center>
                        <h3><b> CASH DROPS</b></h3>
                    </center>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>

        <table width="100%" class="mt-3">
            <tr>
                <td class="room-details">User</td>
                <td class="room-details">
                    {{ printData.user.name }}
                </td>
            </tr>
              <tr>
                <td class="room-details">Ref No.</td>
                <td class="room-details">
                    {{ printData.ref_no }}
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>
        <table width="100%" class="mt-2">
            <tr>
                <td class="room-details">Drop Timestamp</td>
                <td class="room-details">
                    {{ formatMonthDateTime(printData.created_at) }}
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b> ========================================= </b></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Cash Drop Amount: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ cashFormatter(printData.cash_amount) }}</b>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Cash Drawer Account: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ printData.cashdrawer_account.account }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Cash Deposit Account: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ printData.account.account }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="top_row">
                    <div style="width: 15%">
                        <b> Note: </b>
                    </div>
                    <div style="width: 15%">
                        <b> {{ printData.note }}</b>
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td><b> ========================================= </b></td>
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
import ReceiptHeader from "../menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    components: {
        ReceiptHeader,
    },
    props: ["printData"],
    data() {
        return {
            deficit_amount: 0,
        };
    },

    created() {
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
