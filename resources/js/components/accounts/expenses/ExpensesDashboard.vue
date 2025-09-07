<template>
    <div class="">
        <div class="row " v-if="isReadPermitted">
            <div class="col-md-2">
                <ExpensesNav />
            </div>
            <div class="col-md-10">
                <v-btn @click="add_expenses_modal = true" dark color="primary"
                    >+ Add Expense Category</v-btn
                >
                <v-btn color="primary" @click="new_expenses_modal = true"
                    >Record Expenses</v-btn
                >
                <div class="card">
                    <div class="card-header">Expenses</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="container mt-3">
                                <div class="row align-items-end g-3">
                                    <!-- From Date -->
                                    <div class="col-md-2">
                                        <label>From *</label>
                                        <date-picker
                                            valueType="format"
                                            class="w-100"
                                            v-model="params.from"
                                        ></date-picker>
                                    </div>

                                    <!-- To Date -->
                                    <div class="col-md-2">
                                        <label>To *</label>
                                        <date-picker
                                            valueType="format"
                                            class="w-100"
                                            v-model="params.to"
                                        ></date-picker>
                                    </div>

                                    <!-- Filter Button -->
                                    <div class="col-md-1">
                                        <v-btn
                                            color="info"
                                            x-small
                                            @click="fetchExpenses(1)"
                                        >
                                            Filter
                                        </v-btn>
                                    </div>

                                    <!-- Search Input -->
                                    <div class="col-md-2">
                                        <label>Search</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            placeholder="Search"
                                            v-model="search_params.search"
                                        />
                                    </div>

                                    <!-- Search Category -->
                                    <div class="col-md-4">
                                        <label>Search Category</label>
                                        <treeselect
                                            :load-options="fetchCategory"
                                            :options="expense_category_select"
                                            :auto-load-root-options="false"
                                            v-model="
                                                search_params.expense_type_id
                                            "
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Select Category"
                                            class="w-100"
                                        />
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="">Branch Filter</label>
                                        <treeselect
                                            :load-options="fetchBranch"
                                            :options="branch_select_data"
                                            :auto-load-root-options="false"
                                            v-model="search_params.branch_id"
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Select   Branch "
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <span class="badge badge-secondary totals-badge"
                                >Total Expenses:
                                {{ cashFormatter(total_expenses) }}</span
                            >

                            <div class="table-responsive">
                                <table
                                    class="table table-bordered table-sm stylish-table custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th scope="col">Created at</th>
                                            <th scope="col">Ref</th>
                                            <th scope="col">Categorye<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_expense_type
                                                    "
                                                    sort-key="sort_expense_type"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                /></th>
                                            <th scope="col">Details</th>
                                            <th>Created By<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_user
                                                    "
                                                    sort-key="sort_user"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                /></th>
                                            <th>Supplier</th>
                                            <th>Expense Date<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_date_recorded
                                                    "
                                                    sort-key="sort_date_recorded"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                /></th>
                                            <th>CU No</th>
                                            <th>
                                                Amount<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_amount
                                                    "
                                                    sort-key="sort_amount"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                />
                                            </th>
                                            <th>Amount Paid<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_amount_paid
                                                    "
                                                    sort-key="sort_amount_paid"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                /></th>
                                            <th>Unpaid Balance<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_unpaid_balance
                                                    "
                                                    sort-key="sort_unpaid_balance"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                /></th>
                                            <th>Branch</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in expenses.data"
                                            :key="i"
                                        >
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
                                                <template
                                                    v-if="
                                                        value.amount_paid <
                                                        value.amount
                                                    "
                                                >
                                                    <v-btn
                                                        x-small
                                                       color="info"
                                                        v-if="isUpdatePermitted"
                                                        title="update"
                                                        @click="
                                                            payExpenses(value)
                                                        "
                                                    >
                                                        Pay
                                                    </v-btn></template
                                                >
                                            </td>
                                            <td scope="row">
                                                {{
                                                    formatDateTime(
                                                        value.created_at
                                                    )
                                                }}
                                            </td>
                                            <td>{{ value.ref_no }}</td>
                                            <td>
                                                {{ value.expense_type.name }}
                                            </td>
                                            <td>
                                                {{ value.details }}
                                            </td>
                                            <td>
                                                {{ value.user.name }}
                                            </td>

                                            <td>
                                                {{
                                                    value.supplier.company_name
                                                }}
                                            </td>
                                            <td>
                                                {{ value.date_recorded }}
                                            </td>
                                            <td>{{ value.cu_invoice_no }}</td>
                                            <td>
                                                {{
                                                    cashFormatter(value.amount)
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
                                                {{
                                                    cashFormatter(
                                                        value.unpaid_balance
                                                    )
                                                }}
                                            </td>
                                            <td>{{ value.branch.branch }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <pagination
                                    v-if="expenses !== null"
                                    v-bind:results="expenses"
                                    v-on:get-page="fetchExpenses"
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
        <Modal v-model="new_expenses_modal" title="Add Expenses" width="1200px">
            <record-expenses
                v-if="new_expenses_modal"
                v-on:dashboard-active="dismissDiag"
            />
        </Modal>
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import AddExpenseType from "./AddExpenseType.vue";
import ExpensesNav from "./ExpensesNav.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Pagination from "../../utilities/Pagination.vue";
import PayExpenses from "./PayExpenses.vue";
import RecordExpenses from "./RecordExpenses.vue";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    components: {
        AddExpenseType,
        Unauthorized,
        Treeselect,
        Pagination,
        ExpensesNav,
        PayExpenses,
        RecordExpenses,
        SortButtons,
    },
    data() {
        return {
            new_expenses_modal: false,
            expenses: [],
            line_data: null,
            payment_modal: false,
            add_expenses_modal: false,
            btn_loading: false,
            expense_category_select: null,
            total_expenses: 0,
            search_params: {
                expense_type_id: null,
                search: "",
                branch_id: null,
            },
            sort_params: {sort_date_recorded:"", sort_amount: "",sort_amount_paid:"",sort_unpaid_balance:"",sort_expense_type:"",sort_user:"" },
            params: {
                to: null,
                from: null,
            },
        };
    },
    watch: {
          sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.fetchExpenses(1);
            }, 500),
        },
        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.fetchExpenses(1);
            }, 500),
        },
    },

    mounted() {
        this.fetchExpenses(1);
    },
    methods: {
          updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        dismissDiag() {
            this.new_expenses_modal = false;
            this.payment_modal = false;
            this.fetchExpenses(1);
        },
        payExpenses(value) {
            this.line_data = value;
            this.payment_modal = true;
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/expense/fetch", {
                params: {
                    ...this.params,
                    ...this.search_params,
                },
            });
            this.hideLoader();

            if (res.status == 200) {
                var data = res.data.results;

                var data_array = [];
                data.map((array_item) => {
                    data_array.push({
                        created_at: this.formatDateTime(array_item.created_at),
                        expense_type: array_item.expense_type.name,
                        ref_no: array_item.ref_no,
                        details: array_item.details,
                        supplier: array_item.supplier.company_name,
                        date_recorded: array_item.date_recorded,
                        cu_invoice_no: array_item.cu_invoice_no,
                        user: array_item.user.name,
                        amount: array_item.amount,
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
                    "data/expense/destroy/" + id,
                    {}
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.s("deleted");
                    this.fetchExpenses(1);
                } else {
                    this.form_error(res);
                }
            }
        },
        async fetchExpenses(page) {
            this.showLoader();
            const res = await this.getApi("data/expense/fetch", {
                params: {
                    page,
                    ...this.search_params,
                    ...this.params,
                    ...this.sort_params
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.expenses = res.data.results;
                this.total_expenses = res.data.total;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
