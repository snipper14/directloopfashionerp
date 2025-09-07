<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <ReconNav />
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Accounts Reconciliation Report
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="">Select Bank Account</label>
                                <treeselect
                                    width="1200px"
                                    :load-options="fetchAccounts"
                                    :options="accounts_select_data"
                                    :auto-load-root-options="false"
                                    v-model="params.account_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select Account "
                                />
                            </div>
                        </div>
                        <div class="row">
                            <v-simple-table
                                style="width: 100%"
                                dense
                                class="elevation-2 rounded-table"
                            >
                                <template v-slot:default>
                                       <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Date</th>
                                                <th>Ref</th>
                                                <th>Opening Amount</th>
                                                <th>Closing Amount</th>
                                                <th>User</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    value, i
                                                ) in statement_report.data"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ value.account.account }}
                                                </td>
                                                <td>
                                                    {{ value.transaction_date }}
                                                </td>
                                                <td>{{ value.ref_no }}</td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            value.opening_balance
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            value.closing_balance
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ value.user.name }}</td>
                                                <td>
                                                    <v-btn
                                                        color="primary"
                                                        @click="
                                                            viewDetails(value)
                                                        "
                                                        x-small
                                                        >view details</v-btn
                                                    >
                                                </td>
                                            </tr>
                                        </tbody>
                                 
                                </template>
                            </v-simple-table>
                            <pagination
                                v-if="statement_report !== null"
                                v-bind:results="statement_report"
                                v-on:get-page="fetchReconByRef"
                            ></pagination>
                            Items Count {{ statement_report.total }}
                            <br />

                            <div class="center_button d-flex flex-row">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <Button type="primary"
                                        >Export Excel
                                    </Button></export-excel
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="detail_modal" width="1200px">
            <QBReconsDetailsReport
                v-if="detail_modal"
                :details_data="details_data"
            />
        </Modal>
    </div>
</template>

<script>
import ReconNav from "./ReconNav.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Pagination from "../../utilities/Pagination.vue";
import QBReconsDetailsReport from "./QBReconsDetailsReport.vue";
export default {
    components: { Treeselect, ReconNav, Pagination, QBReconsDetailsReport },
    data() {
        return {
            statement_report: [],
            isLoading: true,
            details_data: null,
            detail_modal: false,
            params: {
                account_id: null,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.fetchReconByRef(1);
            }, 500),
        },
    },
    methods: {
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchReconByRef",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((value) => {
                    data_array.push({
                        account: value.account.account,
                        entry_date: value.entry_date,
                        ref: value.ref,
                        opening_balance: value.opening_balance,
                        closing_balance: value.closing_balance,
                        sum_cr_amount: value.sum_cr_amount,
                        sum_dr_amount: value.sum_dr_amount,
                        user: value.user.name,
                    });
                });
                return data_array;
            }
        },
        async viewDetails(value) {
            this.details_data = value;
            this.detail_modal = true;
        },
        async fetchReconByRef(page) {
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchReconByRef",
                {
                    params: {
                        ...this.params,
                        page,
                    },
                }
            );

            if (res.status === 200) {
                this.statement_report = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([this.fetchReconByRef(1)]);

            this.isLoading ? this.hideLoader() : "";
        },
    },
};
</script>

<style scoped>
.custom-table {
    color: #333;
    background-color: #f8f9fa;
    border-collapse: separate;
    border-spacing: 0;
}

.custom-table thead {
    background-color: #1434a4;
    color: white;
}

.custom-table th,
.custom-table td {
    padding: 15px;
    text-align: left;
    border: 1px solid #dee2e6;
}

.custom-table th:first-child,
.custom-table td:first-child {
    width: 40px;
    text-align: center;
}

.custom-table tbody tr:nth-child(even) {
    background-color: #e9ecef;
}

.custom-table input[type="checkbox"] {
    accent-color: #007bff;
    cursor: pointer;
    transform: scale(1.2);
}

/* Hover effect */
.custom-table tbody tr:hover {
    background-color: #d6d8db;
}
</style>
