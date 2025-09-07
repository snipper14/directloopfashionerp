<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <executive-nav />
            </div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header-wrapper">
                        <h4><b>Cummulative Product Sales</b></h4>
                        <form class="form-inline">
                            <input
                                class="form-control mr-sm-2"
                                type="search"
                                v-model="params.search"
                                placeholder="Search Customer,Order,Date"
                                aria-label="Search"
                            />
                        </form>
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
                            <div class="d-flex flex-column mr-2">
                                <label> To Datetime *</label>
                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.to"
                                ></date-picker>
                            </div>
                            <div class="form-group col-md-2 nopadding">
                                <label for=""> Select Location</label>
                                <treeselect
                                    width="300"
                                    :load-options="fetchLocation"
                                    :options="department_data"
                                    :auto-load-root-options="false"
                                    v-model="params.department_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select Location "
                                />
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th>Product Category</th>
                                    <th>Product Name</th>
                                    <th scope="col">Total Purchase Price</th>
                                    <th scope="col">Qty Sold</th>
                                    <th scope="col">Total Sale</th>
                                    <th scope="col">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(
                                        data, i
                                    ) in product_sales_list_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock.product_category.name }}
                                    </td>
                                    <td>{{ data.stock.name }}</td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.sum_purchase_price
                                            )
                                        }}
                                    </td>
                                    <td>{{ data.sum_qty }}</td>
                                    <td>
                                        {{ cashFormatter(data.row_total) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.row_total -
                                                    data.sum_purchase_price
                                            )
                                        }}
                                    </td>

                                    <td>
                                        {{ data.user ? data.user.name : "" }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <pagination
                            v-if="product_sales_list_data !== null"
                            v-bind:results="product_sales_list_data"
                            v-on:get-page="fetchProductList"
                        ></pagination>
                        Items Count {{ product_sales_list_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllSales"
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
                                    :fetch="getAllSales"
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
            </div>
        </div>
        <view-invoice
            :data="invoice_data"
            v-if="active.view_invoice"
            v-on:dashboard-active="setActiveRefresh"
        />

        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiEyeCircleOutline,
} from "@mdi/js";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ExecutiveNav from "./ExecutiveNav.vue";
import ViewInvoice from "../accounts_receivable/invoices/ViewInvoice.vue";
import Pagination from "../../utilities/Pagination.vue";
export default {
    data() {
        return {
            invoice_payment_modal: false,
            active: {
                dashboard: true,
                delivery_dash: false,
                view_invoice: false,
                view_delivery_notes: false,
            },
            new_data: null,
            product_sales_list_data: [],
            department_data: null,
            invoice_data: null,
            pdf_data: [],
            invoice_total: {},
            isLoading: true,

            params: {
                department_id: null,
                search: "",
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiEyeCircleOutline,
            },
        };
    },
    components: {
        ExecutiveNav,
        ViewInvoice,
        Pagination,
        Treeselect,
    },
    mounted() {
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
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchProductList(1)]).then(
                function (results) {
                    return results;
                }
            );

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchLocation() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/dept/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.department_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.department,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },

        async getAllSales() {
            this.showLoader();
            const res = await this.getApi(
                "data/invoices/fetchexecutiveCummulativeProductSalesReport",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    product_category: data.stock.product_category.name,
                    stock: data.stock.name,
                    sum_purchase_price: data.sum_purchase_price,
                    sum_qty: data.sum_qty,
                    invoice_date: data.invoice_date,

                    stock: data.stock.name,
                    sum_purchase_price: data.sum_purchase_price,
                    qty: data.qty,
                    row_total: data.row_total,
                    margin: data.row_total - data.sum_purchase_price,

                    user: data.user.name,
                });
            });
            return data_array;
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        dismissModal() {
            this.invoice_payment_modal = false;
        },
        async fetchProductList(page) {
            const res = await this.getApi(
                "data/invoices/fetchexecutiveCummulativeProductSalesReport",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.product_sales_list_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
