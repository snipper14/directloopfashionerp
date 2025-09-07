<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'direct_purchase' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('direct_purchase')"
                >
                    New Direct Order
                </button>
                <button
                    :class="[
                        'direct_order_delivery' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('direct_order_delivery')"
                >
                    Receive Orders
                </button>

                <hr />
                <button
                    :class="[
                        'direct_order_delivery_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('direct_order_delivery_report')"
                >
                    Receivable Reports
                </button>

                <hr />
                <button
                    :class="[
                        'direct_grn' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('direct_grn')"
                >
                    GRN Reports
                </button>

                <hr />
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {}
};
</script>
<style scoped>
.flex-column button {
    border-bottom: 2px solid #fff !important;
}
</style>
