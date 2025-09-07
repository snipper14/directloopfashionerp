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
                                >Issue Single Product</a
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
                                >Issue Assembly Product</a
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
                                >Update Manufactured Stock</a
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
                            <issue-single-product
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
                            <issue-assembly-product
                                v-if="user_data != null"
                                :user_data="user_data"
                            />
                        </div>

                        <div
                            class="tab-pane fade"
                            id="update"
                            role="tabpanel"
                            aria-labelledby="single-tab"
                        >
                            <update-assembly-product-stock
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
import IssueSingleProduct from "./IssueSingleProduct.vue";
import IssueAssemblyProduct from "./IssueAssemblyProduct.vue";
import UpdateAssemblyProductStock from "./UpdateAssemblyProductStock.vue";
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
        IssueSingleProduct,
        IssueAssemblyProduct,
        UpdateAssemblyProductStock
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
