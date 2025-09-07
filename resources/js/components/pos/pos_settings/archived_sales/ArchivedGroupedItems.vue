<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><pos-setting-nav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Archived Sales Report</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3">
                            <div class="d-flex flex-column">
                                <label>Archived From *</label>

                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column">
                                <label>Archived To *</label>
                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="to"
                                ></date-picker>
                            </div>
                            <div>
                                <button
                                    class="btn btn-primary btn-sm mt-4"
                                    @click="filterDate()"
                                >
                                    Filter
                                </button>
                                <button
                                    class="btn btn-info btn-sm mt-4"
                                    @click="fetchAllData()"
                                >
                                    Fetch All
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search As Waiter</label>
                                    <treeselect
                                        :load-options="fetchWaiter"
                                        :options="waiter_select_data"
                                        :auto-load-root-options="false"
                                        v-model="search_params.waiter_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Waiter"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search As Cashier</label>
                                    <treeselect
                                        :load-options="fetchWaiter"
                                        :options="waiter_select_data"
                                        :auto-load-root-options="false"
                                        v-model="search_params.cashier_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Cashier"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search..</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>

                      
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                     
                                        <th>Item</th>
                                        <th>Qty Sold</th>
                                        <th scope="col">Amount</th>
                                        <th>Vat</th>

                                      
                                      
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                     
                                        <td>{{ data.stock.product_name }}</td>
                                         <td>{{ data.sum_qty }}</td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.sum_total
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sum_vat) }}
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
                                class="col-8 d-flex"
                               
                            >
                                <export-excel
                                    
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
            </div>
        </div>
        <Modal v-model="details_modal" title="Archived Sale Details">
            <archived-details
                v-if="order_data && details_modal"
                :order_data="order_data"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import datetime from "vuejs-datetimepicker";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import PosSettingNav from "../PosSettingNav.vue";
import ArchivedDetails from "./ArchivedDetails.vue";
export default {
    components: {
        datetime,
        Treeselect,
        Unauthorized,
        Pagination,
        PosSettingNav,
        ArchivedDetails,
    },
    data() {
        return {
            search_params: {
                waiter_id: null,
                cashier_id: null,
            },
            details_modal: false,
            order_data: null,
            search: "",
            sale_data: [],
            waiter_select_data: null,
            from: null,
            to: null,
            total_vat: 0,
            total_sales: 0,

            isLoading: true,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async details(data) {
            this.showLoader();
            const res = await this.getApi("data/archive_order/details", {
                params: {
                    order_no: data.order_no,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.order_data = res.data;
                this.details_modal = true;
            } else {
                this.form_error(res);
            }
        },

        async fetchWaiter() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.waiter_select_data = this.modifyWaiterSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyWaiterSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchAllData() {
            this.isLoading = true;
            this.from = null;
            this.to = null;
            this.concurrentApiReq();
        },
        async printDetailedData() {
            setTimeout(() => {
                this.$refs.AllSaleReportPrintComponent.printBill();
            }, 1000);
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/archive_order/fetchGroupedItems",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
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
                //this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchTotals() {
            const res = await this.getApi(
                "data/archive_order/archivedTotal",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                const data = res.data;
                if (data) {
                    this.total_vat = data.sum_vat;
                    this.total_sales = data.sum_total;
                }
            } else {
                this.swr("Server error occurred");
            }
        },

        async printTotals() {
            setTimeout(() => {
                this.$refs.AllSaleTotalPrintComponents.printBill();
            }, 1000);
        },

        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/archive_order/fetchGroupedItems", {
                params: {
                    ...this.search_params,
                    search: this.search,
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                },
            });
            this.hideLoader();
            return res;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        sum_qty:array_item.sum_qty,
                        total_vat: array_item.sum_vat,

                        receipt_total: array_item.sum_total,
                        customer: array_item.customer
                            ? array_item.customer.company_name
                            : "",
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
    },
};
</script>
