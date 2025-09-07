<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <center>
                    <img :src="branch.img_url" alt="" height="100px" />
                    <h3 style="margin-top: 2rem"><b>Medical Record</b></h3>
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
                                User: {{ details_data.user.name }} </span
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
                            <span>
                                <span>
                                    Patients Name:
                                    {{
                                        details_data.customer.company_name
                                    }} </span
                                ><br />
                            </span>
                            <span v-if="details_data.customer.company_phone">
                                <span>
                                    Patients Contacts:
                                    {{
                                        details_data.customer.company_phone
                                    }} </span
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
                                    v-if="
                                        details_data.healthcare_provider_report
                                    "
                                    class="small-tr"
                                >
                                    <td>Health care Provider Report</td>

                                    <td>
                                        {{
                                            details_data.healthcare_provider_report
                                        }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.symptoms_complaints"
                                    class="small-tr"
                                >
                                    <td>Symptoms /Complaints</td>

                                    <td>
                                        {{ details_data.symptoms_complaints }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="
                                        details_data.pre_existing_medical_conditions
                                    "
                                    class="small-tr"
                                >
                                    <td>Pre Existing Medical Conditions</td>

                                    <td>
                                        {{
                                            details_data.pre_existing_medical_conditions
                                        }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.allergies"
                                    class="small-tr"
                                >
                                    <td>Allergies</td>

                                    <td>{{ details_data.allergies }}</td>
                                </tr>
                                <tr
                                    v-if="
                                        details_data.past_surgeries_medical_procedures
                                    "
                                    class="small-tr"
                                >
                                    <td>Past Sugeries/ Medical Procedures</td>

                                    <td>
                                        {{
                                            details_data.past_surgeries_medical_procedures
                                        }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.family_medical_history"
                                    class="small-tr"
                                >
                                    <td>Family Medical History</td>

                                    <td>
                                        {{
                                            details_data.family_medical_history
                                        }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.current_medication"
                                    class="small-tr"
                                >
                                    <td>Current Medication</td>

                                    <td>
                                        {{ details_data.current_medication }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.date_time_appointments"
                                    class="small-tr"
                                >
                                    <td>Next Appointment</td>

                                    <td>
                                        {{
                                            details_data.date_time_appointments
                                        }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.visiting_health_provider"
                                    class="small-tr"
                                >
                                    <td>Visiting Health Provider</td>

                                    <td>
                                        {{
                                            details_data.visiting_health_provider
                                        }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.purpose_of_visit"
                                    class="small-tr"
                                >
                                    <td>Purpose of Visit</td>

                                    <td>
                                        {{ details_data.purpose_of_visit }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="details_data.treatment_plans"
                                    class="small-tr"
                                >
                                    <td>Treatment Plans</td>

                                    <td>
                                        {{ details_data.treatment_plans }}
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
    props: ["details_data"],
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
