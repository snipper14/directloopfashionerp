<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b> Sales Report</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'all_sales_reports' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('all_sales_reports')"
                >
                    All Sales
                </button>
                <hr />
                  <button
                    :class="[
                        'collapsible_allsale_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('collapsible_allsale_report')"
                >
                   Expandable Sales Report.
                </button>

                <hr />
                <button
                    :class="[
                        'rs_category_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('rs_category_report')"
                >
                    Sales Summary.
                </button>

                <hr />
                <button
                    :class="[
                        'sale_summary_with_activity' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('sale_summary_with_activity')"
                >
                    Sales Summary With Activity.
                </button>

                <hr />
                  <button
                    :class="[
                        'customer_sales_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('customer_sales_report')"
                >
                    Customer Sales
                </button>
                  <hr />
                <button
                    :class="[
                        'items_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('items_report')"
                >
                    Sold Items
                </button>
                <hr />
                
                <button
                    :class="[
                        'pos_reports' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('pos_reports')"
                >
                    Daily Sales
                </button>

                <hr />
                  <button
                    :class="[
                        'daily_itemized_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('daily_itemized_report')"
                >
                    Daily Items Sales
                </button>

                <hr />
                <button
                    :class="[
                        'profit_loss_summary' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('profit_loss_summary')"
                >
                    Profit/Loss Summ
                </button>
                <hr />
                <button
                    :class="[
                        'card_payment_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('card_payment_report')"
                >
                    Card Payment Report
                </button>
                <hr>
                      <button
                    :class="[
                        'mpesa_cashout_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('mpesa_cashout_report')"
                >
                    Mpesa Payment Report
                </button>

                <hr />
                  <button
                    v-if="  branch.etims_type == 'oscu'"
                    :class="[
                        'non_etims_sales_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('non_etims_sales_report')"
                >
                    Non-OEtims Sales Report
                </button>

                <hr />
                <button
                v-if="  branch.etims_type == 'oscu'"
                    :class="[
                        'etims_sales_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('etims_sales_report')"
                >
                    OEtims Sales Report
                </button>

                <hr />
                   <button
                v-if="  branch.etims_type == 'vscu'"
                    :class="[
                        'vscu_etims_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('vscu_etims_report')"
                >
                    vEtims Sales Report
                </button>

                <hr />
                   <button
                v-if="  branch.etims_type == 'vscu'"
                    :class="[
                        'non_vscu_etims_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('non_vscu_etims_report')"
                >
                    Non Etims Sales Report
                </button>

                <hr />
                <button
                    :class="[
                        'user_sale_summary' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('user_sale_summary')"
                >
                    Users Sales Summary.
                </button>

                <hr />
                     <button
                    :class="[
                        'deadstock_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('deadstock_report')"
                >
                    Dead Stock
                </button>
                <hr/>
                   <button
                    :class="[
                        'archived_sales_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('archived_sales_report')"
                >
                    Archived Sales
                </button>

                <hr />
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    components: {
       
    },
    
    data() {
        return {
         branch: this.$store.state.branch,
        };
    },
}
</script>
