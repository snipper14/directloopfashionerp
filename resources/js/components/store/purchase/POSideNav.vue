<template>
<div class="container ">
    <div class="col-md-12">
        <nav class="nav flex-column">
            <button
                :class="[
                    'po_management' === this.$route.name ? 'nav-active' : ''
                ]"
                class="btn btn-secondary btn-sm"
                @click="$router.push('po_management')"
            >
                New Order
            </button>
          
            <button
                    :class="[
                        'low_stock' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('low_stock')"
                >
                   Low Stock
                </button>
            <button
                :class="[
                    'order_delivery' === this.$route.name
                        ? 'nav-active'
                        : ''
                ]"
                class="btn btn-secondary btn-sm"
                @click="$router.push('order_delivery')"
            >
                Receive PO
            </button>
            <button
                :class="[
                    'delivery_notes' === this.$route.name
                        ? 'nav-active'
                        : ''
                ]"
                class="btn btn-secondary btn-sm"
                @click="$router.push('delivery_notes')"
            >
                goods received(grn)
            </button>
                <button
                :class="[
                    'grn_batch_report' === this.$route.name
                        ? 'nav-active'
                        : ''
                ]"
                class="btn btn-secondary btn-sm"
                @click="$router.push('grn_batch_report')"
            >
                    Batch Report(grn)
            </button>
             <button
                    :class="[
                        'grn_return_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('grn_return_report')"
                >
                     Goods Returns Report
                </button>
                   <button
                    :class="[
                        'purchase_ageing_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('purchase_ageing_report')"
                >
                     Purchase Ageing Report
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
