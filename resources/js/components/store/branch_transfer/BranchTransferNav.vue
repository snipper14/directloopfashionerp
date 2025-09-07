<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'branch_transfer' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('branch_transfer')"
                >
                    Transfer
                </button>

                <button
                    :class="[
                        'receive_transferred_stock' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('receive_transferred_stock')"
                >
                    Receive Stock
                </button>
                  <button
                    :class="[
                        'branch_transfer_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('branch_transfer_report')"
                >
               Transfer Report
                </button>
                   <button
                    :class="[
                        'receivable_branch_transfer_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('receivable_branch_transfer_report')"
                >
               Receivable Report
                </button>
                  <button
                    :class="[
                        'lost_transfer_report' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('lost_transfer_report')"
                >
               Lost Transfer Report
                </button>
              
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {},
};
</script>
<style scoped>
.flex-column button {
    border-bottom: 2px solid #fff !important;
}
</style>
