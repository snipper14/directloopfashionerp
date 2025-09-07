<template>
    <div>
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <ReceivableNav />
            </div>

            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header-wrapper">
                        <h4>Orders Management</h4>
                        <form class="form-inline">
                            <input
                                class="form-control mr-sm-2"
                                type="search"
                                v-model="search"
                                placeholder="Search Customer,Order,Date"
                                aria-label="Search"
                            />
                        </form>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Order No</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Items Qty</th>
                                    <th scope="col">Total Amount</th>

                                    <th scope="col">Generate Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in orders_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.customer.company_name }}
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="updateOrder(data)"
                                            >{{ data.order_no }}
                                        </router-link>
                                    </td>
                                    <td>{{ data.order_date }}</td>
                                    <td>{{ data.sum_qty }}</td>
                                    <td>
                                        {{ cashFormatter(data.order_total) }}
                                    </td>
   <td>
                                        <router-link
                                            title="write"
                                            v-if="isWritePermitted"
                                            to="#"
                                            @click.native="recallOrder(data)"
                                        >
                                            Recall Order
                                        </router-link>
                                    </td>
                                    
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="orders_data !== null"
                            v-bind:results="orders_data"
                            v-on:get-page="fetchOrders"
                        ></Pagination>
                        Items Count {{ orders_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
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
                                    class="btn btn-default btn-export ml-2 "
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
            <div v-else>
                <unauthorized />
            </div>
        </div>
           <add-order
            v-if="active.add_order"
            :data="customer_data"
            v-on:dashboard-active="setActiveRefresh"
        />
        <update-order
            :data="edit_order_data"
            v-if="active.edit_order"
            v-on:dashboard-active="setActiveRefresh"
        />
        <new-invoice
            :data="invoice_data"
            v-if="active.new_invoice"
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
    mdiFilePdfBoxOutline
} from "@mdi/js";
import ReceivableNav from "./ReceivableNav.vue";
import UpdateOrder from "./UpdateOrder.vue";
import Pagination from "../../utilities/Pagination.vue";
import NewInvoice from "./invoices/NewInvoice.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import AddOrder from "./orders/AddOrder.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                edit_order: false,
                new_invoice: false,
                  add_order: false,
            },
              customer_data: null,
            edit_order_data: null,
            orders_data: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],
            search: "",
            params: {
                name: "",
                description: ""
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    created() {},
    components: {
        ReceivableNav,
        Pagination,
        UpdateOrder,
        NewInvoice,
        Unauthorized,
        AddOrder
    },
    mounted() {
        this.fetchOrders(1);
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        }
    },
    methods: {
        generateInvoice(data) {
            this.invoice_data = data;

            this.setActiveComponent("new_invoice");
        },
        async updateOrder(data) {
            this.edit_order_data = data;

            this.setActiveComponent("edit_order");
        },

        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllSales();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllSales() {
            const res = await this.getApi("data/sales/fetchAll", "");
            var data_array = [];
            res.data.results.map(array_item => {
                data_array.push({
                    company_name: array_item.customer.company_name,
                    order_date: array_item.order_date,
                    order_no: array_item.order_no,
                    qty: array_item.sum_qty,
                    order_total: array_item.order_total
                });
            });
            return data_array;
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/sales/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.orders_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchOrders();
        },

        async fetchOrders(page) {
            this.showLoader();
            const res = await this.getApi("data/sales/fetch", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.orders_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
         async recallOrder(data) {
            const res = await this.callApi(
                "post",
                "data/orders/recallOrder",
                data
            );
            if (res.status === 200) {
                this.s("recalled successfully");
                this.customer_data = res.data[0];
                this.setActiveComponent("add_order");
            } else {
                this.form_error(res);
            }
        },
    }
};
</script>
