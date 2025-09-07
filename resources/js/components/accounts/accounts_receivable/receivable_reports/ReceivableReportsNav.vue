<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'invoice_reports' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('invoice_reports')"
                >
                    Customer Invoice Reports
                </button>
                <hr />
                <button
                    :class="[
                        'payment_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('payment_report')"
                >
                    Customer Payments
                </button>
                <hr />
                <button
                    :class="[
                        'customer_ledger' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('customer_ledger')"
                >
                    View/Print Customer Statements
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
