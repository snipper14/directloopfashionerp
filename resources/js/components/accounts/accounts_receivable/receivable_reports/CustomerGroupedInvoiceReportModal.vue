<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card">
                    <div class="card-header-wrapper">
                        <h4><b>Customer Invoices Reports</b></h4>
                        <form class="form-inline">
                            <input
                                class="form-control mr-sm-2"
                                type="search"
                                v-model="params.search"
                                placeholder="Search Customer,Order,Date"
                                aria-label="Search"
                            />
                        </form>
                        <span class="red_line">Surpassed credit limit</span>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Customer Name</th>
                                    <th>Total Paid</th>
                                    <th>Total Unpaid</th>
                                    <th scope="col">Credit Limit</th>
                                    <th scope="col">Total Invoices Amount</th>
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
                                        {{
                                            cashFormatter(data.sum_amount_paid)
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.sum_unpaid_amount
                                            )
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            cashFormatter(
                                                data.customer.credit_limit
                                            )
                                        }}
                                    </td>

                                    <td
                                        :class="{
                                            red_line:
                                                data.invoice_total >=
                                                data.customer.credit_limit,
                                        }"
                                    >
                                        <b>{{
                                            cashFormatter(data.invoice_total)
                                        }}</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="orders_data !== null"
                            v-bind:results="orders_data"
                            v-on:get-page="fetchInvoices"
                        ></Pagination>
                        Items Count {{ orders_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportInvoice"
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
                                    :fetch="exportInvoice"
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

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                delivery_dash: false,
                view_invoice: false,
                view_delivery_notes: false,
            },
            new_data: null,
            orders_data: [],
            invoice_data: null,
            pdf_data: [],

            params: { search: "" },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,

        Unauthorized,
    },
    mounted() {
        this.showLoader();
        this.fetchInvoices(1);
    },
    watch: {
        params: {
            handler: _.debounce(function (val, old) {
                this.fetchInvoices(1);
            }, 500),

            deep: true,
        },
    },
    methods: {
        async exportInvoice() {
            this.showLoader();
            const res = await this.getApi(
                "data/invoices/fetchCustomerGroupedInvoiceTotals",
                {
                    params: {
                        

                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            var data_array = [];
            res.data.map((array_item) => {
                data_array.push({
                    company_name: array_item.customer.company_name,
                    total_paid: array_item.sum_amount_paid,
                    total_unpaid: array_item.sum_unpaid_amount,
                    total_vat: array_item.sum_tax_amount,
                    credit_limit:array_item.customer.credit_limit,
                    total_amount: array_item.invoice_total,
                });
            });
            return data_array;
        },

        async fetchInvoices(page) {
            const res = await this.getApi(
                "data/invoices/fetchCustomerGroupedInvoiceTotals",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );
            this.hideLoader();

            if (res.status === 200) {
                this.orders_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped>
.red_line {
    background: rgb(246, 160, 22);
    color: #fff;
}
</style>
