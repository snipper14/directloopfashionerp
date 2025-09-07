<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <asset-manager-nav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h5>Assets Reports</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="params.search"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <b> Total Asset Value:</b>
                            {{ cashFormatter(asset_total.total_purchase) }}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <v-btn
                                    small
                                    color="secondary"
                                    @click="add_asset_modal = true"
                                    >Add New</v-btn
                                >
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Asset Code</th>

                                    <th scope="col">Asset Name</th>
                                    <th scope="col">Qty</th>
                                    <th>Purchase Price</th>
                                    <th>Purchase Date</th>
                                    <th>Category</th>
                                    <th>Serial No /Model</th>
                                    <th scope="col">Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in asset_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.asset_code }}
                                    </td>
                                    <td>
                                        {{ data.asset_name }}
                                    </td>

                                    <td>
                                        {{ data.qty }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.purchase_price) }}
                                    </td>
                                    <td>{{ data.purchase_date }}</td>
                                    <td>{{ data.asset_category.category }}</td>
                                    <td>
                                        {{ data.serial_no }} /{{
                                            data.model_name
                                        }}
                                    </td>
                                    <td>
                                        <button
                                            v-if="isUpdatePermitted"
                                            title="update"
                                            class="btn btn-primary custom-button"
                                            @click="sendMaintenance(data)"
                                        >
                                            Send To Maintenance
                                        </button>
                                        <button
                                            v-if="isUpdatePermitted"
                                            title="update"
                                            class="btn btn-primary custom-button"
                                            @click="editData(data)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="isDeletePermitted"
                                            title="delete"
                                            class="btn btn-danger custom-button"
                                            @click="deleteAsset(data.id, i)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <pagination
                            v-if="asset_data !== null"
                            v-bind:results="asset_data"
                            v-on:get-page="fetchAssets"
                        ></pagination>
                        Items Count {{ asset_data.total }}
                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportExcel"
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
                                    :fetch="exportExcel"
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
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <Modal v-model="add_asset_modal" width="1200px">
            <new-asset-modal
                v-if="add_asset_modal"
                v-on:dashboard-active="setActiveRefresh"
            />
        </Modal>

        <Modal v-model="edit_asset_modal" width="1200px">
            <edit-asset-modal
                v-if="edit_asset_modal"
                v-bind:edit_data="edit_data"
                v-on:dashboard-active="setActiveRefresh"
            />
        </Modal>

        <Modal v-model="maintenance_modal" width="800px">
            <maintenance-modal
                v-if="maintenance_modal"
                v-bind:edit_data="edit_data"
                v-on:dashboard-active="setActiveRefresh"
            />
        </Modal>

        <notifications group="foo" />
    </div>
</template>

<script>
import AssetManagerNav from "./AssetManagerNav.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";
import NewAssetModal from "./NewAssetModal.vue";
import EditAssetModal from "./EditAssetModal.vue";
import MaintenanceModal from "./MaintenanceModal.vue";
export default {
    data() {
        return {
            add_asset_modal: false,
            edit_asset_modal: false,
            maintenance_modal: false,
            active: {
                view_details: false,
                dashboard: true,
            },
            params: { search: "" },
asset_total:0,
            sum_amount_paid: 0,
            edit_data: null,
            asset_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        AssetManagerNav,
        Unauthorized,
        Pagination,
        NewAssetModal,
        EditAssetModal,
        MaintenanceModal,
    },
    mounted() {
        this.fetchAssets(1);
        this.fetchTotal()
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchAssets(1);
            }, 500),
        },
    },
    methods: {
        async deleteAsset(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/assets/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.w("deleted ");
                    this.asset_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        editData(data) {
            this.edit_data = data;
            this.edit_asset_modal = true;
        },
        sendMaintenance(data) {
            this.edit_data = data;
            this.maintenance_modal = true;
        },
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_payment/exportExcel",
                ""
            );
            this.hideLoader();
            var data_array = [];
            res.data.map((array_item) => {
                data_array.push({
                    supplier: array_item.supplier
                        ? array_item.supplier.company_name
                        : "",
                    amount_paid: array_item.amount_paid,
                    outstanding_balance: array_item.outstanding_balance,
                    ref_no: array_item.ref_no,
                    date_paid: array_item.date_paid,
                    payment_details: array_item.payment_details,
                    user: array_item.user ? array_item.user.name : "",
                });
            });
            return data_array;
        },

        viewStaements(data) {
            this.supplier_info = data;
            this.setActiveComponent("view_stamt");
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.add_asset_modal = false;
            this.edit_asset_modal = false;
            this.maintenance_modal=false
            this.fetchAssets(1);
        },

        async fetchTotal(page) {
         
            const res = await this.getApi("data/assets/fetchTotal", {
                params: {
                    
                    ...this.params,
                },
            });
          
            if (res.status === 200) {
                this.asset_total = res.data;
            } else {
                
                this.swr("Server error try again later");
            }
        },
        async fetchAssets(page) {
            this.showLoader();
            const res = await this.getApi("data/assets/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.asset_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
