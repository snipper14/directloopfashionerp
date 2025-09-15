<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Product Details Reports</div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
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
                                <label for="">Search</label>
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Created at</th>
                                        <th scope="col">Purchase Date</th>
                                         

                                        <th scope="col">Stock</th>
                                        <th scope="col">Unit</th>
                                        <th>
                                            Qty Sold
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_qty
                                                "
                                                sort-key="sort_qty"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Cost P. <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_cost_price
                                                "
                                                sort-key="sort_cost_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th>Price
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_price
                                                "
                                                sort-key="sort_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th>Discount % <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_discount
                                                "
                                                sort-key="sort_discount"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th scope="col">
                                            Sold Amount <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total
                                                "
                                                sort-key="sort_total"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Vat</th>

                                        <th>Order No</th>
                                        <th>Batchno</th>
                                        <th>Purchase Date</th>
                                        <th scope="col">User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                    <td>{{ data.supplier ? data.supplier.company_name : '' }}</td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.purchase_date }}</td>
                                        <td>
                                            {{ data.stock.product_name }}
                                        </td>
                                        <td>
                                            {{ data.unit.name }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.qty) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.cost_price) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.price) }}
                                        </td>
                                        <td>{{ cashFormatter(data.discount) }}%</td>
                                        <td>
                                            {{ cashFormatter(data.row_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_vat) }}
                                        </td>

                                        <td>
                                            {{ data.order_no }}
                                        </td>
                                        <td>
                                            {{ data.batch_no }}
                                        </td>
                                        <td>
                                            {{ data.purchase_date }}
                                        </td>
                                        <td>
                                            {{
                                                data.user ? data.user.name : ""
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export CSV
                                    </button>
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
        <Modal v-model="modal.details" title="Sale Details">
            <POSDetailReport
                v-if="order_data_details && modal.details"
                :order_data_details="order_data_details"
                :total_order="total_sales"
                :total_tax="total_vat"
                :cash_pay="total_cash"
                :mpesa_pay="total_mpesa"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import POSDetailReport from "./dine_in_reports/POSDetailReport.vue";
import POSReportNav from "./POSReportNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ItemParticularsComponent from "../menu_components/dinerscomponents/ItemParticularsComponent.vue";
import SortButtons from '../../utilities/SortButtons.vue';
export default {
    data() {
        return {
            modal: {
                details: false,
            },
            package_data: null,

            room_data: null,
            params: {
                room_id: null,
                room_package_id: null,
                from: "",
                to: "",
             
            },
            sort_params:{
                   sort_qty: "",
                sort_total: "",
                sort_price: "",
                sort_cost_price:"",
                sort_discount:"",
            },
            order_data_details: null,
            sum_row_total: 0,
            sum_row_vat: 0,
            total_cost: 0,
            sum_service_charge_amount: 0,
            isLoading: true,
            search: "",

            sale_data: [],
            all_sale_data: [],
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        POSDetailReport,
        Treeselect,
        ItemParticularsComponent,
        SortButtons,
    },
    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        params: {
            deep: true,
            handler() {
                this.concurrentApiReq();
            },
        },
         sort_params: {
            deep: true,
            handler() {
                this.concurrentApiReq();
            },
        },
    },
    methods: {
      
  updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/detailsReport", {
                params: {
                    search: this.search,
                    ...this.params,
                    ...this.sort_params,
                    currentRoute: this.$route.name,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        stock: array_item.stock.product_name,
                        order_no: array_item.order_no,
                        batch_no: array_item.batch_no,
                        supplier: array_item.supplier ? array_item.supplier.company_name : '',
                        purchase_date: array_item.purchase_date,
                        order_date: array_item.order_date,
                        qty: array_item.qty,
                        cost_price: array_item.cost_price,
                        discount: array_item.discount,
                        price: array_item.price,
                        row_total: array_item.row_total,
                        row_vat: array_item.row_vat,

                        cashier: array_item.cashier
                            ? array_item.cashier.name
                            : "",
                        sales_person: array_item.user
                            ? array_item.user.name
                            : "",
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },

        async fetchTotals() {
            const res = await this.getApi(
                "data/room_sale/soldItemTotals",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,
                        ...this.params,
                        ...this.sort_params
                    },
                }
            );

            if (res.status == 200) {
                const data = res.data;
                if (data) {
                    this.sum_row_vat = data.sum_row_vat;
                    this.sum_row_total = data.sum_row_total;
                    this.total_cost = data.total_cost;
                    this.sum_service_charge_amount =
                        data.sum_service_charge_amount;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/detailsReport",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,
                        page,
                        ...this.params,
                        ...this.sort_params
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
