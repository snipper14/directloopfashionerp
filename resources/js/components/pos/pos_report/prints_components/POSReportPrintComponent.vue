<template>
    <div class="col-12" id="print_bill" data-app>
        <div style="border: 2px solid black">
            <receipt-header />
            <hr />
            <br />
            <br /><b>From {{ from }} To {{ to }}</b>
            <p>Printed By: {{ this.$store.state.user.name }}</p>
            <br />
            <h3><b>DINE REPORT</b></h3>
            <br /><br />
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Sale Date</th>
                        <th scope="col">Amount</th>
                        <th>Vat</th>
                        <th scope="col">Cash Paid</th>
                        <th scope="col">Mpesa Paid</th>
                        <th scope="col">Card Paid</th>

                    
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="small-tr"
                        v-for="(data, i) in sale_data.data"
                        :key="i"
                    >
                        <td>{{ data.paid_date }}</td>

                        <td>
                            {{ cashFormatter(data.sum_receipt_total) }}
                        </td>
                        <td>
                            {{ cashFormatter(data.sum_total_vat) }}
                        </td>

                        <td>
                            {{ cashFormatter(data.sum_cash_pay) }}
                        </td>
                        <td>
                            {{ cashFormatter(data.sum_mpesa_pay) }}
                        </td>

                        <td>
                            {{ cashFormatter(data.sum_card_pay) }}
                        </td>

                       
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import ReceiptHeader from "../../menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    props: ["sale_data", "from", "to"],
    data() {
        return {};
    },
    components: {
        ReceiptHeader,
    },

    methods: {
        printBill: function () {
            printJS("print_bill", "html");
            // this.value = value;
        },
    },
    mounted() {},
};
</script>

<style scoped>
#print_bill {
    visibility: hidden;
}
</style>
