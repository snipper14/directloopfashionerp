<template>
     <div class="bill-wrapper">
              <order-details-without-total
              :data="data.results"
              />
                <div class="d-flex justify-content-end mt-1 mr-4">
                    <span>
                        <b>
                            TAX:
                            {{ cashFormatter(data.total_tax) }}</b
                        >
                    </span>
                </div>
                <div class="d-flex justify-content-end mt-2 mr-4">
                    <span>
                        <b>
                            TOTAL:
                            {{ cashFormatter(data.order_total) }}</b
                        >
                    </span>
                </div>
            </div>
</template>

<script>
import ItemParticularsComponent from './ItemParticularsComponent.vue'
import OrderDetailsWithoutTotal from './OrderDetailsWithoutTotal.vue'

    export default {
  components: { ItemParticularsComponent, OrderDetailsWithoutTotal },
        props:['data'],
           data(){
            return{
            }
        },
        mounted() {
            
        }
    }
</script>
<style scoped>
.bill-active {
    background: #bec5b7 !important;
}
.bill-wrapper {
    height: 300px;
    overflow-x: hidden;
    overflow-y: auto;
}
.cart-items {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
</style>