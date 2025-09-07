<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b></b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'inventory_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('inventory_report')"
                >
                    Branch Comparison
                </button>
                <hr />
                <button
                    :class="[
                        'all_branches_inventory_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('all_branches_inventory_report')"
                >
                    All Branches Report.
                </button>

                <hr />
                 <button
                    :class="[
                        'closing_stock' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('closing_stock')"
                >
                    Closing Stock Report.
                </button>

                <hr />
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    components: {},

    data() {
        return {
            branch: this.$store.state.branch,
        };
    },
};
</script>
