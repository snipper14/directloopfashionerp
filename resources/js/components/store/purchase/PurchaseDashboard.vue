<template>
    <div>
        <div class="row" v-if="isReadPermitted">
            <div class="col-2">
                <POSideNav />
            </div>
            <div class="col-10">
                <div class="">
                    <div
                        class="row justify-content-center"
                        v-if="active.dashboard"
                    >
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
                                            @click="
                                                setActiveComponent('add_po')
                                            "
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
                                        <h5>Purchase Orders</h5>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div
                                            class="d-flex flex-row justify-content-around"
                                        >
                                            <div class="form-group">
                                                <label> From *</label
                                                ><date-picker
                                                    v-model="params.from"
                                                    valueType="format"
                                                ></date-picker>
                                            </div>
                                            <div class="form-group">
                                                <label> To *</label
                                                ><date-picker
                                                    v-model="params.to"
                                                    valueType="format"
                                                ></date-picker>
                                            </div>
                                            <div class="form-group">
                                                <button
                                                    @click="concurrentApiReq()"
                                                    class="btn btn-secondary btn-sm"
                                                >
                                                    Filter Date
                                                </button>
                                            </div>
                                            <div class="form-group">
                                                <input
                                                    type="text"
                                                    v-model="search"
                                                    placeholder="Search"
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <span
                                            class="badge approved-selected"
                                            style="color: #fff"
                                            >Waiting Internal Approval</span
                                        >
                                        <span
                                            class="badge progress-selected"
                                            style="color: #fff"
                                            >order in progress</span
                                        >
                                    </div>
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table
                                                class="table table-sm table-striped table-bordered mt-3 custom-table"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th scope="col">
                                                            Order No
                                                        </th>
                                                        <th>Vendor <sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_supplier
                                                                "
                                                                sort-key="sort_supplier"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            /></th>
                                                        <th>Deadline Date</th>
                                                        <th>
                                                            Receipt Date
                                                            <sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_receipt_date
                                                                "
                                                                sort-key="sort_receipt_date"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            />
                                                        </th>
                                                        <th>User<sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_user
                                                                "
                                                                sort-key="sort_user"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            /></th>
                                                        <th>Progress</th>
                                                        <th>
                                                            Internal Approval
                                                        </th>

                                                        <th scope="col">Qty</th>
                                                        <th scope="col">
                                                            Qty Delivered
                                                        </th>
                                                        <th scope="col">
                                                            Total <sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_sum_sub_total
                                                                "
                                                                sort-key="sort_sum_sub_total"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            />
                                                        </th>
                                                        <th>Branch</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        class="small-tr"
                                                        v-for="(
                                                            data, i
                                                        ) in order_data.data"
                                                        :key="i"
                                                        :class="{
                                                            'approved-selected':
                                                                data.internal_confirmation ==
                                                                    'pending' &&
                                                                data.progress ==
                                                                    'completed',
                                                        }"
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
                                                                    ? data
                                                                          .supplier
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
                                                            {{
                                                                data.receipt_date
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                data.user
                                                                    ? data.user
                                                                          .name
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
                                                                    data.progress
                                                                }}</router-link
                                                            >
                                                        </td>
                                                        <td>
                                                            {{
                                                                data.internal_confirmation
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
                                                        <td>
                                                            {{
                                                                data.branch
                                                                    .branch
                                                            }}
                                                        </td>
                                                        <td>
                                                            <v-btn
                                                                v-if="
                                                                    isDeletePermitted
                                                                "
                                                                x-small
                                                                color="danger"
                                                                @click="
                                                                    deleteOrder(
                                                                        data,
                                                                        i
                                                                    )
                                                                "
                                                            >
                                                                Delete
                                                            </v-btn>
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
                                                :fetch="getAllPO"
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
                                                :fetch="getAllPO"
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

                    <add-purchase-order
                        v-if="active.add_po"
                        v-on:dashboard-active="setActiveRefresh"
                        :supplier_data="supplier_data"
                        :tax_data="tax_data"
                        :pending_order="pending_order"
                    >
                    </add-purchase-order>
                    <purchase-order-details
                        v-if="active.view_details"
                        v-on:dashboard-active="setActiveNoRefresh"
                        :pending_order="pending_order"
                    />
                </div>
            </div>
        </div>
        <div v-else><unauthorized /></div>
    </div>
</template>

<script>
import AddPurchaseOrder from "./AddPurchaseOrder.vue";
import PurchaseOrderDetails from "./PurchaseOrderDetails.vue";
import Pagination from "../../utilities/Pagination.vue";
import POSideNav from "./POSideNav.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../utilities/Unauthorized.vue";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_po: false,
                view_details: false,
            },
            sort_params: { sort_receipt_date: "", sort_supplier: "",sort_user:"" ,sort_sum_sub_total:""},
            totals_summary: {},
            params: {
                from: null,
                to: null,
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
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        AddPurchaseOrder,
        PurchaseOrderDetails,
        Pagination,
        POSideNav,
        Unauthorized,
        SortButtons,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.fetchOrders(1);
            }
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        async deleteOrder(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/po/destroy", {
                    params: {
                        order_no: data.order_no,
                        supplier_id: data.supplier_id,
                    },
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
                        supplier_id: data.supplier_id,
                    },
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Approved ");
                    data.supplier_confirmation = "confirmed";
                }
            }
        },
        async getTax() {
            const res = await this.getApi("data/tax/fetch", "");

            if (res.status == 200) {
                this.tax_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async getAllPO() {
            this.showLoader();
            const res = await this.getApi("data/po/fetchAll", "");
            this.hideLoader();
            if (res.status === 200) {
                var purchase_array = res.data;
                for (var i = 0; i < purchase_array.length; i++) {
                    purchase_array[i].branch = purchase_array[i].branch
                        ? purchase_array[i].branch.branch
                        : "";
                    purchase_array[i].supplier = purchase_array[i].supplier
                        ? purchase_array[i].supplier.company_name
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
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.getAllSuppliers(),
                this.getTax(),
                this.fetchOrders(1),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchOrders(page) {
            const res = await this.getApi("data/po/fetch", {
                params: {
                    page,
                    ...this.params,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.sort_params,
                },
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchTotal(page) {
            const res = await this.getApi("data/po/fetchTotals", {
                params: {
                    page,
                    ...this.params,
                    search: this.search.length >= 2 ? this.search : "",
                },
            });
            if (res.status === 200) {
                console.log(JSON.stringify(res.data));
                this.totals_summary = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },

        async getAllSuppliers() {
            const res = await this.getApi("data/suppliers/fetchAll", "");
            if (res.status === 200) {
                this.supplier_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },

        continueOrder(data) {
            this.pending_order = data;

            this.setActiveComponent("add_po");
        },
        viewDetails(data) {
            this.pending_order = data;
            this.setActiveComponent("view_details");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.pending_order = null;
            this.fetchOrders(1);
        },

        setActiveNoRefresh() {
            this.setActive(this.active, "dashboard");
            this.pending_order = null;
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
