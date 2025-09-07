<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <div class="row">
                    <div class="col-md-12">
                        <center>
                            <h3
                                style="
                                    text-decoration: underline;
                                    text-align: center;
                                    font-weight: bold;
                                    margin-top: 2rem;
                                "
                            >
                                PURCHASE ORDER
                            </h3>
                        </center>
                    </div>
                </div>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-6">
                        <center>
                            <img
                                :src="logo"
                                alt=""
                                :width="imageWidth"
                                :height="imageHeight"
                            />

                            <h4>
                                Order No: {{ po_details[0].order_no }}
                                <br />
                                <p>
                                    Date:
                                    {{
                                        formatDateToDDMMYYYY(
                                            po_details[0].receipt_date
                                        )
                                    }}
                                </p>
                                <p>
                                    Delivery Deadline:
                                    {{
                                        formatDateToDDMMYYYY(
                                            po_details[0].order_deadline
                                        )
                                    }}
                                </p>
                            </h4>
                        </center>
                    </div>

                    <div class="col-md-6">
                        <company-header-details
                            :company_details="company_details"
                        />
                    </div>
                </div>

                <div />
                <div
                    style="
                        margin-top: 2rem;
                        border: 1px solid black;
                        margin-right: 2rem;
                        margin-left: 2rem;
                    "
                ></div>
                <div :style="dynamicStyle"></div>
                <div class="row">
                    <div class="col-md-6">
                        <supplier-header-details
                            :supplier_details="supplier_details"
                        />
                    </div>
                </div>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-12 mt-2 table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>
                                        <b class="title_2">Item Name</b>
                                    </td>

                                    <td><b class="title_2">Qty</b></td>
                                    <td><b class="title_2">Price</b></td>
                                    <td><b class="title_2">Tax</b></td>
                                    <td><b class="title_2">Total</b></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, i) in po_details" :key="i">
                                    <td class="item">
                                        <span>{{ data.stock.name }}</span>
                                    </td>
                                    <td class="item">
                                        {{ data.qty }}
                                    </td>

                                    <td class="item">
                                        {{ cashRounded(data.unit_price) }}
                                    </td>

                                    <td class="item">
                                        {{ cashRounded(data.taxes) }}
                                    </td>
                                    <td class="item">
                                        {{ cashRounded(data.sub_total) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td><b>Sub Total</b></td>
                                    <td>
                                        <b>{{
                                            cashRounded(
                                                total_amount - total_vat
                                            )
                                        }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td><b>Total VAT</b></td>
                                    <td>
                                        <b>{{ cashRounded(total_vat) }}</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="3"></td>

                                    <td><b>Total Amount</b></td>
                                    <td c>
                                        <b>{{ cashRounded(total_amount) }}</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div
                    class="row "
                    style="
                        margin-top: 8rem;
                       
                      
                        margin-right: 2rem;
                        margin-left: 2rem;
                    "
                >
                    <div class="col-md-12">
                        Approved
                        By________________________________________signature______________________________date________________________________
                    </div>
                </div>
                <div
                    class="row"
                    style=" bottom: 6rem; width: 100%"
                >
                    <div class="col-md-12">
                        <div
                            style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                margin-right: 2rem;
                                margin-left: 2rem;
                            "
                        ></div>
                        <div :style="dynamicStyle"></div>
                    </div>
                </div>
                <div
                    class="row"
                    style=" bottom: 1rem; width: 90%"
                >
                    <div class="col-md-12">
                        <div>
                            <p
                                style="
                                    display: inline-block;
                                    margin-left: 3rem;
                                    float: left;
                                "
                            >
                                Generated By:
                                {{ po_details[0].user.name }}
                            </p>

                            <!-- <p style="margin-left: 3rem; float: right">
                                <b>Thank You For Your Business!</b>
                            </p> -->
                            <br />
                            <!-- <p style="margin-left: 3rem; float: right">
                             Website: www.greenshieldmania.com
                            </p> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CompanyHeaderDetails from "./CompanyHeaderDetails.vue";
import SupplierHeaderDetails from "./SupplierHeaderDetails.vue";

export default {
    components: { CompanyHeaderDetails, SupplierHeaderDetails },
    props: ["po_details"],
    data() {
        return {
            logo: null,
            company_details: this.po_details[0].branch,
            supplier_details: this.po_details[0].supplier,
            logo: null,
            total_amount: 0,
            total_vat: 0,
            publicPath: process.env.BASE_URL,
            borderColor: "#0000",
            imageWidth: this.$store.state.branch.logo_width,
            imageHeight: this.$store.state.branch.logo_height,
        };
    },
    created() {
        this.logo = this.$store.state.branch.img_url;
        this.borderColor = this.$store.state.branch.strip_color;
        //this.$store.state.branch.img_url
        const cssPath = window.location.origin + this.$route.path;
        this.total_amount = this.po_details.reduce(function (sum, val) {
            return sum + val.sub_total;
        }, 0);
        this.total_vat = this.po_details.reduce(function (sum, val) {
            return sum + val.taxes;
        }, 0);
    },
    computed: {
        dynamicStyle() {
            return {
                marginTop: "1rem",
                marginBottom: "1rem",
                border: `1px solid ${this.borderColor}`,
                marginRight: "2rem",
                marginLeft: "2rem",
            };
        },
    },
    methods: {
        printBill: function () {
            const contentToPrint = this.$refs.contentToPrint;

            // Create a new window or iframe
            const printWindow = window.open("", "", "");

            // Write the content of the target div to the new window or iframe
            printWindow.document.open();
            printWindow.document.write(`
      <html>
        <head>
          <title>Print</title>
         <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
      
           
           <style>
       

 .top-table  table  tr, .top-table  table  tr td{
  border: 1px solid;

}
.table-title {
padding-left: 10px;padding-right: 10px;
}
.hr-line{
    height:8px;
    color:black;
}
.table {
max-height:100px !important;
    padding-bottom: 10rem; /* Adjust this value to match the height of your bottom strip */
}



           </style>
        </head>
        <body>
          ${contentToPrint.innerHTML}
        </body>
      </html>`);
            printWindow.document.close();

            // Print the new window or iframe
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        },
    },
};
</script>
<style scoped>
/* #print_section {
    visibility: hidden;
} */
.border {
    border: 3px solid black !important;
}

/* .block-disp {
    display: block;
} */
.table-container {
    padding-bottom: 6rem; /* Adjust this value to match the height of your bottom strip */
}

.table-container .table tr {
    page-break-inside: avoid;
}

/* Styling for the bottom strip */
.bottom-strip {
    display: none;
}

/* Use @media print to control the display on the last page */


</style>
