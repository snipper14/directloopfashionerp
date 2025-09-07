<template>
     <div >
                <div
                    class="d-flex flex-column mt-2 mb-2"
                    v-for="(value, i) in data"
                    :key="i"
                >
                    <div class="row">
                        <div class="col-5">
                          
                          <item-particulars-component
                          :data="value"
                          />
                        </div>
                        <div class="col-2">
                            <b class="cart-items"> X </b>
                            <b class="cart-items"> {{ value.qty }}</b>
                        </div>
                        <div class="col-3">
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
                            <span class="badge badge-secondary">{{
                                value.no_guest
                            }}</span>
                        </p>
                        <p class="ml-2">
                            {{ value.notes }}
                        </p>
                    </div>
                    <hr />
                </div>
              
               
            </div>
</template>

<script>
import ItemParticularsComponent from './ItemParticularsComponent.vue'

    export default {
  components: { ItemParticularsComponent },
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