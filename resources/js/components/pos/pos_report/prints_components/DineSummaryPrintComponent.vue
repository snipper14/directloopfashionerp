<template>
    <div id="print_section">
          <div>
            <b> {{print_title}}</b><br />
            <receipt-header/>
            <hr>
            <b>From {{ from }} To {{ to }}</b><br><br>
            <p>Printed By: {{ this.$store.state.user.name }}</p>
            <br />
        </div><br>
        
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                    <tr>
                        <th scope="col" style="width: 10%">
                            <thead>
                                <th scope="col" style="width: 10%">stock</th>
                                <th scope="col" style="width: 2%">Qty</th>
                                <th scope="col" style="width: 5%">Total</th>
                            </thead>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="small-tr"
                        v-for="(data, i) in category_data"
                        :key="i"
                    >
                        <td>
                            <span style="width: 10%">
                                <b> {{ data.category_name }}</b></span
                            >
                            <span style="width: 40%">
                                TOTAL SALES:
                                <b>
                                    {{ cashFormatter(data.category_total) }}</b
                                ></span
                            >
                            <br />
                            <table class="table table-sm">
                                <tr v-for="(products, x) in data.data" :key="x">
                                    <td style="width: 10%">
                                        {{ products.stock.product_name }}
                                    </td>
                                    <td style="width: 2%">
                                        {{ products.sum_qty }}
                                    </td>
                                    <td style="width: 5%">
                                        {{
                                            cashFormatter(
                                                products.sum_row_total
                                            )
                                        }}
                                    </td>
                                </tr>
                            </table>
                            <br />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import ReceiptHeader from '../../menu_components/dinerscomponents/ReceiptHeader.vue';
export default {
    props:['category_data','from','to','print_title','grouped_dept_summary','total_sales','total_refundable'],
    data() {
        return {};
    },
    components: {
       ReceiptHeader 
    },

    methods: {
           printBill: function () {
            printJS("print_section", "html");
            // this.value = value;
        },
    },
}
        
</script>
<style scoped>
#print_section {
     visibility: hidden; 
}
</style>
