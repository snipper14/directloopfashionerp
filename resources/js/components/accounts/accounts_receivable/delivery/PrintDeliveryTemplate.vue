<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <div
                    style="
                        width: 92%;
                        margin-top: 2rem;
                        margin-left: 4%;
                        margin-right: 4%;
                    "
                >
                    <!-- start header -->
                    <div class="row">
                        <div class="col-md-3">
                            <h3
                                style="
                                    text-align: center;
                                    font-weight: bold;
                                    margin-top: 1rem;
                                "
                            >
                                DELIVERY NOTE
                            </h3>
                            <br />
                            <updated-customer-info-header
                                :customer_info="delivery_details[0].customer"
                            />
                        </div>
                        <div class="col-md-4">
                            <center>
                                <img :src="logo" alt="" height="100px" />
                            </center>
                        </div>
                        <div class="col-md-3">
                            <h6 style="margin-left: 4rem">
                                Delivery No:
                                {{ delivery_details[0].delivery_no }}
                                <br />
                                Invoice No:
                                {{ delivery_details[0].invoice_no }}
                                <br />
                                <p v-if="delivery_details[0].invoice_date">
                                    Generated on:
                                    {{ delivery_details[0].delivery_date }}
                                </p>

                                <br />
                            </h6>
                        </div>
                    </div>
                    <!-- end header -->

                    <div :style="dynamicStripStyle"></div>

                    <div
                        class="row"
                        style="margin-left: 8px; margin-right: 8px"
                    >
                        <div class="col-md-12 mt-2">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td><b class="title_2">Code</b></td>
                                        <td>
                                            <b class="title_2">Item Name</b>
                                        </td>

                                        <td><b class="title_2">Qty</b></td>
                                        <td><b class="title_2">Price</b></td>
                                        <td><b class="title_2">Amount</b></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(data, i) in delivery_details"
                                        :key="i"
                                    >
                                        <td class="item">
                                            {{ data.stock.code }}
                                        </td>
                                        <td class="item">
                                            {{ data.stock.product_name }}
                                        </td>
                                        <td class="item">
                                            {{ data.qty }}
                                        </td>
                                        <td class="item">
                                            {{
                                                data.invoice
                                                    ? cashFormatter(
                                                          data.invoice.price
                                                      )
                                                    : 0
                                            }}
                                        </td>
                                        <td class="item">
                                            {{
                                                data.invoice
                                                    ? cashFormatter(
                                                          data.invoice.price *
                                                              data.qty
                                                      )
                                                    : 0
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><b>TOTAL DELIVERY</b></td>
                                        <td>
                                            <b>{{
                                                cashFormatter(totalAmount)
                                            }}</b>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="row"
                            style="
                                margin-top: 4rem;

                                margin-right: 2rem;
                                margin-left: 2rem;
                            "
                        >
                            <div class="col-md-12">
                                Approved
                                By________________________________________signature______________________________date________________________________
                            </div>
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
                                <div :style="dynamicStripStyle"></div>
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
                                        {{ delivery_details[0].user.name }}
                                    </p>

                                    <br />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <company-footer
                                    :company_details="
                                        delivery_details[0].branch
                                    "
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CompanyFooter from "../../../utilities/CompanyFooter.vue";
import UpdatedCustomerInfoHeader from "../invoices/invoice_print_elem/UpdatedCustomerInfoHeader.vue";

export default {
    components: {
        UpdatedCustomerInfoHeader,
        CompanyFooter,
    },
    props: ["delivery_details", "logo"],
    data() {
        return {
            borderColor: "",
            logo_type: "name",
            logo: null,
            company_details: this.delivery_details[0].branch,
            publicPath: process.env.BASE_URL,
            imageWidth: this.$store.state.branch.logo_width,
            imageHeight: this.$store.state.branch.logo_height,
        };
    },
    created() {
        this.borderColor = this.$store.state.branch.strip_color;
        this.logo = this.$store.state.branch.img_url;
        this.logo_type = this.$store.state.branch.logo_type;
        const cssPath = window.location.origin + this.$route.path;
    },
    computed: {
        dynamicStripStyle() {
            return {
                marginTop: "1rem",
                marginBottom: "1rem",
                border: `1px solid ${this.borderColor}`,
                marginRight: "2rem",
                marginLeft: "2rem",
            };
        },
        dynamicStyle() {
            return {
                color: ` ${this.$store.state.branch.strip_color}`,
            };
        },
        totalAmount() {
            if (!Array.isArray(this.delivery_details)) return 0; // Ensure it's an array

            return this.delivery_details.reduce((sum, item) => {
                let price = item?.invoice?.price || 0; // Use optional chaining to avoid errors
                let qty = item?.qty || 0;
                return sum + price * qty;
            }, 0);
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
           .icons{
           height:24px;
           width:24px;
           }
             .table td.item {
    padding-top: 0.4rem !important;
    padding-bottom: 0.4rem !important;
}
       .item-code{
       max-width: 20%;overflow: hidden}
.top-image{
margin-top:28px;
}
 .top-table  table  tr, .top-table  table  tr td{
  border: 1px solid;

}
.table-title {
padding-left: 10px;padding-right: 10px;
}
.footer-item{
padding-left:2rem;
}
.payitems{
padding-left:1rem;
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

            // window.print();
            // printJS("print_section", "html");
            // this.value = value;
            // printJS({
            //     printable: "print_section",
            //     type: "html",
            //     style: ".print-border { border: 1px solid black; padding: 10px; width:80%;}",
            // });
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
