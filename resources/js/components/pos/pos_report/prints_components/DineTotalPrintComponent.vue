<template>
    <div id="print_section">
        <br />
        <div>
            <b> {{report_title}}</b><br />
            <receipt-header />
            <hr>
            <b>From {{ from }} To {{ to }}</b>
            <p>Printed By: {{ this.$store.state.user.name }}</p>
            <br />
        </div>
        <hr>
        <div class="row">
            <br />
            <br />
            <table style="width: 30%">
                <tr>
                    <td>
                        <b>TOTAL SALES: </b>
                    </td>
                    <td>
                        <b> {{ cashFormatter(total_sales) }}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>TOTAL VAT:</b>
                    </td>

                    <td>
                        <b> {{ cashFormatter(total_vat) }}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>TOTAL CASH:</b>
                    </td>

                    <td>
                        <b> {{ cashFormatter(total_cash) }}</b>
                    </td>
                </tr>

                 <tr>
                    <td>
                        <b>TOTAL MPESA:</b>
                    </td>

                    <td>
                        <b> {{ cashFormatter(total_mpesa) }}</b>
                    </td>
                </tr>
                  <tr>
                    <td>
                        <b>TOTAL CARD:</b>
                    </td>

                    <td>
                        <b> {{ cashFormatter(total_card) }}</b>
                    </td>
                </tr>
            </table>
            <hr>
        </div>
    </div>
</template>

<script>
import ReceiptHeader from "../../menu_components/dinerscomponents/ReceiptHeader.vue";
export default {
    components: { ReceiptHeader },
    props: ["total_sales", "total_vat",'total_cash','total_mpesa','total_card', "to", "from","report_title"],
    data() {
        return {};
    },
    mounted() {
        console.log("Component mounted.");
    },
    methods: {
        printBill: function () {
            printJS("print_section", "html");
            // this.value = value;
        },
    },
};
</script>
<style scoped>
#print_section {
     visibility: hidden; 
}
</style>
