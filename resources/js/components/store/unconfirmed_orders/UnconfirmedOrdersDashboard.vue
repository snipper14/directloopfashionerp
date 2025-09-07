<template>
    <div class="row">
        <div class="col-2">
            <POSideNav />
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
                                <div class="col-md-3"></div>
                                <div class="col-md-5">
                                    <b> <h5>Confirm Orders</h5></b>
                                </div>
                                <div class="col-md-4 float-md-right ">
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

                                                    <th scope="col">Qty</th>
                                                    <th scope="col">
                                                        Qty Delivered
                                                    </th>
                                                    <th scope="col">Total</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    class="small-tr"
                                                    v-for="(data,
                                                    i) in order_data.data"
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <confirm-orders
                    v-if="active.confirm_po"
                    v-on:dashboard-active="setActiveRefresh"
                    :supplier_data="supplier_data"
                    :tax_data="tax_data"
                    :pending_order="pending_order"
                />
            </div>
              <center v-else>
                    <b style="color:red;font-size:1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
        </div>
    </div>
</template>

<script>
import ConfirmOrders from "./ConfirmOrders.vue";
import Pagination from "../../utilities/Pagination.vue";
import POSideNav from "../purchase/POSideNav.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                confirm_po: false
            },
            pending_order: null,
            supplier_data: [],
            tax_data: [],
            order_data: [],
            search: "",

            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        ConfirmOrders,
        Pagination,
        POSideNav
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            handler() {
                this.fetchOrders(1);
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.fetchOrders(1);
            }
        }
    },
    methods: {
        async deleteOrder(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/po/destroy", {
                    params: {
                        order_no: data.order_no,
                        supplier_id: data.supplier_id
                    }
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.order_data.data.splice(i, 1);
                }
            }
        },
        async vendorApproval(data, i) {
            const shouldDelete = await this.confirmDialogue(
                "You are about to approve from vendor"
            );
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/po/vendorApproval", {
                    params: {
                        order_no: data.order_no,
                        supplier_id: data.supplier_id
                    }
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Approved ");
                    data.supplier_confirmation = "confirmed";
                }
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchOrders(1)]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchOrders(page) {
            const res = await this.getApi("data/po/fetchUnconfirmed", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : ""
                }
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },

        viewDetails(data) {
            this.pending_order = data;
            this.setActiveComponent("confirm_po");
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.pending_order = null;
            this.fetchOrders(1);
        }
    }
};
</script>
<style scoped></style>
