<template>
    <div class="row" >
        <div class="col-md-2" v-if=" isReadPermitted">
            <StockProductionNav />
        </div>
        <div class="col-md-10" >
            <div class="container" v-if="isDisplayPermitted && isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-4">
                                <v-btn
                                    v-if="isWritePermitted"
                                    small
                                    color="info"
                                    @click="raw_materials_modal = true"
                                >
                                    <v-icon color="white" medium>{{
                                        icons.mdiPlusThick
                                    }}</v-icon
                                    >Issue Raw Materials
                                </v-btn>
                            </div>
                        </div>
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="col-md-5">
                                    <h5><b> Production Dashboard</b></h5>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="d-flex justify-content around mt-3">
                                    <div class="d-flex flex-column">
                                        <label> From Datetime *</label>

                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.from"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <label> To Datetime *</label>
                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.to"
                                        ></date-picker>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Search</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            v-model="params.search"
                                        />
                                    </div>
                                    <div>
                                        <div class="form-group">
                                            <label
                                                for=""
                                                class="center-vertical"
                                                >Ongoing Production</label
                                            >
                                            <input
                                                v-model="is_complete"
                                                type="checkbox"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <center>
                                        <h4><b>Production Cost Report</b></h4>
                                    </center>
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    Production No
                                                </th>
                                                <th>Product</th>
                                                <th>Total Material Cost</th>
                                                <th>Qty Materials</th>
                                                <th>Qty Produced</th>
                                                <th>Product C.P</th>
                                                <th>Production Value</th>
                                                <th>Created at</th>
                                                <th>Prod Type</th>
                                                <th>Completion Date</th>

                                                <th>Production Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    value, i
                                                ) in production_data.data"
                                                :key="i"
                                            >
                                                <td scope="row">
                                                    <router-link
                                                        to="#"
                                                        @click.native="
                                                            viewDetails(value)
                                                        "
                                                        >{{
                                                            value.production_no
                                                        }}</router-link
                                                    >
                                                </td>
                                                <td scope="row">
                                                    {{
                                                        value.stock.product_name
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            value.sum_total_materials_cost
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{ value.sum_material_qty }}
                                                </td>
                                                <td>
                                                    {{ value.desired_qty }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            value.cost_price
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            value.cost_price *
                                                                value.desired_qty
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        formatDateTime(
                                                            value.created_at
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <span
                                                        v-if="
                                                            value.end_product ==
                                                            '0'
                                                        "
                                                        >End Prod</span
                                                    ><span v-else
                                                        >Raw Material</span
                                                    >
                                                </td>
                                                <td>
                                                    {{
                                                        formatDateTime(
                                                            value.production_date
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    <router-link
                                                        v-if="
                                                            value.production_status ==
                                                                'progress' &&
                                                            isUpdatePermitted
                                                        "
                                                        to="#"
                                                        @click.native="
                                                            updateMaterials(
                                                                value
                                                            )
                                                        "
                                                        >update</router-link
                                                    >
                                                </td>
                                                <td>
                                                    <span>
                                                        <v-btn
                                                            v-if="
                                                                value.production_status ==
                                                                    'progress' &&
                                                                isUpdatePermitted
                                                            "
                                                            small
                                                            dark
                                                            @click="
                                                                completeProduction(
                                                                    value,
                                                                    i
                                                                )
                                                            "
                                                        >
                                                            complete Production
                                                        </v-btn>
                                                        <p v-else>completed</p>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                   <Pagination
                            v-if="production_data !== null"
                            v-bind:results="production_data"
                            v-on:get-page="fetch"
                        ></Pagination>
                        Items Count {{ production_data.total }}
                          <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    type="xls"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                   <v-btn x-small title="download" color="primary">Export Excel</v-btn>
                                    
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="production.csv"
                                > <v-btn x-small  title="download" color="success">Export CSV</v-btn>
                                   
                                </export-excel>
                            </div>
                        </div>
                                <div class="row total-display mt-2">
                                    <span
                                        class="badge badge-secondary totals-badge"
                                        >Production Cost:
                                        {{
                                            cashFormatter(
                                                production_total.sum_total_materials_cost
                                            )
                                        }}</span
                                    >
                                    <span
                                        class="badge badge-warning totals-badge"
                                        >Total Materials Qty:
                                        {{
                                            cashFormatter(
                                                production_total.sum_material_qty
                                            )
                                        }}</span
                                    >
                                    <!-- <span class="badge badge-warning totals-badge"
                                >Total Produced: {{ cashFormatter(production_total.sum_total_produced) }}</span
                            > -->

                                    <!-- <span class="badge badge-warning totals-badge"
                                >Total Qty Produced: {{ cashFormatter(sum_qty_produced) }}</span
                            > -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <unauthorized />
            </div>
        </div>
        <Modal
            v-model="raw_materials_modal"
            title="Add Production Materials"
            width="1000px"
        >
            <add-raw-materials-modal
                v-if="raw_materials_modal"
                v-on:dismiss-modal="dismiss_raw_material"
            />
        </Modal>
        <Modal
            v-model="update_raw_materials_modal"
            title="update Production Materials"
            width="1000px"
        >
            <update-raw-materials-modal
                v-if="update_raw_materials_modal && row_data"
                :row_data="row_data"
                v-on:dismiss-modal="dismiss_raw_material"
            />
        </Modal>

        <Modal
            v-model="production_details_modal"
            title=" Production Details"
            width="1000px"
        >
            <production-details-modal
                v-if="production_details_modal && row_data"
                :row_data="row_data"
                v-on:dismiss-modal="dismiss_raw_material"
            />
        </Modal>
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";

import AddRawMaterialsModal from "./AddRawMaterialsModal.vue";
import UpdateRawMaterialsModal from "./UpdateRawMaterialsModal.vue";
import ProductionDetailsModal from "./ProductionDetailsModal.vue";
import StockProductionNav from "./StockProductionNav.vue";
import Pagination from '../../../utilities/Pagination.vue';
export default {
    components: {
        Unauthorized,
        UpdateRawMaterialsModal,
        AddRawMaterialsModal,
        ProductionDetailsModal,
        StockProductionNav,
        Pagination,
    },
    data() {
        return {
            active: {
                stock_take_component: false,
                stock_update_component: false,
            },
            production_total: {},
            to: null,
            from: null,
            params: {
                search: "",
                from: null,
                to: null,
            },
            sum_qty_produced: 0,
            isLoading: true,
            is_complete: false,
            production_data: [],
            raw_materials_modal: false,
            update_raw_materials_modal: false,
            production_details_modal: false,
            row_data: null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        is_complete: {
            handler: _.debounce(function () {
                this.concurrentApiReq();
            }, 100),
        },
    },
    mounted() {
        this.concurrentApiReq();
    },

    methods: {
        async fetchExcel(){
             const res = await  this.getApi("data/stock_production/fetch", {
                params: {
                 
                    ...this.params,

                    is_complete: this.is_complete,
                },
            });

             const data_array=[]
             res.data.map((value) => {
                    data_array.push({
                        id: (value.id),
                        production_no: value.production_no,
                        name:value.stock.product_name,
                        sum_total_materials_cost: value.sum_total_materials_cost,
                        sum_material_qty:value.sum_material_qty,
                       
                        desired_qty: value.desired_qty,
                        created_at:this.formatDateTime(
                                                            value.created_at
                                                        ),
                        production_date:this.formatDateTime(
                                                            value.production_date
                                                        ),
                       
                       
                    });
                });
                return data_array;
        },
        async viewDetails(value) {
            this.row_data = value;
            this.production_details_modal = true;
        },
        async completeProduction(value, i) {
            const confirm = await this.confirmDialogue(
                "You are about to complete production"
            );
            if (!confirm) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/stock_production/completeProduction",
                {
                    end_product: value.end_product,
                    desired_qty: value.desired_qty,
                    stock_id: value.stock_id,
                    production_no: value.production_no,
                    department_id: value.department_id,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Production completed");
                this.production_data.data[i].production_status = "completed";
            } else {
                this.form_error(res);
            }
        },
        async updateMaterials(value) {
            this.row_data = value;
            this.update_raw_materials_modal = true;
        },
        dismiss_raw_material() {
            this.raw_materials_modal = false;
            this.update_raw_materials_modal = false;
            this.production_details_modal = false;
            this.concurrentApiReq();
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetch(1), this.fetchTotal()]);
            this.isLoading ? this.hideLoader() : "";
        },
        async fetch(page) {
            const res = await this.getApi("data/stock_production/fetch", {
                params: {
                    page: page,
                    ...this.params,

                    is_complete: this.is_complete,
                },
            });

            if (res.status == 200) {
                this.production_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchTotal() {
            const res = await this.getApi("data/stock_production/fetchTotal", {
                params: {
                    ...this.params,

                    is_complete: this.is_complete,
                },
            });

            if (res.status == 200) {
                this.production_total = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchTotalQtyProduced() {
            const res = await this.getApi(
                "data/stock_production/fetchTotalQtyProduced",
                {
                    params: {
                        ...this.params,

                        is_complete: this.is_complete,
                    },
                }
            );

            if (res.status == 200) {
                this.sum_qty_produced = res.data.sum_qty_produced;
            } else {
                this.swr("Server error occurred");
            }
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
