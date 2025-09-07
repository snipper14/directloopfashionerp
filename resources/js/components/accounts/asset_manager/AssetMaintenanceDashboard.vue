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
                            <h5>Maintenance Reports</h5>
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
                 
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Asset Code</th>

                                    <th scope="col">Asset Name</th>

                                    <th>Start Date</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th scope="col">Priority</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in asset_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.asset.asset_code }}
                                    </td>
                                    <td>
                                        {{ data.asset.asset_name }}
                                    </td>

                                    <td>
                                        {{ data.maintenance_start_date }}
                                    </td>

                                    <td>{{ data.description }}</td>
                                    <td>{{ data.type }}</td>
                                    <td>
                                        {{ data.priority }}
                                    </td>
                                    <td>{{ data.user.name }}</td>
                                    <td>
                                        <button
                                            v-if="isUpdatePermitted"
                                            title="update"
                                            class="btn btn-primary custom-button"
                                            @click="editData(data)"
                                        >
                                            Edit Status
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
                            v-on:get-page="fetchAssetMaintenance"
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

        <Modal v-model="edit_asset_modal" width="410px">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status*</label>
                        <Select v-model="form_data.type">
                            <Option
                                v-bind:value="data.name"
                                v-for="(data, i) of maintenance_status_select"
                                :key="i"
                            >
                                {{ data.name }}
                            </Option>
                        </Select>
                    </div>
                    <button class="btn btn-primary" @click="updateS()">Update</button>
                </div>
            </div>
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
            maintenance_status_select: [
                { name: "New" },
                { name: "In Progress" },
                { name: "Completed" },
                { name: "Cancelled" },
            ],
            add_asset_modal: false,
            edit_asset_modal: false,
            maintenance_modal: false,
            active: {
                view_details: false,
                dashboard: true,
            },
            params: { search: "" },
            form_data: { type: null, id: null },
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
        this.fetchAssetMaintenance(1);
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchAssetMaintenance(1);
            }, 500),
        },
    },
    methods: {
        async deleteAsset(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/asset_maintenance/destroy/" + id,
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
        async updateS(){
            const res = await this.callApi(
                    "put",
                    "data/asset_maintenance/updateStatus",
                    this.form_data
                );

                if (res.status === 200) {
                    this.w("updated ");
                    this.edit_asset_modal=false
                   this.fetchAssetMaintenance(1)
                } else {
                    this.form_error(res)
                }
        },
        editData(data) {
            this.edit_data = data;
            this.form_data.id=this.edit_data.id
            this.edit_asset_modal = true;
        },
        sendMaintenance(data) {
            this.edit_data = data;
            this.maintenance_modal = true;
        },
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi("data/asset_maintenance/fetch", "");
            this.hideLoader();
            var data_array = [];
            res.data.map((data) => {
                data_array.push({
                    asset_code: data.asset.asset_code,
                    asset_name: data.asset.asset_name,
                    maintenance_start_date: data.maintenance_start_date,
                    description: data.description,
                    type: data.type,
                    priority: data.priority,
                    user: data.user.name,
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
            this.maintenance_modal = false;
            this.fetchAssetMaintenance(1);
        },

        async fetchAssetMaintenance(page) {
            this.showLoader();
            const res = await this.getApi("data/asset_maintenance/fetch", {
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
