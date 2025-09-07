<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'update_stock_balance' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('update_stock_balance')"
                >
                    Update Stock
                </button>
                <hr />
                <button
                    :class="[
                        'physical_update_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('physical_update_report')"
                >
                    Update Stock Report
                </button>
                <hr />
                <button
                    :class="[
                        'stock_deduction_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('stock_deduction_report')"
                >
                    Stock Deduction Report
                </button>
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
