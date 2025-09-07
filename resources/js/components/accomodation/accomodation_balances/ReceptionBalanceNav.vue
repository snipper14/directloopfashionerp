<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>Reception Balance </b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'reservation_balances' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('reservation_balances')"
                >
                    Reception Balances
                </button>
                <hr />
                <button
                    :class="[
                        'reservation_balances_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('reservation_balances_report')"
                >
                    Balance Report
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
