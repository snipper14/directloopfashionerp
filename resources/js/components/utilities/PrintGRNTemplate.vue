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
                                GOODS RECEIVED NOTE (GRN)
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
                                Delivery No: {{ grn_details[0].delivery_no }}
                                <br />
                                Order No: {{ grn_details[0].order_no }}
                                <br />
                                <br />
                                <p v-if="grn_details[0].cu_invoice_no">
                                    CU Invoice No:
                                    {{ grn_details[0].cu_invoice_no }}
                                </p>
                                <p>
                                    Delivery Date:
                                    {{
                                        formatDateToDDMMYYYY(
                                            grn_details[0].received_date
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
                            :header="'From'"
                        />
                    </div>
                </div>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-12 mt-2">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>
                                        <b class="title_2">Item Code</b>
                                    </td>
                                    <td>
                                        <b class="title_2">Item Name</b>
                                    </td>

                                   
                                    <td><b class="title_2">Unit</b></td>
                                    <td><b class="title_2">Qty Ordered</b></td>
                                    <td><b class="title_2">Qty Shipped</b></td>
                                    <td><b class="title_2">Unit Price</b></td>
                                    <td><b class="title_2">Discount</b></td>
                                    <td><b class="title_2">Amount</b></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, i) in grn_details" :key="i">
                                    <td class="item">
                                        <span>{{ data.stock.code }}</span>
                                    </td>
                                    <td class="item">
                                        <span>{{ data.stock.name }}</span>
                                    </td>
                                    <td class="item">
                                        <span>{{ data.stock.unit.name }}</span>
                                    </td>
                                    <td class="item">
                                        {{ data.qty_ordered }}
                                    </td>
                                    <td class="item">
                                        {{ data.qty_delivered }}
                                    </td>
                                    <td class="item">
                                        {{ cashRounded(data.unit_price) }}
                                    </td>
                                    <td>{{ cashRounded(data.discount_amount) }}</td>
                                    <td class="item">
                                        {{ cashRounded(data.sub_total) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6"></td>
                                    <td><b>Sub Total</b></td>
                                    <td>
                                        <b>{{
                                            cashRounded(
                                                total_amount - total_vat - total_discount
                                            )
                                        }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6"></td>
                                    <td><b>Total VAT</b></td>
                                    <td>
                                        <b>{{ cashRounded(total_vat) }}</b>
                                    </td>
                                </tr>

  <tr>
                                    <td colspan="6"></td>
                                    <td><b>Total Discount</b></td>
                                    <td>
                                        <b>{{ cashRounded(total_discount) }}</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="6"></td>

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
                    class="row"
                    style="
                        margin-top: 4rem;
                     
                        bottom: 4rem;
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
                    style="position: fixed; bottom: 1rem; width: 90%"
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
                                {{ grn_details[0].user.name }}
                            </p>

                            <br />
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
    props: ["grn_details"],
    data() {
        return {
            logo: null,
            company_details: this.grn_details[0].branch,
            supplier_details: this.grn_details[0].supplier,
            logo: null,
            total_amount: 0,
            total_discount:0,
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
        this.total_amount = this.grn_details.reduce(function (sum, val) {
            return sum + val.sub_total;
        }, 0);
        this.total_vat = this.grn_details.reduce(function (sum, val) {
            return sum + val.tax_amount;
        }, 0);
        this.total_discount = this.grn_details.reduce(function (sum, val) {
            return sum + val.discount_amount;
        }, 0);
    },
    computed: {
        dynamicStyle() {
            return {
                marginTop: "2rem",
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
</style>
