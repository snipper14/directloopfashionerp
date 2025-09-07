<template>
<span>
     <span><router-link
                                                    to="#"
                                                    @click.native="
                                                        checkInventory()
                                                    "
                                                    >{{product_name}}</router-link>
                                                     
          
       </span>
        <Modal v-model="check_inventory_modal">
            <check-inventory-modal
                v-if="check_inventory_modal"
                :inventory_data="inventory_data"
        /></Modal>
</span>
  
</template>

<script>
import CheckInventoryModal from '../pos_retail/CheckInventoryModal.vue';
export default {
  components: { CheckInventoryModal },
    props:['product_name','stock_id'],
    data() {
        return {
            check_inventory_modal:false,
            inventory_data:[]
        };
    },
    mounted() {
        console.log("Component mounted.");
    },
    methods:{
        dismissModal(){
            this.check_inventory_modal=false
        },
        async checkInventory(id) {
            this.showLoader();
            const res = await this.getApi("data/inventory/getItemQty", {
                params: {
                    id: this.stock_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.inventory_data = res.data;
                this.check_inventory_modal = true;
            } else {
                this.form_error(res);
            }
        },
    }
};
</script>
