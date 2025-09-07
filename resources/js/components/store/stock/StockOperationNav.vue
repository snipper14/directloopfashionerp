<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'create_ingredients' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('/create_ingredients')"
                >
                    Stock Aggregate
                </button>
                <hr />

                <button
                    :class="[
                        'add_production_cost' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('/add_production_cost')"
                >
                    Production Cost
                </button>
                <hr />

                <button
                    :class="[
                        'split_settings' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('/split_settings')"
                >
                   Add Children
                </button>
                <hr />
                <button
                    :class="[
                        'split_product' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('/split_product')"
                >
                    Sub- Product
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>
<script>
export default {
    props: ["id"],
    mounted() {
        console.log("====================================");
        console.log(this.$route.name);
        console.log("====================================");
    }
};
</script>
