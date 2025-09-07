<template>
    <div class="container">
        <div
            :class="[
                value.stock_id,

                value.isComplete == 1 ? 'order-printed' : '',
            ]"
            class="d-flex flex-column mt-2 mb-2"
            v-for="(value, i) in order_data_surface"
            :key="i"
            @click="cartClck(value)"
        >
            <div class="row">
                <div class="col-7">
                    <item-particulars-component :data="value" />
                </div>
                <div class="col-2">
                    <b class="cart-items"> X </b>
                    <b class="cart-items"> {{ value.qty }}</b>
                </div>
                <div class="col-1">
                    <b class="cart-items">
                        {{ cashFormatter(value.row_total) }}</b
                    >
                </div>
             
            </div>
            <div class="d-flex justify-content-start">
                <p class="mr-2 cart-items">Unit Price /</p>

                <p class="cart-items">
                    {{ cashFormatter(value.price) }}
                </p>
                <p class="ml-2">
                    Guest
                    <span class="badge badge-secondary cart-items">{{
                        value.no_guest
                    }}</span>
                </p>
                <p class="ml-2 cart-items">
                    {{ value.notes }}
                </p>
            </div>
            <hr />
        </div>

        <div class="d-flex justify-content-end mt-2">
            <span>
                <b> TOTAL: {{ cashFormatter(total_order) }}</b>
            </span>
        </div>
        <div class="d-flex justify-content-end mt-1">
            <span>
                <b> TAX: {{ cashFormatter(total_tax) }}</b>
            </span>
        </div>
    </div>
</template>

<script>
import ItemParticularsComponent from "./dinerscomponents/ItemParticularsComponent.vue";
export default {
    components: { ItemParticularsComponent },
    props: ["order_data_surface", "total_tax", "total_order"],
    data() {
        return {};
    },
    mounted() {
        console.log("Component mounted.");
    },
};
</script>
<style scoped>
.order-printed {
    background: #c8e6c9 !important;
}
</style>