<template>
    <div id="print_section">
        <div class="print-border" :style="wrapperStyle">
            <div ref="contentToPrint">
                <span v-for="n in loop_array" :key="n" :style="spanStyle">
                    <center>
                        <barcode
                            :width="form_data.bar_width"
                            :height="form_data.bar_height"
                            :fontSize="form_data.bar_font_size"
                            textAlign="center"
                             :value="`${stockObject.code}${batch_no ? '-' + batch_no : ''}`"
                            :style="barcodeStyle"
                        >
                        </barcode>
                        <!-- <span class="dark-background">{{company.toLowerCase()}}</span> -->
                        <b :style="spanStyle">
                            Ksh. {{stockObject.selling_price}} / {{ stockObject.label }}
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
    props: ["stockObject", "dimensionObj", "qty","batch_no"],
    data() {
        return {
            company: this.$store.state.branch.business_name,
            value: "https://example.com",
            size: 300,
            publicPath: process.env.BASE_URL,
            barcodeValue: this.stockObject.code,
            loop_array: parseInt(this.qty),
            form_data: {
                bar_font_size: this.dimensionObj.bar_font_size, // Adjusted for smaller labels
                bar_height: this.dimensionObj.bar_height, // Adjusted for 2x1 inch size
                bar_width: this.dimensionObj.bar_width, // Adjusted for 2x1 inch size
            },
            barcodeStyle: {
                marginTop: "5px",
                paddingBottom: "0px",
                transformOrigin: "center center",
            },
        };
    },
    mounted() {
        console.log("DIMENS>>>>"+JSON.stringify(this.dimensionObj));
    },
    computed: {
        wrapperStyle() {
            return {
                width: "2in",
                height: "1in",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                border: "1px solid black",
                padding: "5px",
            };
        },
        spanStyle() {
            return {
                display: "block",
                margin: "auto",
                textAlign: "center",
                fontSize: this.dimensionObj.item_description_fontsize + "px", // Dynamic font size
                marginTop: "0px", // Add margin-top
                overflow: "hidden", // Ensure text overflow is hidden
                textOverflow: "ellipsis", // Add ellipsis for overflowing text
                whiteSpace: "nowrap",
            };
        },
    },
    methods: {
        printBill: function () {
            const contentToPrint = this.$refs.contentToPrint;

            const printWindow = window.open("", "", "");
            printWindow.document.open();
            printWindow.document.write(`
      <html>
        <head>
          <title>Print</title>
          <style>
            body {
              margin: 0;
              padding: 0;
              width: 2in;
              height: 1in;
            }
            .dark-background {
    background-color: #343a40; /* Dark background */
    color: #ffffff; /* White text */
    padding: 0 4px; /* Add some padding around the text */
    border-radius: 4px; /* Optional: Add rounded corners */
    display: inline-block; /* Ensures the background only applies to the text */
}
          </style>
        </head>
        <body>
          ${contentToPrint.innerHTML}
        </body>
      </html>`);

            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.document.close();
            }, 1000);
        },
    },
};
</script>

<style scoped>
.border {
    border: 1px solid black !important;
}
</style>
