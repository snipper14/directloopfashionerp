<template>
    <v-app style="margin-top: 0px">
         <mainnav
                :user="user"
                :permission="permission"
                :role="role"
                :department="department"
                :branch="branch"
                v-on:dashboard-active="showLanding"
                @dismiss-landing="hideLanding"
                
            />
        <div class="landing_content_wrapper" v-if="active.dashboard">
            <div class="row justify-content-center">
                <div class="col-md-11">
                    <div class="row" v-if="false">
                        <div class="col-md-3">
                            <span class="badge badge-secondary"
                                ><h6>Branch: {{ branch.branch }}</h6></span
                            >
                        </div>
                        <div class="col-md-3 d-flex flex-row">
                            <span class="badge badge-secondary mr-3"
                                ><h6>Role:{{ role.roleName }}</h6>
                            </span>
                            <span class="badge badge-secondary">
                                <h6>Dept: {{ department.department }}</h6>
                            </span>
                        </div>
                        <div class="col-md-4 d-flex flex-row">
                            <span class="badge badge-secondary">
                                <h6>{{ user.name }}</h6>
                            </span>

                            <p class="top-tag ml-3">
                                Branch: {{ branch.branch }}
                            </p>
                        </div>
                        <div class="col-md-2">
                            <v-btn color="warning" @click="logout()" small
                                >logout</v-btn
                            >
                        </div>
                    </div>
                    <div class="card landing_content">
                        <div class="card-header">
                            <div v-if="false">
                                <div class="badge-custom">
                                    <h4>
                                        <b>{{ app_name }}</b>
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 d-flex flex-column">
                                    <v-btn
                                        color="info"
                                        style="overflow-wrap: break-word"
                                        large
                                        @click="linkPage('home')"
                                    >
                                        Dashboard
                                    </v-btn>
                                    <v-btn
                                        @click="linkPage('branch')"
                                        color="warning"
                                        class="mt-2"
                                        large
                                    >
                                        Settings
                                    </v-btn>
                                     <div class="m-top"><center><img :src="branch.img_url" width="200px" alt=""></center></div>
                                </div>
                                <div class="col-md-9 row">
                                    <div
                                        @click="linkPage('retail_pos')"
                                        style="background: #283593"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiPointOfSale
                                        }}</v-icon>
                                        <p>Point Of Sale</p>
                                    </div>
                                    <div
                                        @click="
                                            linkPage('stock_management')
                                        "
                                        style="background: #D500F9"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiBasket 
                                        }}</v-icon>
                                        <p>Stock Management</p>
                                    </div>
                                      <div
                                        @click="
                                            linkPage('production_management')
                                        "
                                        style="background: #388e3c"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiStocking
                                        }}</v-icon>
                                        <p>Production</p>
                                    </div>
                                    <div
                                        @click="
                                            linkPage('inventory_management')
                                        "
                                        style="background: #ff3d00"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiStoreOutline
                                        }}</v-icon>
                                        <p>Inventory</p>
                                    </div>
                                    <div
                                        @click="linkPage('po_management')"
                                        style="background: #00897b"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiCashRemove
                                        }}</v-icon>
                                        <p>Purchases</p>
                                    </div>
                                    <div
                                        @click="linkPage('employee_manager')"
                                        style="background: #7986cb"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiHumanQueue
                                        }}</v-icon>
                                        <p>HRM</p>
                                    </div>
                                    <div
                                        @click="linkPage('orders_dashboard')"
                                        style="background: #f50057"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiAccountCashOutline
                                        }}</v-icon>
                                        <p>Accounts Receivable</p>
                                    </div>
                                    <div
                                        @click="
                                            linkPage('new_supplier_invoice')
                                        "
                                        style="background: #ff6f00"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiCashRefund
                                        }}</v-icon>
                                        <p>Accounts Payable</p>
                                    </div>
                                    <div
                                        @click="linkPage('executive_report')"
                                        style="background: #7c4dff"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiFinance
                                        }}</v-icon>
                                        <p>Executive Report</p>
                                    </div>
                                    <div
                                        @click="linkPage('all_sales_reports')"
                                        style="background: #9c27b0"
                                        class="text-white tab-btn col-md-2 mt-2"
                                    >
                                        <v-icon style="color: white" x-large>{{
                                            icons.mdiSalesforce
                                        }}</v-icon>
                                        <p>POS Report</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="active.page_view">
           

           
        </div>
         <!-- <router-view> </router-view> -->
    </v-app>
</template>

<script>
import {
    mdiPointOfSale,
    mdiStoreOutline,
    mdiAccountCashOutline,
    mdiCashRefund,
    mdiFinance,
    mdiSalesforce,
    mdiStocking,
    mdiCashRemove,
    mdiHumanQueue,
    mdiBasket 
} from "@mdi/js";
import mainnav from "./mainnav.vue";
export default {
    components: { mainnav },
    props: ["user", "permission", "role", "department", "branch"],
    data() {
        return {
            active: {
                dashboard: true,
                page_view: false,
            },
            icons: {
                mdiPointOfSale,
                mdiStoreOutline,
                mdiAccountCashOutline,
                mdiFinance,
                mdiSalesforce,
                mdiStocking,
                mdiCashRefund,
                mdiCashRemove,
                mdiHumanQueue,
                mdiBasket 
            },
        };
    },
     mounted() {
        console.log("landing loaded.");
    },
    created() {
        this.$store.commit("setUpdateUser", this.user);
        this.$store.commit("setUserPermission", this.permission);
        this.$store.commit("setBranch", this.branch);
        this.$store.commit("setDepartment", this.department);
    },
    methods: {
        showLanding(){
            console.log("baaack");
             
             this.setActive(this.active, "dashboard");
        },
        hideLanding(){
             console.log("not baaack");
            
             this.setActive(this.active, "dashboard89");
        },
        logout() {
            this.$router.push("logout");
            window.location.reload();
        },
        linkPage(slug) {
            this.$router.push(slug);
            this.setActive(this.active, "page_view");
            //  window.location.reload();
        },
    },
};
</script>
<style scoped>
.badge-secondary {
    font-size: 8px !important;
}
.badge-custom {
    background-color: #f47207;
    border-radius: 12px;
    padding: 10px;
}
.badge-custom > h4 {
    color: #fff;
}
.tab-btn {
    height: 160px !important;
    width: 160px !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-left: 6px;
}
.tab-btn:hover {
    cursor: pointer;
    animation: bubble 0.4s ease-out !important;
}
.tab-btn:active {
    cursor: pointer;
    background: #f47207;
}
@keyframes bubble {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}
.tab-btn > p {
    padding-top: 10px;
    overflow-wrap: break-word;
}
</style>
