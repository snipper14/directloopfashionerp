<template>
    <div class="container-fluid">
        <div v-if="active.dashboard">
            <div v-if="isReadPermitted">
             
        <div class="card">
                    <div class="card-header"></div>
             <div class="row">
                <div class="col-md-6 ">
                     <fieldset class="border">
                                    <legend class="text-center">Point Of Sale</legend>
                  <div class="row"><div class="col-md-6 form-group">
                        <label for="">Scan Code</label>
                        <input type="text"  class="form-control">
                    </div>
                     <div class="col-md-6 form-group">
                         <label for="">Search</label>
                        <input type="text"  class="form-control">
                    </div>
                    </div>  
                     </fieldset>
                    
                </div>
                 <div class="col-md-6"></div>
             </div>
          
        </div>
        
            </div>
            <div v-else>
                <unauthorized />
            </div>
        </div>
       
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import {
    mdiAccountPlus,
    mdiFoodForkDrink,
    mdiCashRegister,
    mdiCashPlus,
    mdiLogoutVariant,
} from "@mdi/js";
import Unauthorized from "../utilities/Unauthorized.vue";

export default {
    data() {
        return {
            active: {
                dashboard: true,
                change_password: false,
            },
            
          
            form_data: {
                table_id: null,
            },
            icons: {
                mdiCashPlus,
                mdiAccountPlus,
                mdiFoodForkDrink,
                mdiCashRegister,
                mdiLogoutVariant,
            },
        };
    },

    mounted() {
       
       
    },
    components: { Unauthorized },

    methods: {
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.getShift();
        },
       
      
      
      
      
       
       
      
        
        
    },
    computed: {
        ...mapGetters({
            userInfo: "getUser",
            branchInfo: "getBranch",
        }),
    },
};
</script>
<style scoped>
.menu-icon {
    height: 150px;
    width: 200px;
    border-radius: 20px;
    margin-top: 4px;
}
.order-type {
    background: #1e88e5;
    margin-right: 2px;
}
.menu-text {
    color: #ffffff;
}
.elem-wrapper {
    margin-top: 2rem;
    display: flex;
    flex-direction: row;
    align-items: stretch;
    width: 100%;
}
.elem-wrapper > div {
    flex-grow: 1;
    border-radius: 4px;
}
.elem-wrapper .active {
    background: #4caf50;
    -webkit-transform: rotate(-8deg);
    -moz-transform: rotate(-8deg);
    -o-transform: rotate(-8deg);
    -ms-transform: rotate(-8deg);
    transform: rotate(-8deg);

    /* z-index: -1;
     bottom: 25px;
    left: 10px;
    width: 150%;
    top: 80%;
    max-width: 300px;
    -webkit-box-shadow: 0 35px 20px #777;
    -moz-box-shadow: 0 35px 20px #777;
    box-shadow: 0 35px 20px #777;
    -webkit-transform: rotate(-8deg);
    -moz-transform: rotate(-8deg);
    -o-transform: rotate(-8deg);
    -ms-transform: rotate(-8deg);
    transform: rotate(-8deg);  */
}
</style>
