<template>
    <div class="row justify-content-center">
        <div class="col-2">
             <POSideNav />
        </div>
        <div class="col-md-10" v-if="isReadPermitted">
            
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a
                                class="nav-link active"
                                id="home-tab"
                                data-toggle="tab"
                                href="#home"
                                role="tab"
                                aria-controls="home"
                                aria-selected="true"
                                >Purchase Return</a
                            >
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link"
                                id="single-tab"
                                data-toggle="tab"
                                href="#single"
                                role="tab"
                                aria-controls="single"
                                aria-selected="false"
                                >Purchase Return Report</a
                            >
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div
                            class="tab-pane fade show active"
                            id="home"
                            role="tabpanel"
                            aria-labelledby="home-tab"
                        >
                            <purchase-return-dashboard
                            
                            />
                        </div>
                        <div
                            class="tab-pane fade"
                            id="single"
                            role="tabpanel"
                            aria-labelledby="single-tab"
                        >
                         
                         <purchase-return-report/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <center v-else class="mt-4">
                <b style="color:red;font-size:1.2rem"
                    >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                >
            </center>
        <notifications group="foo" />
    </div>
</template>
<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import PurchaseReturnDashboard from "./PurchaseReturnDashboard.vue";
import PurchaseReturnReport  from "./PurchaseReturnReport.vue";
import POSideNav from "../purchase/POSideNav.vue";

export default {
    
    data() {
        return {
            req_id: "",
            form_data: {
                req_id: ""
            },
            dept_data:null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    created() {
       
    },
    components: {
        PurchaseReturnDashboard,
       POSideNav,
        PurchaseReturnReport
    },
    methods: {
    

             
      
    }
};
</script>
