<template>
    <div class="container">
        <div class="col-12">
         <order-details-without-total
              :data="order_data"
              />
        </div>
    </div>
</template>

<script>
import OrderDetailsWithoutTotal from '../../menu_components/dinerscomponents/OrderDetailsWithoutTotal.vue';
export default {
    components: {  OrderDetailsWithoutTotal },
    props: ["order_data"],
    data() {
        return {};
    },
    mounted() {
        console.log("Component mounted.");
    },
};
</script>
