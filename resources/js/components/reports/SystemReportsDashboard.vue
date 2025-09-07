<template>
    <div class="container">
        <div class="row justify-content-center" v-if="isDisplayPermitted">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><b>GENERAL REPORT</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex justify-content around mt-3">
                                <div class="form-group">
                                    <label> From *</label>

                                    <date-picker
                                        type="datetime"
                                        width="300px"
                                        v-model="from"
                                    ></date-picker>
                                </div>
                                <div class="">
                                    <label> To *</label>
                                    <date-picker
                                        type="datetime"
                                        width="300px"
                                        v-model="to"
                                    ></date-picker>
                                </div>
                                <div>
                                    <button
                                        class="btn btn-info btn-sm ml-2"
                                        @click="concurrentApiReq()"
                                    >
                                        Filter 
                                    </button>

                                </div>
                                <div>
                                    <button class="btn btn-info btn-sm ml-2" @click="printRecord()"> 
                                        Print Record
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row"  id="print_record">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <td scope="col"> <b> Source</b></td>
                                            <td scope="col"><b> Amount</b></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="small-tr">
                                            <td>
                                                <b class="titles">
                                                    Invoices
                                                </b>
                                            </td>
                                            <td>
                                                <b class="titles">
                                                    {{
                                                        cashFormatter(
                                                            report_data
                                                                .invoices
                                                                .sum_row_total
                                                        )
                                                    }}
                                                </b>
                                            </td>
                                        </tr>
                                        <tr class="small-tr">
                                            <td>
                                                <b class="titles">
                                                    Point Of Sale
                                                </b>
                                            </td>
                                            <td>
                                                <b class="titles">
                                                    {{
                                                        cashFormatter(
                                                            report_data
                                                                .all_sales
                                                                .sum_receipt_total
                                                        )
                                                    }}
                                                </b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>cash payments</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        report_data.all_sales
                                                            .sum_cash_pay
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>M-pesa payments</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        report_data.all_sales
                                                            .sum_mpesa_pay
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Card payments</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        report_data.all_sales
                                                            .sum_card_pay
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                         <tr>
                                            <td>POS Credit Note</td>
                                            <td>
                                               - {{
                                                    cashFormatter(
                                                        report_data.pos_credit.sum_credit_amount
                                                            
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                     
                                      
                                      
                                       
                                  
                                        <tr class="small-tr">
                                            <td>
                                                <b class="titles">
                                                    GROSS TOTAL
                                                </b>
                                            </td>
                                            <td>
                                                <b class="titles">
                                                    {{
                                                        cashFormatter(
                                                            gross_total
                                                        )
                                                    }}
                                                </b>
                                            </td>
                                        </tr>
                                         <tr class="small-tr">
                                            <td>
                                                <b class="titles-purchase">
                                                   Total Purchases
                                                </b>
                                            </td>
                                            <td>
                                                <b class="titles-purchase">
                                                    {{
                                                        cashFormatter(
                                                            total_purchase
                                                        )
                                                    }}
                                                </b>
                                            </td>
                                        </tr>
                                         <tr class="small-tr">
                                            <td>
                                                <b class="titles">
                                                    NET TOTAL
                                                </b>
                                            </td>
                                            <td>
                                                <b class="titles">
                                                    {{
                                                        cashFormatter(
                                                            gross_total-total_purchase
                                                        )
                                                    }}
                                                </b>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <unauthorized/>
        </div>
        
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Unauthorized from '../utilities/Unauthorized.vue';
export default {
    components: {
        Treeselect,
        Unauthorized,
    },
    data() {
        
        return {
            isLoading: true,
            to: null,
            from: null,
            report_data: {},
            gross_total: 0,
            total_purchase:0,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        report_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.calculateGross();
                this.calculateTotalPurchase()
            }, 500),
        },
    },
    methods: {
        printRecord(){
             printJS("print_record", "html");
        },
        calculateTotalPurchase(){
          var d_purchase=this.report_data.all_direct_purchases.sum_total?
          this.report_data.all_direct_purchases.sum_total:0
          var purchase=this.report_data.all_purchases.sum_total?
          this.report_data.all_purchases.sum_total:0
          this.total_purchase=d_purchase+purchase
        },
        calculateGross() {
            var total_pos = this.report_data.all_sales.sum_receipt_total
                ? this.report_data.all_sales.sum_receipt_total
                : 0;
                 var sum_invoice_total = this.report_data.invoices.sum_row_total
                ? this.report_data.invoices.sum_row_total
                : 0;
          
            var refundable = this.report_data.refundable.sum_row_total
                ? this.report_data.refundable.sum_row_total
                : 0;

                 var total_credit_pos = this.report_data.pos_credit.sum_credit_amount
                ? this.report_data.pos_credit.sum_credit_amount
                : 0;
           

                this.gross_total=total_pos+refundable+sum_invoice_total-total_credit_pos
        },
        async fetchData() {
            const res = await this.getApi(
                "data/system_reports/fetch",

                {
                    params: {
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.report_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchData(1)]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
};
</script>
<style scoped>
.titles {
    font-size: 1.4rem !important;
}
.titles-purchase{
     font-size: 1.3rem !important;
     color: #9e9d24 !important;
}
</style>
