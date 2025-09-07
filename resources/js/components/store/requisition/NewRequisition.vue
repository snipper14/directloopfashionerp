<template>
    <div class="row justify-content-center">
        <div class="col-2">
            <RequisitioNav />
        </div>
        <div class="col-md-10" v-if="true">
            
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
                                >Single Product</a
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
                                >Assembly Product</a
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
                            <single-product
                             v-if="req_id != '' && dept_data!=null"
                               
                                :req_id="req_id"
                                :dept_data="dept_data"
                            />
                        </div>
                        <div
                            class="tab-pane fade"
                            id="single"
                            role="tabpanel"
                            aria-labelledby="single-tab"
                        >
                            <assembly-product
                                v-if="req_id != '' && dept_data!=null"
                              
                                :req_id="req_id"
                                :dept_data="dept_data"
                            />
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
import RequisitioNav from "./RequisitioNav.vue";
import SingleProduct from "./SingleProduct.vue";
import AssemblyProduct from "./AssemblyProduct.vue";
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
        this.concurrentApiReq();
    },
    components: {
        SingleProduct,
        AssemblyProduct,
        RequisitioNav,
    },
    methods: {
         async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchReqNo(), this.fetchDept()]).then(
                function(results) {
                    return results;
                }
            );
            this.hideLoader();
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");
             
            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchReqNo() {
           
            const res = await this.getApi("data/requisition/fetchReqNo", {});
         
            if (res.status === 200) {
                this.req_id = res.data;
               
            } else {
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
