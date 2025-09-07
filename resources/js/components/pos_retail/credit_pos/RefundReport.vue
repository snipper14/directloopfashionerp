<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><CreditNav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3><b style="color: red">RETURNS REFUND REPORT</b></h3>
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
                            <div class="col-md-4">
                                <input
                                    type="text"
                                    placeholder="search"
                                    v-model="search"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-4">
                                <h3>
                                    <b>
                                        Total Refund
                                        {{ cashFormatter(total_refund) }}</b
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
                                        <th scope="col">
                                            Timestamp<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_created_at
                                                "
                                                sort-key="sort_created_at"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Credit Date</th>
                                        <th>Receipt No</th>
                                        <th>Credit No</th>
                                        <th>Refund Account <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_account_name
                                                "
                                                sort-key="sort_account_name"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /> </th>
                                        <th scope="col">Refund Amount <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_refund_amount
                                                "
                                                sort-key="sort_refund_amount"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>

                                        <th>User<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_user
                                                "
                                                sort-key="sort_user"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th>Ref</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in refund_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>

                                        <td>{{ data.credit_date }}</td>
                                        <td>{{ data.receipt_no }}</td>
                                        <td>{{ data.credit_no }}</td>
                                        <td>{{ data.account.account }}</td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.refund_amount
                                                    )
                                                }}</b
                                            >
                                        </td>

                                        <td>{{ data.user.name }}</td>
                                        <td>{{ data.ref_details }}</td>
                                        <td>{{ data.comments }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="refund_data !== null"
                            v-bind:results="refund_data"
                            v-on:get-page="fetch"
                        ></Pagination>
                        Items Count {{ refund_data.total }}
                        <div class="row">
                            <div class="col-md-8 d-flex">
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
                sort_created_at: "",
                sort_account_name:"",
                sort_user:"",
                sort_refund_amount:""
            },
            total_refund: 0,

            search: "",
            refund_data: [],
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
                "data/pos_credit/fetchRefundsTotal",

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
                    this.total_refund = data.total_amount;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async apiCall(page) {
            const res = await this.getApi("data/pos_credit/fetchRefunds", {
                params: {
                    page,
                    ...this.search_params,
                    search: this.search,
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                    ...this.sort_params
                },
            });
            return res;
        },
        async fetch(page) {
            const res = await this.apiCall(page);

            if (res.status === 200) {
                this.refund_data = res.data;
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
                        account: data.account.account,
                        refund_amount: data.refund_amount,

                        user: data.user.name,
                        ref_details: data.ref_details,
                        comments: data.comments,
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
