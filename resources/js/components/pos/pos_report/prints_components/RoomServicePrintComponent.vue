<template>
    <div class="col-12" id="print_bill" data-app>
        <div style="border: 2px solid black;">
            <receipt-header />
            <hr><br>
            <br><b>From {{from}}     To {{to}}</b>
            <p>Printed By: {{this.$store.state.user.name}}</p><br>
            <p><b>Room Service Report</b></p><br><br>
            <hr>
        </div>
        <hr>
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

                       
                        <th scope="col">Customer</th>
                        <th scope="col">Cashier</th>
                        <th scope="col">Waiter</th>
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
                            {{
                                data.guest ? data.guest.name : ""
                            }}
                        </td>
                        <td>
                            {{ data.cashier ? data.cashier.name : "" }}
                        </td>
                        <td>
                            {{ data.user ? data.user.name : "" }}
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
    props: ["sale_data",'from','to'],
    data() {
        return {};
    },
    components: {
        ReceiptHeader,
    },

    methods: {
        printBill: function () {
                  console.log(">>>>>>>>>>>++");
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
