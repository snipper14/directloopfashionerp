<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><CreditNav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">
                        <h3><b style="color: red">CREDIT NOTES</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3">
                            <div class="d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column">
                                <label> To Datetime *</label>
                                <date-picker
                                    valueType="format"
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
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Filter User</label>
                                    <treeselect
                                        :load-options="fetchWaiter"
                                        :options="waiter_select_data"
                                        :auto-load-root-options="false"
                                        v-model="search_params.user_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select User"
                                    />
                                </div>
                            </div>

                            <div class="col-md-3">
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
                        <div class="row">
                            <div class="col-md-4">
                                <h3>
                                    <b>
                                        Total Credit Vat
                                        {{ cashFormatter(total_cr_vat) }}</b
                                    >
                                </h3>
                            </div>
                            <div class="col-md-4">
                                <h3>
                                    <b>
                                        Total Credit Amount
                                        {{ cashFormatter(total_cr) }}</b
                                    >
                                </h3>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Timestamp<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_created_at
                                                "
                                                sort-key="sort_created_at"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th scope="col">Credit Date</th>
                                        <th>
                                            Receipt No
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_receipt_no
                                                "
                                                sort-key="sort_receipt_no"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Credit No</th>
                                        <th scope="col">
                                            Cr.Amount
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_amount
                                                "
                                                sort-key="sort_total_amount"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Cr.Vat<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_vat
                                                "
                                                sort-key="sort_total_vat"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th>User<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_user_name
                                                "
                                                sort-key="sort_user_name"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>

                                        <th>Etims Customer</th>

                                        <th>Etims Customer PIN</th>
                                        <th>Etims Url</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in credit_note_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <v-btn
                                                x-small
                                                color="primary"
                                                @click="viewDetails(data)"
                                                >View Details</v-btn
                                            >
                                        </td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.credit_date }}</td>
                                        <td>{{ data.receipt_no }}</td>
                                        <td>{{ data.credit_no }}</td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.total_amount
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.total_vat) }}
                                        </td>

                                        <td>{{ data.user.name }}</td>
                                        <td>{{ data.etims_customer_name }}</td>
                                        <td>{{ data.etims_customer_pin }}</td>
                                        <td>
                                            <a
                                                :href="data.sale_detail_url"
                                                target="_blank"
                                                >Etims Link</a
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="credit_note_data !== null"
                            v-bind:results="credit_note_data"
                            v-on:get-page="fetch"
                        ></Pagination>
                        Items Count {{ credit_note_data.total }}
                        <div class="row">
                            <div
                                class="col-md-8 d-flex"
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
        <Modal v-model="credit_note_report_details_modal" width="1200px">
            <POSCreditReportDetails
                v-if="credit_note_report_details_modal"
                :credit_details="credit_details"
            />
        </Modal>

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import CreditNav from "./CreditNav.vue";
import datetime from "vuejs-datetimepicker";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import POSCreditReportDetails from "./POSCreditReportDetails.vue";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    components: {
        CreditNav,
        Unauthorized,
        Pagination,
        datetime,
        Treeselect,
        POSCreditReportDetails,
        SortButtons,
    },
    data() {
        return {
            search_params: {
                user_id: null,
                cashier_id: null,
                order_type: null,
            },
            sort_params: {
                sort_receipt_no: "",
                sort_total_amount: "",
                sort_total_vat:"",
                sort_user_name:"",
                sort_created_at:""
            },

            total_cr_vat: 0,
            total_cr: 0,
            total_refundable: 0,
            search: "",
            credit_note_data: [],
            waiter_select_data: null,
            from: null,
            to: null,
            credit_details: null,
            isLoading: true,
            credit_note_report_details_modal: false,
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
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async viewDetails(data) {
            this.credit_details = data;
            this.credit_note_report_details_modal = true;
        },
        async fetchWaiter() {
            const res = await this.getApi("data/users/fetchAll", {});

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

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetch(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchTotals() {
            const res = await this.getApi(
                "data/pos_credit/fetchTotal",

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
                    this.total_cr_vat = data.total_vat;
                    this.total_cr = data.total_amount;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async apiCall(page) {
            const res = await this.getApi("data/pos_credit/fetch", {
                params: {
                    page,
                    ...this.search_params,
                    search: this.search,
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                    ...this.sort_params,
                },
            });
            return res;
        },
        async fetch(page) {
            const res = await this.apiCall(page);

            if (res.status === 200) {
                this.credit_note_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchExcel() {
            const res = await this.apiCall();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        timestamp: this.formatDateTime(data.created_at),
                        credit_date: data.credit_date,
                        receipt_no: data.receipt_no,
                        credit_no: data.credit_no,
                        total_amount: data.total_amount,
                        total_vat: data.total_vat,
                        user: data.user.name,
                        etims_timestamp: data.etims_timestamp,
                        etims_date: data.etims_date,
                        etims_time: data.etims_time,
                        trader_invoice_number: data.trader_invoice_number,
                        digitax_id: data.digitax_id,
                        serial_number: data.serial_number,
                        receipt_number: data.receipt_number,
                        sale_detail_url: data.sale_detail_url,
                        etims_customer_name: data.etims_customer_name,
                        etims_customer_pin: data.etims_customer_pin,
                        taxable_amount_a: data.taxable_amount_a,
                        taxable_amount_b: data.taxable_amount_b,
                        taxable_amount_c: data.taxable_amount_c,
                        taxable_amount_d: data.taxable_amount_d,
                        taxable_amount_e: data.taxable_amount_e,
                        tax_rate_a: data.tax_rate_a,
                        tax_rate_b: data.tax_rate_b,
                        tax_rate_c: data.tax_rate_c,
                        tax_rate_d: data.tax_rate_d,
                        tax_rate_e: data.tax_rate_e,
                        tax_amount_a: data.tax_amount_a,
                        tax_amount_b: data.tax_amount_b,
                        tax_amount_c: data.tax_amount_c,
                        tax_amount_d: data.tax_amount_d,
                        tax_amount_e: data.tax_amount_e,
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
