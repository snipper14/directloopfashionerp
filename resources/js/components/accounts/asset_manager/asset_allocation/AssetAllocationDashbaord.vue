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
                            <h5>Assets Allocations</h5>
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
                        <div class="row">
                            <div class="col-md-3">
                                <v-btn
                                    small
                                    color="secondary"
                                    @click="allocate_asset_modal = true"
                                    >+Add New</v-btn
                                >
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Alloc. Code</th>

                                    <th scope="col">Asset Name</th>
                                    <th scope="col">Qty</th>
                                    <th>Allocated From </th>
                                    <th>Allocated To </th>
                                    <th>Allocated To</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in asset_allocation_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.allocation_code }}
                                    </td>
                                    <td>
                                        {{ data.asset.asset_name }}
                                    </td>

                                    <td>
                                        {{ data.qty }}
                                    </td>
                                    <td>
                                        {{ data.allocated_from}}
                                    </td>
                                    <td>{{ data.allocated_to }}</td>
                                    <td>{{ data.employee.name }}</td>
                                      <td>{{ data.reason }}</td>
                                  
                                    <td>
                                        <button
                                        v-if="isUpdatePermitted"
                                        title="update"
                                            class="btn btn-primary custom-button"
                                            @click="revokeAsset(data.id,i)"
                                        >
                                           Revoke
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
                            v-if="asset_allocation_data !== null"
                            v-bind:results="asset_allocation_data"
                            v-on:get-page="fetchAssetAllocated"
                        ></pagination>
                        Items Count {{ asset_allocation_data.total }}
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
       
        
         <Modal v-model="allocate_asset_modal" width="1200px">
        <new-asset-allocation-modal
       v-if="allocate_asset_modal" 
       v-on:dashboard-active="setActiveRefresh"
        />
        </Modal>
        
        <notifications group="foo" />
    </div>
</template>

<script>
import AssetManagerNav from "../AssetManagerNav.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import NewAssetAllocationModal from './NewAssetAllocationModal.vue';

export default {
    data() {
        return {
            add_asset_modal: false,
            edit_asset_modal:false,
            allocate_asset_modal:false,
            active: {
                view_details: false,
                dashboard: true,
            },
            params: { search: "" },
            
            sum_amount_paid: 0,
            edit_data:null,
            asset_allocation_data: [],
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
        NewAssetAllocationModal,
      
    },
    mounted() {
        this.fetchAssetAllocated(1);
    },
    watch: {
         params: {
            deep: true,
            handler: _.debounce(function(val, old) {
                this.fetchAssetAllocated(1);
            }, 500)
        }
    },
    methods: {
       async deleteAsset(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/asset_allocation/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.w("deleted ");
                    this.asset_allocation_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
          async revokeAsset(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/asset_allocation/revoke/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.w("revoked ");
                    this.asset_allocation_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
         editData(data) {
            this.edit_data = data;
            this.edit_asset_modal=true
        },
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/asset_allocation/fetch",
                ""
            );
            this.hideLoader();
            var data_array = [];
            res.data.map((data) => {
                data_array.push({
                    allocation_code: data.allocation_code
                        ,
                    asset_name: data.asset.asset_name,
                    qty: data.qty,
                    allocated_from:data.allocated_from,
                    allocated_to: data.allocated_to ,
                    employee: data.employee.name,
                    reason: data.reason,

                       
                                  
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
            this.allocate_asset_modal=false
          
            this.fetchAssetAllocated(1);
        },

       
        async fetchAssetAllocated(page) {
            this.showLoader();
            const res = await this.getApi("data/asset_allocation/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.asset_allocation_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
