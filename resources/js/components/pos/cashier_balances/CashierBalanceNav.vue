<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>Cashier </b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'cashier_balances_dashboard' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('cashier_balances_dashboard')"
                >
                    Cashier Balances
                </button>
                <hr />
                <button
                    :class="[
                        'balances_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('balances_report')"
                >
                    Balance Report
                </button>
                <hr />
                  <button
                    :class="[
                        'cash_drops' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('cash_drops')"
                >
                    Cash Drops
                </button>
                <hr />
             
                <button
                    :class="[
                        'cash_drops_reports' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('cash_drops_reports')"
                >
                    Cash Drops Report
                </button>
                <hr>
                  <button
                    :class="[
                        'mpesa_drops' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('mpesa_drops')"
                >
                    Mpesa Drops
                </button>
                <hr />
                  <button
                    :class="[
                        'mpesa_drops_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('mpesa_drops_report')"
                >
                    Mpesa Drops Report
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {};
    },
    mounted() {}
};
</script>
