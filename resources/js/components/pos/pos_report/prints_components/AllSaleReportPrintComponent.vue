<template>
    <div class="col-12" id="print_bill" data-app>
        <div style="border: 2px solid black">
            <receipt-header />
            <hr />
            <br />
            <br /><b>From {{ from }} To {{ to }}</b>
            <p>Printed By: {{ this.$store.state.user.name }}</p>
            <br />
            <p><b>All Sales Report</b></p>
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
                         <th scope="col">Credit Paid</th>

                        <th scope="col">Cashier</th>
                        <th scope="col">User</th>
                        <th>Customer</th>
                        <th>Branch</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="small-tr"
                        v-for="(data, i) in sale_data"
                        :key="i"
                    >
                        <td>{{ data.paid_date }}</td>

                        <td>
                            {{ cashFormatter(data.receipt_total) }}
                        </td>
                        <td>
                            {{ cashFormatter(data.total_vat) }}
                        </td>
                        <td>
                            <b
                                :title="
                                    'balance: ' +
                                    cashFormatter(data.receipt_balance)
                                "
                            >
                                {{
                                    cashFormatter(
                                        data.cash_pay + data.receipt_balance
                                    )
                                }}
                            </b>
                        </td>
                        <td>
                            {{ cashFormatter(data.mpesa_pay) }}
                        </td>
                        <td>
                            {{ cashFormatter(data.card_pay) }}
                        </td>
                          <td>
                            {{ cashFormatter(data.credit_pay) }}
                        </td>

                        <td>
                            {{ data.cashier ? data.cashier.name : "" }}
                        </td>
                        <td>
                            {{ data.user.name }}
                        </td>
                        <td>{{data.customer.company_name}}</td>
                        <td>{{data.branch.branch }}</td>
                    </tr>
                </tbody>
            </table>
              <div class="row">
            <br />
            <br />
            <table style="width: 30%">
               
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
                <tr>
                    <td>
                        <b>TOTAL CREDIT:</b>
                    </td>

                    <td>
                        <b> {{ cashFormatter(total_credit) }}</b>
                    </td>
                </tr>
                 <tr>
                    
                    <td>
                         <hr>
                        <b>TOTAL SALES: </b>
                    </td>
                    <td>
                        <hr>
                        <b> {{ cashFormatter(total_sales) }}</b>
                    </td>
                </tr>
            </table>
            <hr>
        </div>
        </div>
    </div>
</template>

<script>
import ReceiptHeader from "../../menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    props: ["total_sales", "total_vat",'total_cash','total_mpesa','total_card','total_credit', "sale_data", "from", "to"],
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
