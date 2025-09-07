<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-10">
                        <div>
                            <div
                                style="
                                    margin-top: 2rem;
                                    
                                    padding: 10px;
                                "
                            >
                                <h3 style="margin-top: 2rem">
                                    <b
                                        >{{
                                            print_data[0].branch.business_name
                                        }}
                                        Stock Transfer Document</b
                                    >
                                </h3>

                                <span
                                    ><b
                                        >Transfer From:
                                        {{ print_data[0].branch.branch }}</b
                                    ></span
                                ><br />

                                <span v-if="print_data[0].branch.branch">
                                    <span>
                                        Transfer To
                                        {{
                                            print_data[0].branch_to.branch
                                        }} </span
                                    ><br />
                                </span>
                            </div>
                        </div>
                    </div>

                   
                </div>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-10 top-table">
                        <table class="">
                            <tr>
                                <td class="table-title">
                                    <p>
                                        Transfer ID:
                                        {{ print_data[0].transfer_code }}
                                    </p>
                                </td>
                                <td class="table-title">
                                    <p>
                                        Transfer Date:
                                        {{ print_data[0].transfer_date }}
                                    </p>
                                </td>
                                <td>
                                    <p>
                                        Location
                                        {{
                                            print_data[0].department.department
                                        }}
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td class="table-title">
                                    <p v-if="print_data[0].reference_code">
                                        Reference No.
                                        {{ print_data[0].reference_code }}
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
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, i) in print_data" :key="i" class="border-body">
                                    <td class="item">{{ data.stock.code }}</td>
                                    <td class="item">
                                        {{ data.stock.product_name }}
                                    </td>
                                 
                                    <td class="item">
                                        {{ data.qty }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row ml-3 mr-3">
                        <p v-if="print_data[0].note">
                            {{ print_data[0].note }}
                        </p>
                    </div>
                    <div class="row ml-3">
                        <div
                            style="
                                margin-top: 2rem;

                                padding: 10px;
                            "
                        >
                            <p>Generated By: {{ print_data[0].user.name }}</p>

                            <br />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            Signed_______________________Date________________
                        </div>
                        <div class="col-md-4 d-flex flex-row">
                            Checked by_____________________________________
                        </div>
                        <div class="col-md-4 d-flex flex-row">
                            Authorized by___________________________________
                        </div>
                    </div>
                    <div class="row" style="margin-top: 2rem">
                        <div class="col-md-6 d-flex flex-row">
                            Accounts____________________________________________
                        </div>
                        <div class="col-md-6 d-flex flex-row">
                            Security____________________________________________
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    components: {},
    props: ["print_data"],
    data() {
        return {
            logo: null,
            publicPath: process.env.BASE_URL,
        };
    },
    mounted() {
        //this.$store.state.branch.img_url
        const cssPath = window.location.origin + this.$route.path;
        console.log(cssPath);
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
       
.border-body td{

 padding-top:0;
 padding-bottom:0;
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
