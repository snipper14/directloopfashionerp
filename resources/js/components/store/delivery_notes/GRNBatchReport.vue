<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2">
                <POSideNav />
            </div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div>
                    <div>
                        <v-btn color="primary" @click="add_batch_modal = true"
                            >Add Batch</v-btn
                        >
                        <div class="card">
                            <div class="card-header">
                                <h3>
                                    <b>BATCH (GRN) REPORT</b>
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <div class="form-group">
                                        <label for="">Search</label>
                                        <input
                                            type="text"
                                            v-model="params.search"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Delivery No</label>
                                        <input
                                            type="text"
                                            v-model="params.delivery_no"
                                            class="form-control"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label for="">Batch No</label>
                                        <input
                                            type="text"
                                            v-model="params.batch_no"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="supp_dropdown">
                                        <label>Supplier Name*</label>

                                        <div class="form-group">
                                            <treeselect
                                                v-model="params.supplier_id"
                                                :multiple="false"
                                                :options="supplier_options"
                                                :show-count="true"
                                                placeholder="Select "
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="d-flex flex-column">
                                        <label> From Received Date*</label>

                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.from"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column mr-2">
                                        <label> To Received Date *</label>
                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.to"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <label> From Expiry Date*</label>

                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.from_expiry"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column mr-2">
                                        <label> To Expiry Date *</label>
                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.to_expiry"
                                        ></date-picker>
                                    </div>
                                </div>
                                <div
                                    class="table-responsive"
                                    v-if="grouped_report"
                                >
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3 custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th>Item code</th>
                                                <th>Name</th>
                                                <th>Batchno</th>

                                                <th>Vendor</th>

                                                <th>Expiry Date <sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_expiry_date
                                                                "
                                                                sort-key="sort_expiry_date"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            /></th>

                                                <th scope="col">
                                                    Qty Received
                                                </th>
                                                <th scope="col">Qty Sold</th>
                                                <th>Branch</th>
                                                <th>Update qty</th>
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
                                                    {{ data.stock.code }}
                                                </td>

                                                <td>
                                                    {{ data.stock.name }}
                                                </td>
                                                <td>
                                                    {{ data.batch_no }}
                                                </td>

                                                <td>
                                                    {{
                                                        data.supplier
                                                            ? data.supplier
                                                                  .company_name
                                                            : ""
                                                    }}
                                                </td>

                                                <td>{{ data.expiry_date }}</td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.qty_delivered
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ data.qty_sold }}</td>
                                                <td>
                                                    {{ data.branch.branch }}
                                                </td>
                                                <td>
                                                    <v-btn
                                                        v-if="isUpdatePermitted"
                                                        title="UPDATE"
                                                        x-small
                                                        @click="updateQty(data)"
                                                        >Update Qty</v-btn
                                                    >
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <Pagination
                                    v-if="order_data !== null"
                                    v-bind:results="order_data"
                                    v-on:get-page="fetchItemizedGrn"
                                ></Pagination>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="success" small
                                        >Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-btn color="info" small>Export CSV</v-btn>
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
        <Modal v-model="add_batch_modal" width="1200px">
            <add-batch-stock v-if="add_batch_modal" />
        </Modal>
        <Modal v-model="update_model">
            <v-app v-if="update_model">
                <h4>
                    <b>{{ data.stock.name }} Batch No. {{ data.batch_no }}</b>
                </h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Batch Qty</label>
                            <input
                                type="number"
                                v-model="update_form_data.qty"
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <v-btn
                            v-if="isUpdatePermitted"
                            title="UPDATE"
                            @click="submitUpdateBatchQty"
                            color="info"
                            >Update Current Stock</v-btn
                        >
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Sold Qty</label>
                            <input
                                type="number"
                                v-model="update_form_data.sold_qty"
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <v-btn
                            v-if="isUpdatePermitted"
                            title="UPDATE"
                            @click="submitUpdateBatchSoldQty"
                            color="info"
                            >Update Sold</v-btn
                        >
                    </div>
                </div>
            </v-app>
        </Modal>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import POSideNav from "../purchase/POSideNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import DownloadGrn from "./DownloadGrn.vue";
import DeliveryDetails from "./DeliveryDetails.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import AddBatchStock from "./AddBatchStock.vue";
import SortButtons from '../../utilities/SortButtons.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                print_grn: false,
            },
            add_batch_modal: false,
            data: null,
            update_model: false,
            grouped_report: true,
            itemized_report: false,
            show_loading: true,
            delivery_modal: false,
            grn_download_data: null,
            order_details: null,
            delivery_details: null,
            grn_totals_data: {},
            params: {
                search: "",
                order_no: "",
                delivery_no: "",
                supplier_id: null,
                from: null,
                to: null,
                from_expiry: null,
                to_expiry: null,
                batch_no: "",
            },
             sort_params: { sort_expiry_date: "", },
            update_form_data: {
                qty: 0,
                id: "",
                sold_qty: 0,
            },

            order_data: [],
            supplier_data: [],
            supplier_options: null,
        };
    },
    components: {
        Treeselect,
        POSideNav,
        Pagination,
        DownloadGrn,
        DeliveryDetails,
        Unauthorized,
        AddBatchStock,
        SortButtons,
    },
    watch: {
         sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
        params: {
            handler() {
                this.show_loading = false;
                this.concurrentApiReq();
            },
            deep: true,
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        updateQty(data) {
            this.update_form_data.id = data.id;
            this.data = data;
            this.update_model = true;
        },
        async submitUpdateBatchSoldQty() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/po_receivable/submitUpdateBatchSoldQty",
                this.update_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Updated successfully");

                this.update_form_data.qty = 0;

                this.update_form_data.sold_qty = 0;

                this.concurrentApiReq();
            } else {
                this.form_error(res);
            }
        },
        async submitUpdateBatchQty() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/po_receivable/submitUpdateBatchQty",
                this.update_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Updated successfully");

                this.update_form_data.qty = 0;

                this.update_form_data.sold_qty = 0;
                this.concurrentApiReq();
            } else {
                this.form_error(res);
            }
        },

        viewDetails(data) {
            this.delivery_details = data;
            this.delivery_modal = true;
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },

        async concurrentApiReq() {
            this.show_loading ? this.showLoader() : null;
            await Promise.all([
                this.fetchItemizedGrn(1),
                this.getAllSuppliers(),
            ]).then(function (results) {
                return results;
            });
            this.show_loading ? this.hideLoader() : null;
        },

        async fetchExcel() {
            const res = await this.getApi(
                "data/po_receivable/fetchItemizedGrnBatch",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    code: data.stock.code,
                    name: data.stock.name,

                    expiry_date: data.expiry_date,
                    user: data.user.name,

                    qty: data.qty_delivered,
                    qty_sold: data.qty_sold,

                    batch_no: data.batch_no,
                });
            });
            return data_array;
        },
        modifyProductKey() {
            let modif = this.supplier_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.company_name,
                };
            });
            return modif;
        },
        async getAllSuppliers() {
            const res = await this.getApi("data/suppliers/fetchAll", "");

            if (res.status === 200) {
                this.supplier_data = res.data.results;
                this.supplier_options = this.modifyProductKey();
            } else {
                this.swr("Server error try again later");
            }
        },

        async fetchItemizedGrn(page) {
            const res = await this.getApi(
                "data/po_receivable/fetchItemizedGrnBatch",
                {
                    params: {
                        page,
                        ...this.params,
                        ...this.sort_params
                    },
                }
            );
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped>
.supp_dropdown {
    width: 40% !important;
}
#printMe {
    visibility: hidden;
}
</style>
