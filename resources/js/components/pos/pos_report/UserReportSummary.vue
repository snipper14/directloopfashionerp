<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">
                        {{ report_title.toUpperCase() }}
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    v-model="from"
                                    type="datetime"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    v-model="to"
                                    type="datetime"
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
                            <div>
                                <button
                                    class="btn btn-primary btn-sm"
                                    v-if="isDownloadPermitted"
                                    @click="printRecords"
                                >
                                    Print Records
                                </button>
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="">Branch Filter</label>
                                <treeselect
                                    :load-options="fetchBranch"
                                    :options="branch_select_data"
                                    :auto-load-root-options="false"
                                    v-model="params.branch_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select   Branch "
                                />
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="">User Activity</label>
                                <select
                                    v-model="params.user_activity"
                                    class="form-control"
                                >
                                    <option value="sales">Sales</option>
                                    <option value="cashier">Cashier</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <th scope="col">Name</th>

                                    <th scope="col">
                                        Total Sale<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_sum_receipt_total
                                            "
                                            sort-key="sort_sum_receipt_total"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>
                                </thead>

                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in user_report_data"
                                        :key="i"
                                    >
                                        <td>
                                            <span>
                                                <b
                                                    v-if="
                                                        params.user_activity ==
                                                        'sales'
                                                    "
                                                >
                                                    {{ data.user.name }}</b
                                                >
                                                <b v-else>
                                                    {{ data.cashier.name }}</b
                                                ></span
                                            >
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_receipt_total
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <UserSummaryPrintComponent
            ref="UserSummaryPrintComponent"
            v-if="user_report_data.length > 0"
            :user_report_data="user_report_data"
            :all_user_totals="all_user_totals"
            :report_title="report_title"
            :user_id="user_id"
            :from="from"
            :to="to"
            :params="params"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import POSReportNav from "./POSReportNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import UserSummaryPrintComponent from "./prints_components/UserSummaryPrintComponent.vue";
import SortButtons from "../../utilities/SortButtons.vue";

export default {
    data() {
        return {
            print_title: "ROOM SERVICE SUMMARY",
            isLoading: true,
            all_user_totals: 0,
            branch_select_data: null,
            report_title: "",
            user_option: [
                {
                    label: "Cashier",
                    value: "cashier",
                },
                {
                    label: "user",
                    value: "user",
                },
            ],
            user_id: "user",
            search: "",

            to: "",
            from: "",
            user_report_data: [],
            user_id: 9,
            params: {
                user_activity: "sales",
                branch_id: null,
            },
            cashier_id: null,
            sort_params: {
                sort_sum_receipt_total: "",
            },
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        UserSummaryPrintComponent,
        Treeselect,
        SortButtons,
    },
    watch: {
        user_id: {
            handler(val) {
                if (val == "cashier") {
                    this.cashier_id = 4;
                    this.user_id = null;
                    this.concurrentApiReq();
                }
                if (val == "waiter") {
                    this.cashier_id = null;
                    this.user_id = 5;

                    this.concurrentApiReq();
                }
            },
        },
        params: {
            deep: true,
            handler(val) {
                this.concurrentApiReq();
            },
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

        printRecords() {
            setTimeout(() => {
                this.$refs.UserSummaryPrintComponent.printBill();
            }, 1000);
        },
        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchBranch() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/branch/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.branch_select_data = this.modifyBranchSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyBranchSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.branch,
                };
            });
            return modif;
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchGroupedUser",

                {
                    params: {
                        search: this.search,
                        user_id: this.user_id,
                        cashier_id: this.cashier_id,
                        ...this.params,
                        ...this.sort_params,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.user_report_data = res.data;
                this.all_user_totals = this.user_report_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.total;
                },
                0);
                this.report_title = this.user_id + " Summary Report";
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchData(1)]).then(function (
                results
            ) {
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
