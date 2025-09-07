<template>
    <div id="print_section">
          <div>
            <b> {{report_title.toUpperCase()}}</b><br />
            <receipt-header/>
            <hr>
            <b>From {{ from }} To {{ to }}</b>
            <p>Printed By: {{ this.$store.state.user.name }}</p>
            <br />
        </div>
          <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <th scope="col">Name</th>
                                   

                                    <th scope="col">Total Sale</th>
                                </thead>

                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in user_report_data"
                                        :key="i"
                                    >
                                        <td>
                                              <span>
                                                <b
                                                    v-if="
                                                        params.user_activity ==
                                                        'sales'
                                                    "
                                                >
                                                    {{ data.user.name }}</b
                                                >
                                                <b v-else>
                                                    {{ data.cashier.name }}</b
                                                ></span
                                            >
                                        </td>
                                        <td>
                                           {{
                                                cashFormatter(
                                                    data.sum_receipt_total
                                                )
                                            }}
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
    props:['from','to',"report_title",'user_report_data','waiter_id','all_user_totals','params'],
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
