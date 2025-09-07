<template>
    <div id="print_section">
        <div class="print-border">
            <div ref="contentToPrint">
                <span v-for="n in loop_aaray" :key="n" :style="spanStyle">
                    <center>
                        <barcode
                            :width="form_data.bar_width"
                            :height="form_data.bar_height"
                            :fontSize="form_data.bar_font_size"
                            textAlign="center"
                            v-bind:value="barcodeValue"
                            :style="barcodeStyle"
                        >
                        </barcode>

                        <b style="font-size: 64px; margin-top:2rem;display: -webkit-box;   -webkit-line-clamp: 3; line-height: 1.0em;  max-height:3.2em;  overflow: hidden; text-overflow: ellipsis;     ">
                           
                            {{ stockObject.label }}
                        </b>
                    </center>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import VueBarcode from "vue-barcode";
export default {
    components: { barcode: VueBarcode },
    props: ["stockObject", "dimensionObj", "qty"],
    data() {
        return {
            value: "https://example.com",
            size: 300,
            publicPath: process.env.BASE_URL,
            barcodeValue: this.stockObject.code,
            loop_aaray: parseInt(this.qty),
            form_data: {
                bar_font_size: this.dimensionObj.bar_font_size,
                bar_height: 600,
                bar_width: 7,
                // wrapper_height: 220,
                // wrapper_width: 195,
            },
            barcodeStyle: {
                marginTop: "100px",

               paddingBottom:'1rem',
                //transform: 'rotate(90deg)', // Rotate the barcode 90 degrees
                transformOrigin: "center center", // Ensure the rotation occurs around the center
            },
        };
    },
    created() {
        //this.form_data = this.dimensionObj;
        //this.$store.state.branch.img_url
        const cssPath = window.location.origin + this.$route.path;
    },
    computed: {
        spanStyle() {
            return {
                 marginRight: "150px",
                display: "block",
                marginBottom: "10px",
           
                padding: "8px",
                marginRight: "4px",
                transform: "rotate(90deg)",
                height:"100%",
             
                // height: this.form_data.wrapper_height + "px",
                // width: this.form_data.wrapper_width + "px",
            };
        },
    },
    methods: {
        printBill: function () {
            //     this.$nextTick(() => {
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

            printWindow.document.close(); // necessary for IE >= 10
            printWindow.focus(); // necessary for IE >= 10

            // Print the new window or iframe
            setTimeout(() => {
                printWindow.print();
                printWindow.document.close();
            }, 1000);

            //  });
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
