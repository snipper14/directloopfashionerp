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
                    <div class="row">
                        <div class="col-md-4">
                            <h3
                                style="
                                    text-align: center;
                                    font-weight: bold;
                                    margin-top: 1rem;
                                "
                            >
                             {{company_details.business_name}}   Customer Statement
                            </h3>
                            <br />
                            <updated-customer-info-header
                                :customer_info="
                                    print_statement_data.data[0].customer
                                "
                            />
                        </div>
                        <div class="col-md-4">
                            <center>
                                <img
                                    :src="logo"
                                    alt=""
                                    :width="imageWidth"
                                    :height="imageHeight"
                                />
                            </center>
                            <center></center>
                        </div>
                        <div class="col-md-4">
                            <h6
                                style="
                                    margin-left: 4rem;

                                    padding: 10px;
                                "
                            >
                                <br />
                                <p>
                                    <br />
                                    Printed On:
                                    {{ getCurrentDate() }} <br>
                                    <span v-if="from">From: {{from}} To {{to}}  </span>  
                                </p>
                              
                            </h6>
                        </div>
                    </div>

                    <div />

                    <div :style="dynamicStyle"></div>
                    <div class="row">
                        <div class="col-md-6"></div>
                    </div>
                    <div
                        class="row"
                        style="margin-left: 8px; margin-right: 8px"
                    >
                        <div class="col-md-12 mt-2 table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>
                                            <b class="title_2">Date</b>
                                        </td>

                                        <td><b class="title_2">Ref No.</b></td>
                                        <td><b class="title_2">Type</b></td>
                                        <td>
                                            <b class="title_2">Description</b>
                                        </td>
                                        <td><b class="title_2">Debit</b></td>
                                        <td><b class="title_2">Credit</b></td>
                                        <td><b class="title_2">Balance</b></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            data, i
                                        ) in print_statement_data.data"
                                        :key="i"
                                    >
                                        <td class="item">
                                            {{ data.entry_date }}
                                        </td>
                                        <td class="item">
                                            {{ data.ref }}
                                        </td>
                                        <td class="item">
                                            {{ data.type }}
                                        </td>
                                        <td class="item">
                                            {{ data.description }}
                                        </td>

                                        <td class="item">
                                            {{ cashRounded(data.debit) }}
                                        </td>

                                        <td class="item">
                                            {{ cashRounded(data.credit) }}
                                        </td>
                                        <td class="item">
                                            {{ cashRounded(data.balance) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div
                        style="margin-left: 8px; margin-right: 8px"
                        class="row table-container"
                    >
                        <table class="table"  style="">
                            <tr>
                                <td> <b class="title_2">180 days </b></td>
                                <td> <b class="title_2">150 days </b></td>
                                <td> <b class="title_2">120 days </b></td>
                                <td> <b class="title_2">90 days </b></td>
                                <td> <b class="title_2">60 days </b></td>
                                <td> <b class="title_2">30 days </b></td>
                                <td> <b class="title_2">Current </b></td>
                            </tr>
                            <tr>
                                <td class="item">
                                    
                                    {{
                                        cashRounded(
                                            print_statement_data.one_80,
                                            2
                                        )
                                    }}
                                </td>
                                <td class="item">
                                    
                                    {{
                                        cashRounded(
                                            print_statement_data.one_50,
                                            2
                                        )
                                    }}
                                </td>
                                <td class="item">
                                    
                                    {{
                                        cashRounded(
                                            print_statement_data.one_20,
                                            2
                                        )
                                    }}
                                </td>
                                <td class="item">
                                    
                                    {{
                                        cashRounded(
                                            print_statement_data.ninety,
                                            2
                                        )
                                    }}
                                </td>
                                <td class="item">
                                    
                                    {{
                                        cashRounded(
                                            print_statement_data.sixty,
                                            2
                                        )
                                    }}
                                </td>
                                <td class="item">
                                    
                                    {{
                                        cashRounded(
                                            print_statement_data.thirty,
                                            2
                                        )
                                    }}
                                </td>
                                <td class="item">
                                    
                                    {{
                                        cashRounded(
                                            print_statement_data.current,
                                            2
                                        )
                                    }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div
                        class="row"
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
                                    {{ print_statement_data.data[0].user.name }}
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
                            <company-footer
                                :company_details="company_details"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CompanyFooter from "../../../utilities/CompanyFooter.vue";
import UpdatedCustomerInfoHeader from "../../../utilities/UpdatedCustomerInfoHeader.vue";

export default {
    components: {
        UpdatedCustomerInfoHeader,
        CompanyFooter,
    },
    props: ["print_statement_data",'from','to'],
    data() {
        return {
            logo: null,
            company_details: this.print_statement_data.data[0].branch,

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
        console.log("logo path" + this.logo);

        this.borderColor = this.$store.state.branch.strip_color;
        //this.$store.state.branch.img_url
        const cssPath = window.location.origin + this.$route.path;
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
       .table td.item {
    padding-top: 0.4rem !important;
    padding-bottom: 0.4rem !important;
}

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
