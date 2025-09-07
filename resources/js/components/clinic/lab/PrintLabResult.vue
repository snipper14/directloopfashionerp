<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <center>
                    <img :src="branch.img_url" alt="" height="100px" />
                    <h3 style="margin-top: 2rem"><b>Lab Consumables</b></h3>
                </center>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-7">
                        <div
                            style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                padding: 10px;
                            "
                        >
                            <h3>
                                <b>{{ branch.business_name }}</b>
                            </h3>
                            <span
                                ><b>{{ branch.business_name }}</b></span
                            ><br />
                            <span v-if="branch.branch">
                                <span> {{ branch.branch }} </span><br />
                            </span>
                            <span v-if="branch.postal_address">
                                <span> {{ branch.postal_address }} </span><br />
                            </span>
                            <span v-if="branch.address">
                                <span> {{ branch.address }} </span><br />
                            </span>

                            <span v-if="branch.kra_pin">
                                <span> Document PIN: {{ branch.kra_pin }} </span
                                ><br />
                            </span>
                            <span>
                                <span>
                                    Lab Tech: {{ details_data.lab_tech }} </span
                                ><br />
                            </span>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div
                            style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                padding: 10px;
                            "
                        >
                            <span v-if="patient_info.company_name">
                                <span>
                                    Patients Name:
                                    {{ patient_info.company_name }} </span
                                ><br />
                            </span>
                            <span v-if="patient_info.company_phone">
                                <span>
                                    Patients Contacts:
                                    {{ patient_info.company_phone }} </span
                                ><br />
                            </span>

                            <span>
                                Printed On:
                                {{ getDateTime() }} </span
                            ><br />
                            <span>
                                Code :
                                {{ details_data.entry_code }} </span
                            ><br />
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-12 mt-2">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <tbody>
                                <tr
                                    v-if="details_data.blood_test"
                                    class="small-tr"
                                >
                                    <td>Blood Test</td>

                                    <td>{{ details_data.blood_test }}</td>
                                </tr>
                                <tr
                                    v-if="details_data.blood_pressure"
                                    class="small-tr"
                                >
                                    <td>Blood Pressure</td>

                                    <td>{{ details_data.blood_pressure }}</td>
                                </tr>
                                <tr
                                    v-if="details_data.heart_rate"
                                    class="small-tr"
                                >
                                    <td>Heart Rate</td>

                                    <td>{{ details_data.heart_rate }}</td>
                                </tr>
                                <tr
                                    v-if="details_data.respiratory_rate"
                                    class="small-tr"
                                >
                                    <td>Respiratory Rate</td>

                                    <td>{{ details_data.respiratory_rate }}</td>
                                </tr>
                                <tr
                                    v-if="details_data.temperature"
                                    class="small-tr"
                                >
                                    <td>Temprature</td>

                                    <td>{{ details_data.temperature }}</td>
                                </tr>
                                <tr
                                    v-if="details_data.height_and_weight"
                                    class="small-tr"
                                >
                                    <td>Height & Weight</td>

                                    <td>
                                        {{ details_data.height_and_weight }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row ml-3">
                        <div
                            style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                padding: 10px;
                            "
                        >
                            <p>Generated By: {{ user.name }}</p>

                            <br />
                            <p>
                                Signed___________________________Date____________________________
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VendorInfo from "../../accounts/accounts_receivable/invoices/invoice_print_elem/VendorInfo.vue";

export default {
    components: { VendorInfo },
    props: ["details_data", "patient_info"],
    data() {
        return {
            branch: {},
            user: {},
            logo: null,
            publicPath: process.env.BASE_URL,
        };
    },
    created() {
        console.log(JSON.stringify(this.details_data));
        this.branch = this.$store.state.branch;
        this.user = this.$store.state.user;
        //this.$store.state.branch.img_url
        const cssPath = window.location.origin + this.$route.path;
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
