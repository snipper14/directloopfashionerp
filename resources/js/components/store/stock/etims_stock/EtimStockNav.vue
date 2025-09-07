<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'stock_management' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('stock_management')"
                >
                    Stock Managements
                </button>

                <hr />
                <button
                    :class="[
                        'create_product_category' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('create_product_category')"
                >
                    Product Categories
                </button>

                <hr />
                 <button
                    :class="[
                        'create_unit' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('create_unit')"
                >
                   Units
                </button>
                 <button
                    :class="[
                        'price_groups' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('price_groups')"
                >
                   Price Groups
                </button>
                 <button
                    :class="[
                        'merged_stock_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('merged_stock_report')"
                >
                   Merged Report
                </button>
                  <button
                    :class="[
                        'trashed_products' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('trashed_products')"
                >
               Trashed
                </button>

                <hr />
                 <button
                    :class="[
                        'etims_items_class_code' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('etims_items_class_code')"
                >
                    Items ClassCode
                </button>

                <hr />
                <button
                    :class="[
                        'etims_item_type_code' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('etims_item_type_code')"
                >
                    ItemType Code
                </button>

                <hr />
                <button
                    :class="[
                        'etims_country_origin' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('etims_country_origin')"
                >
                    Country Origin
                </button>

                <hr />

                <button
                    :class="[
                        'etims_item_packaging_code' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('etims_item_packaging_code')"
                >
                     Packaging Code
                </button>

                <hr />
                 <button
                    :class="[
                        'shelf_allocation' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('shelf_allocation')"
                >
                     Shelve Allocation
                </button>

                <hr />
                 <button
                    :class="[
                        'print_labels' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('print_labels')"
                >
                     Print Labels
                </button>

                <hr />
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
