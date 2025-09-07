<template>
    <div class="col-12" id="print_bill" data-app>
        <div style="border: 2px solid black">
            <receipt-header />
            <hr />
            <br />
            <br /><b>Date {{ report_date }} </b>
            <p>Printed By: {{ this.$store.state.user.name }}</p>
            <br />
            <p><b>MAIN STORE STOCK MOVEMENT</b></p>
            <br /><br />
        </div>

        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>

                                        <th scope="col">Purchase P</th>
                                        <th scope="col">Opening Stock</th>
                                        <th scope="col">Added GRN</th>
                                        <th>T.T Stock</th>
                                        <th scope="col">T.Issue Qty</th>
                                        <th scope="col">T.Balance</th>
                                        <th scope="col">System Stock</th>
                                        <th scope="col">Physical Stock</th>
                                        <th scope="col">Deficit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in report_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.code }}
                                        </td>
                                        <td>
                                            {{ data.product_name }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.purchase_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.opening_stock
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.sum_grn_qty)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.total_stock)
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_issued_qty
                                                )
                                            }}
                                        </td>
                                        <!-- stock balance -->
                                        <td>
                                            {{ data.total_balance }}
                                        </td>

                                        <!-- system stock -->
                                        <td>{{ data.system_stock }}</td>
                                        <!-- physical stock -->
                                        <td>
                                            
                                            
                                            {{data.physical_qty}}
                                        </td>
                                        <td>
                                            {{ data.deficit }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
    </div>
</template>

<script>
import ReceiptHeader from '../../../pos/menu_components/dinerscomponents/ReceiptHeader.vue';


export default {
    props: ["report_data", 'report_date'],
    data() {
        return {};
    },
    components: {
        ReceiptHeader
        ,
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
