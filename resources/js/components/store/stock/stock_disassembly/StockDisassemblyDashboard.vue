<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <stock-production-nav />
            </div>
            <div class="col-md-10" v-if="isDisplayPermitted">
                <div class="row">
                    <div class="col-4">
                        <v-btn
                            v-if="isWritePermitted"
                            small
                            title="write"
                            color="info"
                            @click="raw_materials_modal = true"
                        >
                            <v-icon color="white" medium>{{
                                icons.mdiPlusThick
                            }}</v-icon
                            >Start Disassembly
                        </v-btn>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Product Disassembly</div>

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
                        </div>
                        <div class="table-responsive">
                            <center>
                                <h4><b>Disassembly Report</b></h4>
                            </center>
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Production No</th>
                                        <th>Issue no</th>
                                        <th>Parent Product</th>
                                        <th>Parent Sum Qty</th>
                                        <th>Parent Total Cost</th>
                                        <th>Qty Child Element</th>
                                        <th>Child Total Cost</th>
                                        <th>Date</th>
                                        <th>Created at</th>
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
                                        <th>{{ value.issue_no }}</th>
                                        <td scope="row">
                                            {{ value.stock.product_name }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(value.parent_qty)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    value.parent_totalcost
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ value.sum_child_qty }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    value.sum_child_totalcost
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ value.disassembly_date }}
                                        </td>
                                        <td>
                                            {{
                                                formatDateTime(value.created_at)
                                            }}
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
                                    name="disassembly.xls"
                                >
                                    <v-btn
                                        x-small
                                        title="download"
                                        color="primary"
                                        >Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="disassembly.csv"
                                >
                                    <v-btn
                                        x-small
                                        title="download"
                                        color="success"
                                        >Export CSV</v-btn
                                    >
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized/></div>
        </div>

        <Modal
            v-model="raw_materials_modal"
            title="Breaking Down"
            width="1000px"
        >
            <break-component-modal
                v-if="raw_materials_modal"
                v-on:dismiss-modal="dismiss_raw_material"
            />
        </Modal>
        <Modal
            v-model="show_details_modal"
            title="Material Details"
            width="1000px"
        >
            <break-down-details
                v-if="show_details_modal"
                :details_data="details_data"
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
import StockProductionNav from "../stock_production/StockProductionNav.vue";
import BreakComponentModal from "./BreakComponentModal.vue";
import BreakDownDetails from "./BreakDownDetails.vue";
import Unauthorized from '../../../utilities/Unauthorized.vue';
import Pagination from '../../../utilities/Pagination.vue';
export default {
    components: { StockProductionNav, BreakComponentModal, BreakDownDetails, Unauthorized, Pagination },
    data() {
        return {
            details_data: null,
            show_details_modal: false,
            production_data: [],
            isLoading: true,
            raw_materials_modal: false,
            params: {
                search: "",
                from: null,
                to: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        viewDetails(value) {
            this.details_data = value;
            this.show_details_modal = true;
        },
        async fetchExcel() {
            const res = await this.getApi("data/stock_disassembly/fetch", {
                params: {
                    ...this.params,
                },
            });

            const data_array = [];
            res.data.map((value) => {
                data_array.push({
                    id: value.id,
                    production_no: value.production_no,
                    issue_no: value.issue_no,
                    name: value.stock.product_name,
                    parent_qty: value.parent_qty,
                    parent_totalcost: value.parent_totalcost,

                    sum_child_qty: value.sum_child_qty,
                    sum_child_totalcost: value.sum_child_totalcost,
                    created_at: this.formatDateTime(value.created_at),
                    disassembly_date: this.formatDateTime(
                        value.disassembly_date
                    ),
                });
            });
            return data_array;
        },
        dismiss_raw_material() {
            this.raw_materials_modal = false;

            this.concurrentApiReq();
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetch(1), this.fetchTotal()]);
            this.isLoading ? this.hideLoader() : "";
        },
        async fetch(page) {
            const res = await this.getApi("data/stock_disassembly/fetch", {
                params: {
                    page: page,
                    ...this.params,
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
    },
};
</script>
