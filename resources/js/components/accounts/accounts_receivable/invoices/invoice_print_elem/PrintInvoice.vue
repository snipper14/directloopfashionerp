<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <center  class="top-image">

                    <img v-if="logo_type=='image'" :src="logo" alt="" height="100px">
                    <span v-else>
                          <h1  ><b :style="dynamicStyle">{{invoice_details[0].branch.business_name}}</b></h1>
                
                    <b :style="dynamicStyle">REDEFINING HEALTHCARE</b>
                    </span>
                     <div
                            style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                margin-right: 2rem;
                                margin-left: 2rem;
                            "
                        ></div>
                    <div :style="dynamicStripStyle"></div>
                    <h3 style="margin-top: 2rem"><b>INVOICE</b></h3>
                </center>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-6">
                        <client-info :invoice_details="invoice_details" />
                    </div>

                    <div class="col-md-6">
                        <vendor-info :invoice_details="invoice_details" />
                    </div>
                </div>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-10 top-table">
                        <table class="">
                            <tr>
                                <td class="table-title">
                                    <p v-if="invoice_details[0].invoice_no">
                                        Invoice No:
                                        {{ invoice_details[0].invoice_no }}
                                    </p>
                                </td>
                                <td class="table-title">
                                    <p v-if="invoice_details[0].invoice_date">
                                        Invoice Date:
                                        {{ invoice_details[0].invoice_date }}
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td class="table-title">
                                    <p v-if="invoice_details[0].notes">
                                        CU Invoice No.
                                        {{ invoice_details[0].notes }}
                                    </p>
                                </td>
                                <td class="table-title">
                                    <p v-if="invoice_details[0].d_note">
                                        Delivery Note No..
                                        {{ invoice_details[0].d_note }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-12 mt-2">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td><b class="title_2">Code</b></td>
                                    <td><b class="title_2">Item Name</b></td>

                                    <td><b class="title_2">Qty</b></td>
                                    <td><b class="title_2">Price</b></td>
                                     <td v-if="print_discount_amount>0"><b class="title_2">Discount</b></td>
                                    <td><b class="title_2">Total</b></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(data, i) in invoice_details"
                                    :key="i"
                                >
                                    <td class="item">{{ data.stock.code }}</td>
                                    <td class="item">
                                        {{ data.stock.product_name }}
                                    </td>
                                    <td class="item">
                                        {{ data.qty }}
                                    </td>
                                    
                                    <td class="item">
                                        {{ cashRounded(data.price) }}
                                    </td>
                                    <td v-if="print_discount_amount>0" class="item">
                                        -{{ cashRounded(data.discount) }}
                                    </td>
                                    <td class="item">
                                        {{ cashRounded(data.row_total) }}
                                    </td>
                                </tr>
                                <tr>
                                     <td v-if="print_discount_amount>0" colspan="4"></td>
                                     <td v-else  colspan="3"></td>
                                    <td ><b>Sub Total</b></td>
                                    <td >
                                        <b>{{
                                            cashRounded(print_invoice_amount-print_tax_amount)
                                        }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    
                                     <td v-if="print_discount_amount>0" colspan="4"></td>
                                     <td v-else  colspan="3"></td>
                                    <td ><b>Total VAT</b></td>
                                    <td >
                                        <b>{{
                                            cashRounded(print_tax_amount)
                                        }}</b>
                                    </td>
                                </tr>
                                 <tr v-if="print_discount_amount>0">
                                     <td colspan="4"></td>
                                    <td><b>Total Discount</b></td>
                                    <td >
                                        <b>{{
                                            cashRounded(print_discount_amount)
                                        }}</b>
                                    </td>
                                </tr>
                                <tr>
                                     <td v-if="print_discount_amount>0" colspan="4"></td>
                                     <td v-else  colspan="3"></td>
                                    
                                    <td ><b>Total Amount</b></td>
                                    <td c>
                                        <b>{{
                                            cashRounded(print_invoice_amount)
                                        }}</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row ml-3">
                         
                        <div  style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                padding: 10px;
                            ">
                            <p> Generated By: {{  invoice_details[0].user.name }}</p>
                            <p>
                                All cheques payable to
                                {{ invoice_details[0].branch.business_name }}
                            </p>
                            <br />
                            <p>
                                Signed___________________________Date____________________________
                            </p></div>
                     
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ClientInfo from "./ClientInfo.vue";
import VendorInfo from "./VendorInfo.vue";
export default {
    components: { ClientInfo, VendorInfo },
    props: ["invoice_details", "print_tax_amount", "print_invoice_amount",'print_discount_amount','logo'],
    data() {
        return {
            borderColor:"",
            logo_type:"name",
            logo:null,
            publicPath: process.env.BASE_URL,
        };
    },
    created() {
 this.borderColor = this.$store.state.branch.strip_color;
        this.logo_type=this.$store.state.branch.logo_type
          const cssPath =  window.location.origin + this.$route.path ;
     console.log(cssPath)
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
       
.top-image{
margin-top:28px;
}
 .top-table  table  tr, .top-table  table  tr td{
  border: 1px solid;

}
.table-title {
padding-left: 10px;padding-right: 10px;
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
