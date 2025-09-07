<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'executive_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('executive_report')"
                >
                    Profit/Loss
                </button>
                <hr />
                  <button
                    :class="[
                        'exec_invoice_sale_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('exec_invoice_sale_report')"
                >
                    Invoice Sales Report
                </button>
                <hr />
                 <button
                    :class="[
                        'invoice_summary_sales' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('invoice_summary_sales')"
                >
                    Summary Sales 
                </button>
                <hr />
                <button
                    :class="[
                        'exec_product_sales_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('exec_product_sales_report')"
                >
                    Product Sales Report
                </button>
                <hr />
              
              <button
                    :class="[
                        'exec_cummulative_product_sales_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('exec_cummulative_product_sales_report')"
                >
                    Product Cummulative Sales Report
                </button>
                <hr />
                 <button
                    :class="[
                        'user_branch_invoice_sales_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('user_branch_invoice_sales_report')"
                >
                   User & Branch Report
                </button>
                <hr />
                 <button
                    :class="[
                        'grn_reports_summary' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('grn_reports_summary')"
                >
                Purchase/GRN Report
                </button>
                <hr />
              
            </nav>
        </div>
    </div>
</template>

<script></script>
