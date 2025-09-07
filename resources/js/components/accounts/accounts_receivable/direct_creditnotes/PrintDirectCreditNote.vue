<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <center>

                    <img :src="logo" alt="" height="100px"><h3 style="margin-top: 2rem"><b>CREDIT NOTE</b></h3>
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
                                    <p v-if="invoice_details[0].credit_no">
                                        Credit Note No:
                                        {{ invoice_details[0].credit_no }}
                                    </p>
                                </td>
                                <td class="table-title">
                                    <p v-if="invoice_details[0].credit_date">
                                        Credit Date:
                                        {{ invoice_details[0].credit_date }}
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td class="table-title">
                                    <p v-if="invoice_details[0].notes">
                                        
                                        {{ invoice_details[0].notes }}
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
                                    
                                    <td class="item">
                                        {{ cashRounded(data.line_total) }}
                                    </td>
                                </tr>
                               
                                <tr>
                                    
                                    
                                     <td   colspan="3"></td>
                                    <td ><b>Total VAT</b></td>
                                    <td >
                                        <b>{{
                                            cashRounded(total_vat_amount)
                                        }}</b>
                                    </td>
                                </tr>
                                
                                <tr>
                                     
                                     <td colspan="3"></td>
                                    
                                    <td ><b>Total Amount</b></td>
                                    <td >
                                        <b>{{
                                            cashRounded(total_credit_amount)
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
import ClientInfo from '../invoices/invoice_print_elem/ClientInfo.vue';
import VendorInfo from '../invoices/invoice_print_elem/VendorInfo.vue';


export default {
    components: { ClientInfo,  VendorInfo },
    props: ["invoice_details", "total_vat_amount", "total_credit_amount",'logo'],
    data() {
        return {
            logo:null,
            publicPath: process.env.BASE_URL,
        };
    },
    mounted() {
        //this.$store.state.branch.img_url
          const cssPath =  window.location.origin + this.$route.path ;
     console.log(cssPath)
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
