<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">POS Product Reports</div>

                    <div class="card-body">
                        <div class="row ">
                          
                            <div class="col-4">
                                <div class="form-group">
                                    <label for=""> Location</label>
                                    <treeselect
                                        :load-options="fetchLocation"
                                        :options="location_data"
                                        :auto-load-root-options="false"
                                        v-model="department_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Dept"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    v-model="from"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    v-model="to"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <button
                                    class="btn btn-secondary btn-sm"
                                    @click="filterDate()"
                                >
                                    Filter Date
                                </button>
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
                        <div class="total-display">
                            <span class="badge badge-secondary totals-badge"
                                >Total Sales:
                                {{ cashFormatter(sum_row_total) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Cost:
                                {{ cashFormatter(total_cost) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total VAT:
                                {{ cashFormatter(sum_row_vat) }}</span
                            >
                            <span class="badge badge-primary totals-badge"
                                >Sales Margin:
                                {{
                                    cashFormatter(sum_row_total - total_cost)
                                }}</span
                            >
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Order Date</th>
                                        <th scope="col">Stock</th>
                                        <th scope="col">Unit</th>
                                        <th>Price</th>
                                        <th scope="col">Amount</th>
                                        <th>Vat</th>
                                        <th>QTY</th>
                                        <th>Order No</th>
                                        <th>Order Type</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Waiter</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.order_date }}</td>
                                        <td>
                                            {{ data.stock.product_name }}
                                         <br>    <span
                                                        v-if="
                                                            data.accompaniment_id
                                                        "
                                                    >
                                                        Served With
                                                        {{
                                                            data.accompaniment
                                                                .product_name
                                                        }}</span
                                                    >
                                        </td>
                                        <td>
                                            {{ data.unit.name }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.price) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_vat) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.qty) }}
                                        </td>
                                        <td>
                                            {{ data.order_no }}
                                        </td>
                                        <td>{{data.order_type}}</td>
                                        <td>
                                            {{
                                                data.customer
                                                    ? data.customer.company_name
                                                    : ""
                                            }}
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
                                    class="btn btn-default btn-export ml-2 "
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
                                    class="btn btn-default btn-export ml-2 "
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
export default {
    data() {
        return {
            location_data: null,
            location_id: null,
            department_id:null,
            modal: {
                details: false
            },
            table_data: null,
            table_id: null,
            order_data_details: null,
            sum_row_total: 0,
            sum_row_vat: 0,
            total_cost: 0,
            isLoading: true,
            search: "",
            to: "",
            from: "",
            sale_data: [],
            all_sale_data: []
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        POSDetailReport,
        Treeselect
    },
    watch: {
        search: {
            handler: _.debounce(function() {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500)
        },
        department_id: {
            handler() {
                this.concurrentApiReq();
            }
        },
        table_id: {
            handler() {
                this.concurrentApiReq();
            }
        }
    },
    methods: {
        
        modifyTableSelect(data) {
            let modif = data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.department
                };
            });
            return modif;
        },
        async fetchLocation() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.location_data = this.modifyTableSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        viewDetails(data) {
            this.order_data_details = data;
            this.modal.details = true;
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/detailsReport", {
                params: {
                    search: this.search,
                    location_id: this.location_id,
                       department_id: this.department_id,
                    from: this.from,
                    to: this.to,
                    currentRoute: this.$route.name
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map(array_item => {
                    data_array.push({
                        order_no: array_item.order_no,
                        order_date: array_item.order_date,
                        qty: array_item.qty,
                        price: array_item.price,
                        row_total: array_item.row_total,
                        row_vat: array_item.row_vat,

                        order_type: array_item.order_type,
                        prev_order_no: array_item.prev_order_no,
                        stock: array_item.stock
                            ? array_item.stock.product_name
                            : "",
                        code: array_item.stock ? array_item.stock.code : "",
                        customer: array_item.customer
                            ? array_item.customer.company_name
                            : "",
                        cashier: array_item.cashier
                            ? array_item.cashier.name
                            : "",
                        sales_person: array_item.user
                            ? array_item.user.name
                            : "",
                        table: array_item.user ? array_item.table.name : "",
                        location: array_item.user
                            ? array_item.location.name
                            : ""
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchTotals() {
            const res = await this.getApi(
                "data/pos_sale/soldItemTotals",

                {
                    params: {
                        search: this.search,
                        location_id: this.location_id,
                          department_id: this.department_id,
                        table_id: this.table_id,
                        from: this.from,
                        to: this.to,
                        currentRoute: this.$route.name
                    }
                }
            );

            if (res.status == 200) {
                const data = res.data;
                if (data) {
                    this.sum_row_vat = data.sum_row_vat;
                    this.sum_row_total = data.sum_row_total;
                    this.total_cost = data.total_cost;
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
                        search: this.search,
                        page,
                        currentRoute: this.$route.name,
                        location_id: this.location_id,
                         department_id: this.department_id,
                        table_id: this.table_id,
                        from: this.from,
                        to: this.to
                    }
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
                this.fetchTotals()
            ]).then(function(results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        }
    },
    mounted() {
        this.concurrentApiReq();
    }
};
</script>
