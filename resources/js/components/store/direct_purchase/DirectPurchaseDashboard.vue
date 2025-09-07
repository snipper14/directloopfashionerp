<template>
    <div class="row">
        <div class="col-2">
            <DPNav />
        </div>
        <div class="col-10">
            <div class="container" v-if="isReadPermitted">
                <div class="row justify-content-center" v-if="active.dashboard">
                    <div class="col-md-12">
                        <!-- <ImportExcel/> -->
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="col-md-3">
                                    <v-btn
                                        v-if="isWritePermitted"
                                        class="ma-2 v-btn-primary"
                                        @click="setActiveComponent('add_po')"
                                        color="primary"
                                        dark
                                    >
                                        <v-icon medium>{{
                                            icons.mdiPlusThick
                                        }}</v-icon>
                                        Add
                                    </v-btn>
                                </div>
                                <div class="col-md-5">
                                    <h3><b>Direct Purchase Orders</b></h3>
                                </div>
                                <div class="col-md-4 float-md-right">
                                    <input
                                        type="text"
                                        placeholder="Search.."
                                        class="form-control small-input"
                                        v-model="search"
                                    />
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <span
                                        class="badge approved-selected"
                                        style="color: #fff"
                                        >Waiting Internal Approval</span
                                    >
                                    <span
                                        class="badge progress-selected"
                                        style="color: #fff"
                                        >Order in progress</span
                                    >
                                </div>
                                <div class="row">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered mt-3"
                                        >
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        Order No
                                                    </th>
                                                    <th>Vendor</th>
                                                    <th>Deadline Date</th>
                                                    <th>Receipt Date</th>
                                                    <th>User</th>
                                                    <th>Progress</th>
                                                    <th>Internal Approval</th>

                                                    <th scope="col">Qty</th>
                                                    <th scope="col">
                                                        Qty Delivered
                                                    </th>
                                                    <th scope="col">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    class="small-tr"
                                                    v-for="(
                                                        data, i
                                                    ) in order_data.data"
                                                    :key="i"
                                                >
                                                    <td>
                                                        <router-link
                                                            to="#"
                                                            @click.native="
                                                                viewDetails(
                                                                    data
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                data.order_no
                                                            }}</router-link
                                                        >
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.supplier
                                                                ? data.supplier
                                                                      .company_name
                                                                : ""
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.order_deadline
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{ data.receipt_date }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.user
                                                                ? data.user.name
                                                                : ""
                                                        }}
                                                    </td>
                                                    <td
                                                        :class="{
                                                            'progress-selected':
                                                                data.progress ==
                                                                'progress',
                                                        }"
                                                    >
                                                        {{ data.progress }}
                                                    </td>
                                                    <td
                                                        :class="{
                                                            'approved-selected':
                                                                data.internal_confirmation ==
                                                                    'pending' &&
                                                                data.progress ==
                                                                    'completed',
                                                        }"
                                                    >
                                                        <router-link
                                                            to="#"
                                                            v-if="
                                                                isUpdatePermitted &&
                                                                data.internal_confirmation ==
                                                                    'pending'
                                                            "
                                                            @click.native="
                                                                continueOrder(
                                                                    data
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                data.internal_confirmation
                                                            }}</router-link
                                                        >
                                                        <div v-else> <router-link
                                                        to="#"
                                                        v-if="isUpdatePermitted"
                                                            @click.native="
                                                                disapproveOrder(
                                                                    data
                                                                )
                                                            "
                                                            >Disapprove
                                                            order</router-link
                                                        ></div>
                                                    </td>
                                                    <td>
                                                       
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.total_qty
                                                            )
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.total_qty_delivered
                                                            )
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.sum_sub_total
                                                            )
                                                        }}
                                                    </td>
                                                    <td>
                                                        <button
                                                            v-if="isAdmin"
                                                            class="btn btn-danger btn-sm"
                                                            @click="
                                                                deleteOrder(
                                                                    data,
                                                                    i
                                                                )
                                                            "
                                                        >
                                                            Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <Pagination
                                        v-if="order_data !== null"
                                        v-bind:results="order_data"
                                        v-on:get-page="fetchOrders"
                                    ></Pagination>
                                    Items Count {{ order_data.total }}

                                    <div class="col-4 d-flex">
                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchAll"
                                            worksheet="My Worksheet"
                                            name="filename.xls"
                                        >
                                            <v-icon medium>{{
                                                icons.mdiMicrosoftExcel
                                            }}</v-icon>
                                            Export Excel
                                        </export-excel>

                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchAll"
                                            worksheet="My Worksheet"
                                            type="csv"
                                            name="filename.xls"
                                        >
                                            <v-icon class="v-icon" medium>{{
                                                icons.mdiFileExcel
                                            }}</v-icon>
                                            Export CSV
                                        </export-excel>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <add-direct-purchase
                v-if="active.add_po"
                v-on:dashboard-active="setActiveRefresh"
                :pending_order="pending_order"
            />
            <direct-purchase-order-details
                v-if="active.view_details"
                v-on:dashboard-active="setActiveRefresh"
                :pending_order="pending_order"
            />
        </div>
    </div>
</template>

<script>
import DPNav from "./DPNav.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Pagination from "../../utilities/Pagination.vue";
import AddDirectPurchase from "./AddDirectPurchase.vue";
import DirectPurchaseOrderDetails from "./DirectPurchaseOrderDetails.vue";
export default {
    components: {
        DPNav,
        Pagination,
        AddDirectPurchase,
        DirectPurchaseOrderDetails,
    },
    data() {
        return {
            active: {
                dashboard: true,
                add_po: false,
                view_details: false,
            },
            isLoading: true,
            pending_order: null,
            search: "",
            order_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    watch: {
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.fetchOrders(1);
            }
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async disapproveOrder(data) {
            const approve = await this.confirmDialogue(
                "Do you want to disapprove orders"
            );
            if (approve) {
                this.showLoader();
                const res = await this.callApi(
                    "post",
                    "data/direct_po/disapproveOrder",
                    {
                        order_no: data.order_no,
                    }
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.successNotif("Approved ");
                  data.internal_confirmation='pending'
                  data.progress='progress'
                  
                }
            }
        },
        async deleteOrder(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/direct_po/destroy", {
                    params: {
                        order_no: data.order_no,
                    },
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.order_data.data.splice(i, 1);
                }
            }
        },
        viewDetails(data) {
            this.pending_order = data;
            this.setActiveComponent("view_details");
        },
        continueOrder(data) {
            this.pending_order = data;
            this.setActiveComponent("add_po");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.pending_order = null;
            this.fetchOrders(1);
        },
        async fetchAll() {
            this.showLoader();
            const res = await this.getApi("data/direct_po/fetch", {
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                var purchase_array = res.data;
                for (var i = 0; i < purchase_array.length; i++) {
                    purchase_array[i].branch = purchase_array[i].branch
                        ? purchase_array[i].branch.branch
                        : "";

                    purchase_array[i].user = purchase_array[i].user
                        ? purchase_array[i].user.name
                        : "";
                }

                return purchase_array;
            } else {
                this.swr("Server Error occurred");
            }
        },
        async fetchOrders(page) {
            const res = await this.getApi("data/direct_po/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                },
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetchOrders(1)]).then(function (results) {
                return results;
            });
            this.isLoading ? this.hideLoader() : "";
        },
    },
};
</script>
<style scoped>
.approved-selected {
    background: #af4448 !important;
}
.progress-selected {
    background: #00bcd4 !important;
}
</style>
