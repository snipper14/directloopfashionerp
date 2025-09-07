<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-11">
                    <div class="card">
                        <div class="card-header-wrapper">
                            <h4>Supplier Invoices</h4>
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
                            <div
                                class="d-flex justify-content around mt-3 primary-color"
                            >
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
                            </div>
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Supplier Name</th>
                                        <th scope="col">Credit Limit</th>
                                        <th scope="col">
                                            Total Invoice Amount
                                        </th>
                                        <th scope="col">Total Paid</th>
                                        <th scope="col">Total Unpaid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in supplier_invoice_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.supplier.company_name }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.supplier.credit_limit) }}
                                        </td>
                                        <td
                                            :class="{
                                                red_line:
                                                    data.sum_invoice_total >=
                                                    data.supplier.credit_limit,
                                            }"
                                        >
                                            {{
                                                cashFormatter(
                                                    data.sum_invoice_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_paid_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_unpaid_amount
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination
                                v-if="supplier_invoice_data !== null"
                                v-bind:results="supplier_invoice_data"
                                v-on:get-page="fetchGroupedInvoices"
                            ></Pagination>
                            Items Count {{ supplier_invoice_data.total }}

                            <div class="row">
                                <div class="col-4 d-flex">
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="exportExcel"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                        <v-btn color="info" small>
                                            <v-icon medium>{{
                                                icons.mdiMicrosoftExcel
                                            }}</v-icon>
                                            Export Excel</v-btn
                                        >
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="exportExcel"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="filename.xls"
                                        ><v-btn color="secondary" small>
                                            <v-icon class="v-icon" medium>{{
                                                icons.mdiFileExcel
                                            }}</v-icon>
                                            Export CSV
                                        </v-btn>
                                    </export-excel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div></v-app
        >

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                new_invoice: false,
                view_invoice: false,
            },
            supplier_total: {},
            isLoading: true,
            show_payment_modal: false,
            invoice_details: null,
            supplier_invoice_data: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],

            params: { search: "", to: null, from: null },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiCash,
            },
        };
    },
    components: {
        Pagination,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetchGroupedInvoices(1)]);
            this.isLoading ? this.hideLoader() : "";
        },

        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_invoice/fetchGroupedInvoices",
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
                    company_name: array_item.supplier.company_name,

                    sum_invoice_total: array_item.sum_invoice_total,
                    sum_paid_amount: array_item.sum_paid_amount,
                    sum_unpaid_amount: array_item.sum_unpaid_amount,
                });
            });
            return data_array;
        },

        async fetchGroupedInvoices(page) {
            const res = await this.getApi(
                "data/supplier_invoice/fetchGroupedInvoices",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.supplier_invoice_data = res.data;
            } else {
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
