<template>
    <div class="row justify-content-center">
        <div class="col-2">
            <RequisitioNav />
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
                                > Single Product Dispatched</a
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
                                > Assembly Product Dispatched</a
                            >
                        </li>

                        <li class="nav-item">
                            <a
                                class="nav-link"
                                id="single-tab"
                                data-toggle="tab"
                                href="#update"
                                role="tab"
                                aria-controls="single"
                                aria-selected="false"
                                >Manufactured Product Report</a
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
                            <single-product-dispatch-report
                                v-if="user_data != null"
                                :user_data="user_data"
                            />
                        </div>
                        <div
                            class="tab-pane fade"
                            id="single"
                            role="tabpanel"
                            aria-labelledby="single-tab"
                        >
                        <assembly-product-dispatch-report
                            v-if="user_data != null"
                                :user_data="user_data"/>
                           
                        </div>

                        <div
                            class="tab-pane fade"
                            id="update"
                            role="tabpanel"
                            aria-labelledby="single-tab"
                        >
                            <assembly-manufactured-report
                                v-if="user_data != null"
                                :user_data="user_data"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <center style="margin-top:4rem" v-else>
            <b style="color:red;font-size:1.2rem;"
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
import SingleProductDispatchReport from './SingleProductDispatchReport.vue';
import AssemblyProductDispatchReport from './AssemblyProductDispatchReport.vue';
import AssemblyManufacturedReport from './AssemblyManufacturedReport.vue';
export default {
    data() {
        return {
            user_data: null,
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
        RequisitioNav,
        
       
        SingleProductDispatchReport,
        AssemblyProductDispatchReport,
        AssemblyManufacturedReport
    },
    methods: {
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchUsers()]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchUsers() {
            const res = await this.getApi("data/users/fetchAll", "");

            if (res.status == 200) {
                this.user_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        }
    }
};
</script>
