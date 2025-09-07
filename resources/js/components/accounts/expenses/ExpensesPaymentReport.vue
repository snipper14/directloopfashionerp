<template>
    <div class="container">
        <div class="row justify-content-center" v-if="isReadPermitted">
            <div class="col-md-3">
                <ExpensesNav />
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Expenses Payment</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex justify-content around mt-3">
                                <div class="d-flex flex-column">
                                    <label> From *</label>

                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.from"
                                    ></date-picker>
                                </div>
                                <div class="d-flex flex-column">
                                    <label> To *</label>
                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.to"
                                    ></date-picker>
                                </div>
                                <div>
                                    <button
                                        class="btn btn-primary btn-sm mt-6"
                                        @click="fetchPaymentReport(1)"
                                    >
                                        Filter
                                    </button>
                                </div>
                                <div class="form-group">
                                    <input
                                        type="text"
                                        class="form-control mt-6"
                                        placeholder="Search"
                                        v-model="params.search"
                                    />
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
                            </div>
                        </div>
                        <div class="row">
                            <span class="badge badge-secondary totals-badge"
                                >Total Paid :
                                {{
                                    cashFormatter(
                                        expenses_total.total_amount_paid
                                    )
                                }}
                            </span>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Account</th>
                                            <th scope="col">details</th>
                                            <th>Expense Type</th>
                                            <th>Expense Details</th>
                                            <th>Pay Method</th>
                                            <th scope="col">Payment ref</th>
                                            <th scope="col">Expenses Amount</th>
                                            <th scope="col">paid Amount</th>
                                            <th>User</th>
                                            <th>Branch</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in expenses.data"
                                            :key="i"
                                        >
                                            <td scope="row">
                                                {{ value.date_paid }}
                                            </td>
                                            <td>
                                                {{ value.account.account }}
                                            </td>
                                            <td>{{ value.payment_details }}</td>
                                            <td>
                                                {{
                                                    value.expense &&
                                                    value.expense.expense_type
                                                        ? value.expense
                                                              .expense_type.name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    value.expense &&
                                                    value.expense.details
                                                        ? value.expense.details
                                                        : ""
                                                }}
                                            </td>

                                            <td>{{ value.pay_method }}</td>
                                            <td>{{ value.ref_no }}</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.expense_amount
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.amount_paid
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{ value.user.name }}
                                            </td>
                                            <td>{{value.branch.branch}}</td>

                                            <td>
                                                <v-btn
                                                    color="danger"
                                                    x-small
                                                    v-if="isDeletePermitted"
                                                    title="delete"
                                                    @click="
                                                        deleteRecord(
                                                            value.id,
                                                            i
                                                        )
                                                    "
                                                >
                                                    Delete
                                                </v-btn>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <pagination
                                    v-if="expenses !== null"
                                    v-bind:results="expenses"
                                    v-on:get-page="fetchPaymentReport"
                                ></pagination>
                                Items Count {{ expenses.total }}
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
                                            <v-btn color="primary" dark small>
                                                Export Excel
                                            </v-btn>
                                        </export-excel>

                                        <export-excel
                                            v-if="isDownloadPermitted"
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchExcel"
                                            worksheet="My Worksheet"
                                            type="csv"
                                            name="filename.xls"
                                        >
                                            <v-btn color="secondary" dark small>
                                                Export CSV
                                            </v-btn>
                                        </export-excel>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else><unauthorized /></div>
        <Modal
            v-model="add_expenses_modal"
            title="Add Expense Category"
            width="60px"
        >
            <add-expense-type v-if="add_expenses_modal" />
        </Modal>
        <Modal v-model="payment_modal" title="Add Payment" width="1200px">
            <pay-expenses
                v-if="payment_modal"
                v-bind:line_data="line_data"
                v-on:dashboard-active="dismissDiag"
            />
        </Modal>
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";

import ExpensesNav from "./ExpensesNav.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Pagination from "../../utilities/Pagination.vue";

export default {
    components: { Unauthorized, Treeselect, Pagination, ExpensesNav },
    data() {
        return {
            expenses: [],
            line_data: null,
            payment_modal: false,
            add_expenses_modal: false,
            btn_loading: true,
            expenses_total: {},
            params: {
                to: null,
                from: null,
                search: "",
                branch_id:null
            },
        };
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.concurrentApiReq();
            }, 500),
        },
    },

    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchPaymentReport(1),
                this.fetchPaymentTotal(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/expense_payment/fetchPaymentReport",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader();

            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                data.map((value) => {
                    data_array.push({
                        date_paid: value.date_paid,
                        account: value.account.account,
                        payment_details: value.payment_details,
                        expense_type: value.expense.expense_type.name,

                        expense_detail: value.expense.details,
                        pay_method: value.pay_method,
                        ref_no: value.ref_no,
                        expense_amount: value.expense_amount,
                        amount_paid: value.amount_paid,
                        name: value.user.name,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "DELETE",
                    "data/expense_payment/destroy/" + id,
                    {}
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.s("deleted");
                    this.expenses.data.splice(i, 1);
                } else {
                    this.form_error(res);
                }
            }
        },
        async fetchPaymentTotal() {
            const res = await this.getApi(
                "data/expense_payment/fetchPaymentTotal",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.expenses_total = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        async fetchPaymentReport(page) {
            const res = await this.getApi(
                "data/expense_payment/fetchPaymentReport",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.expenses = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
