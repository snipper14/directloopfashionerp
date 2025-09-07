<template>
    <div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <v-btn color="primary" @click="$emit('dashboard-active')"
                    >Back</v-btn
                >
                <div>
                    <div>
                        <div class="card">
                            <div class="card-header">
                                <h3>
                                    <b>ITEMIZED GOODS RECEIVED NOTES(GRN)</b>
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
                                                  <th scope="col">
                                                    Qty Delivered
                                                </th>
  <th>Total</th>
                                                <th>B.price</th>
                                                <th>S.price</th>
                                                <th>Batchno</th>
                                                <th scope="col">Delivery No</th>
                                                <th scope="col">Order No</th>
                                                <th>Vendor</th>

                                                <th>Received Date</th>
                                                <th>Expiry Date</th>

                                                <th scope="col">Qty Order</th>
                                              
                                                <th>Tax Amt</th>
                                              
                                                <th>Branch</th>
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
                                                    {{
                                                        cashFormatter(
                                                            data.qty_delivered
                                                        )
                                                    }}
                                                </td>
                                                  <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sub_total
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.stock
                                                                .purchase_price
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.stock
                                                                .selling_price
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{ data.batch_no }}
                                                </td>

                                                <td>
                                                    {{ data.delivery_no }}
                                                </td>
                                                <td>
                                                    {{ data.order_no }}
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
                                                    {{ data.received_date }}
                                                </td>
                                                <td>{{ data.expiry_date }}</td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.qty_ordered
                                                        )
                                                    }}
                                                </td>

                                               

                                                <td>
                                                    {{
                                                        cashRounded(
                                                            data.tax_amount
                                                        )
                                                    }}
                                                </td>
                                              
                                                <td>{{data.branch.branch}}</td>
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
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
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
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import POSideNav from "../purchase/POSideNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import DownloadGrn from "./DownloadGrn.vue";
import DeliveryDetails from "./DeliveryDetails.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                print_grn: false,
            },
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
    },
    watch: {
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
                "data/po_receivable/fetchItemizedGrn",
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
                    purchase_price: data.stock.purchase_price,
                      selling_price: data.stock.selling_price,
                    delivery_no: data.delivery_no,
                    order_no: data.order_no,
                    supplier: data.supplier.company_name,
                    received_date: data.received_date,
                    expiry_date: data.expiry_date,
                    user: data.user.name,

                    qty_ordered: data.qty_ordered,
                    qty_delivered: data.qty_delivered,

                    tax_amount: data.tax_amount,
                    sub_total: data.sub_total,

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
                "data/po_receivable/fetchItemizedGrn",
                {
                    params: {
                        page,
                        ...this.params,
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
