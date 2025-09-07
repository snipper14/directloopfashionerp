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
                                SICK-OFF SHEET
                            </h3>
                        </center>
                    </div>
                </div>
                <div class="row" style="margin-left: 8px; margin-right: 8px">
                    <div class="col-md-5">
                        <center>
                            <img :src="logo" alt="" width="200px" />

                            <h4>
                                Reg No:
                                {{ print_data.entry_code }}
                                <br />
                                <p v-if="print_data.currentdate">
                                    Date:
                                    {{
                                        formatDateToDDMMYYYY(
                                            print_data.currentdate
                                        )
                                    }}
                                </p>
                            </h4>
                        </center>
                    </div>

                    <div class="col-md-6">
                        <div
                            style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                padding: 10px;
                            "
                        >
                            <span
                                ><b>{{ branch_info.business_name }}</b></span
                            ><br />
                            <span v-if="branch_info.branch">
                                <span> {{ branch_info.branch }} </span><br />
                            </span>
                            <span v-if="branch_info.postal_address">
                                <span> {{ branch_info.postal_address }} </span
                                ><br />
                            </span>
                            <span v-if="branch_info.address">
                                <span> {{ branch_info.address }} </span><br />
                            </span>

                            <span v-if="branch_info.email">
                                <span>
                                    Email:
                                    {{ branch_info.email }} </span
                                ><br />
                            </span>
                            <span v-if="branch_info.phone_no">
                                <span>
                                    Tel:
                                    {{ branch_info.phone_no }} </span
                                ><br />
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <center>
                        <h4
                            style="
                                color: #a64240;
                                text-align: center;
                                margin-top: 2rem;
                            "
                        >
                            Clinic|Laboratory|Pharmacy
                        </h4>
                    </center>
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
                <div
                    style="
                        margin-top: 2rem;
                        margin-bottom: 1rem;
                        border: 1px solid #a64240;
                        margin-right: 2rem;
                        margin-left: 2rem;
                    "
                ></div>
                <div class="row">
                    <div
                        class="col-md-12"
                        style="
                            margin-right: 2rem;
                            margin-left: 2rem;
                            margin-top: 2rem;
                        "
                    ></div>
                </div>
                <div class="row" style="margin-right: 2rem; margin-left: 2rem">
                    <table>
                        <tr>
                            <td>
                                <h4><b> Patients Name: </b></h4>
                            </td>
                            <td>
                                <h4>
                                    <b> {{ print_data.patient_name }}</b>
                                </h4>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <h4>Age:</h4>
                            </td>
                            <td>
                                <h4>
                                    {{ print_data.age }}
                                </h4>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h4>Gender:</h4>
                            </td>
                            <td>
                                <h4>
                                    {{ print_data.gender }}
                                </h4>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <h4>Date:</h4>
                            </td>
                            <td>
                                <h4>
                                    {{ print_data.currentdate }}
                                </h4>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h4>Sick off Days:</h4>
                            </td>
                            <td>
                                <h4>
                                    {{ print_data.sickoff_days }}
                                </h4>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h4>
                                    From: {{ print_data.startdate }} To:
                                    {{ print_data.enddate }}
                                </h4>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h4>Doctors Name:</h4>
                            </td>
                            <td>
                                <h4>
                                    {{ print_data.doctors_name }}
                                </h4>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div class="row" style="margin-right: 2rem; margin-left: 2rem">
                    <h6>
                       <b>Doctors Report:</b> 
                        {{ print_data.doctors_report }}
                    </h6>
                </div>
                <div
                    class="row"
                    style="position: fixed; bottom: 6rem; width: 100%"
                >
                    <div
                        class="col-md-12"
                        style="
                            margin-top: 2rem;

                            margin-right: 2rem;
                            margin-left: 2rem;
                        "
                    >
                        
                        Signature________________________________________________________Stamp______________________________
                    </div>
                    <div class="col-md-12">
                        <div
                            style="
                                margin-top: 2rem;
                                border: 1px solid black;
                                margin-right: 2rem;
                                margin-left: 2rem;
                            "
                        ></div>
                        <div
                            style="
                                margin-top: 2rem;
                                margin-bottom: 1rem;
                                border: 1px solid #a64240;
                                margin-right: 2rem;
                                margin-left: 2rem;
                            "
                        ></div>
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
                                {{ user.name }}
                            </p>

                            <p style="margin-left: 3rem; float: right">
                                <b>Get Well Soon!</b>
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
export default {
    components: {},
    props: ["print_data", "logo", "branch_info", "user"],
    data() {
        return {
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
