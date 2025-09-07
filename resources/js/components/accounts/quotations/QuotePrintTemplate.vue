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
                            {{company_details.business_name}}    QUOTATION
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
                                Quotation No: {{ print_data[0].order_no }}
                                <br />
                                <p>
                                    Date:
                                    {{
                                        formatDateToDDMMYYYY(
                                            print_data[0].order_date
                                        )
                                    }}
                                </p>
                               
                            </h4>
                        </center>
                    </div>

                    <div class="col-md-6">
                        <div class="header-data d-flex flex-column align-items-center">
                        <updated-customer-info-header
                            :customer_info="customer_info"
                        /></div>
                    </div>
                </div>

               
           
                <div :style="dynamicStyle"></div>
                <div class="row">
                    <div class="col-md-6">
                      
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

                                    <td><b class="title_2">Total</b></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, i) in print_data" :key="i">
                                    <td class="item">
                                        <span>{{
                                            data.product_name
                                                ? data.product_name
                                                : data.stock.name
                                        }}</span>
                                    </td>
                                    <td class="item">
                                        {{ data.qty }}
                                    </td>

                                    <td class="item">
                                        {{ cashRounded(data.selling_price) }}
                                    </td>

                                    <td>{{ cashRounded(data.row_total) }}</td>
                                </tr>

                                <tr>
                                    

                                    <td><b>Total Amount</b></td>
                                    <td></td>
                                    <td></td>
                                    <td>
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
                        margin-top: 8rem;

                        margin-right: 2rem;
                        margin-left: 2rem;
                    "
                >
                    <!-- <div class="col-md-12">
                        Approved
                        By________________________________________signature______________________________date________________________________
                    </div> -->
                </div>
                <div class="row" style="bottom: 6rem; width: 100%">
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
                <div class="row" style="bottom: 1rem; width: 90%">
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
                                {{ print_data[0].user.name }}
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
                       <div class="col-md-12">
                           <company-footer :company_details="company_details"/>
                        </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CompanyFooter from '../../utilities/CompanyFooter.vue';
import CompanyHeaderDetails from "../../utilities/CompanyHeaderDetails.vue";
import CustomerHeaderDetails from "../../utilities/CustomerHeaderDetails.vue";
import UpdatedCustomerInfoHeader from "../../utilities/UpdatedCustomerInfoHeader.vue";

export default {
    components: {
        CompanyHeaderDetails,
        CustomerHeaderDetails,
        UpdatedCustomerInfoHeader,
        CompanyFooter,
    },
    props: ["print_data"],
    data() {
        return {
            logo: null,
            company_details: this.print_data[0].branch,
            customer_info: this.print_data[0].customer,
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
        this.total_amount = this.print_data.reduce(function (sum, val) {
            return sum + val.row_total;
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
