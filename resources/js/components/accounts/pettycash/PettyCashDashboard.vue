<template>
    <div class="container">
        <div class="row justify-content-center" v-if="isReadPermitted">
            <div class="col-md-8">
                <v-btn @click="add_petty_cash_modal = true" dark color="primary"
                    >+ Add</v-btn
                >
                <div class="card">
                    <div class="card-header"><h3>PETTY CASH</h3></div>

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
                                        @click="fetch(1)"
                                    >
                                        Filter
                                    </button>
                                </div>
                                <div class="form-group">
                                    <input
                                        type="text"
                                        class="form-control mt-6"
                                        placeholder="Search"
                                        v-model="search"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <span class="badge badge-secondary totals-badge"
                                >Total :
                                {{ cashFormatter(total_expenses) }}</span
                            >
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Created at</th>
                                          
                                            <th>Created By</th>
                                            <th> Date</th>
                                            <th>Closing Amt</th>
                                            <th>Opening Amt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in expenses.data"
                                            :key="i"
                                        >
                                            <td scope="row">
                                                {{
                                                    formatDateTime(
                                                        value.created_at
                                                    )
                                                }}
                                            </td>
                                          
                                            <td>
                                                {{ value.user.name }}
                                            </td>
                                            <td>
                                                {{ value.date_recorded }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(value.closing_balance)
                                                }}
                                            </td>
                                              <td>
                                                {{
                                                    cashFormatter(value.opening_balance)
                                                }}
                                            </td>
                                            <td>
                                                <button
                                                    class="btn btn-sm btn-danger"
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
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <pagination
                                    v-if="expenses !== null"
                                    v-bind:results="expenses"
                                    v-on:get-page="fetch"
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
            v-model="add_petty_cash_modal"
            title="Add Expense Category"
            width="60px"
        >
            <AddPettyCashBalaces v-if="add_petty_cash_modal" v-on:closing_diag="completeInput" />
        </Modal>
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import AddPettyCashBalaces from "./AddPettyCashBalaces.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Pagination from "../../utilities/Pagination.vue";
export default {
    components: { AddPettyCashBalaces, Unauthorized, Treeselect, Pagination },
    data() {
        return {
            expenses: [],
            add_petty_cash_modal: false,
            btn_loading: false,
            expense_category_select: null,
            total_expenses: 0,
            form_data: {
                expense_type_id: null,
                date_recorded: "",
                amount: 0,
            },
            search: "",
            params: {
                to: null,
                from: null,
            },
        };
    },
    watch: {
        search: {
            handler: _.debounce(function () {
                this.fetch(1);
            }, 500),
        },
    },

    mounted() {
        this.form_data.date_recorded = this.getCurrentDate();

        this.fetch(1);
    },
    methods: {
        completeInput(){
            this.add_petty_cash_modal=false
            this.fetch(1)
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/petty_cash/fetch", {
                params: {
                    search: this.search,
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status == 200) {
                var data = res.data.results;

                var data_array = [];
                data.map((array_item) => {
                    data_array.push({
                      
                        date_recorded: array_item.date_recorded,
                        user: array_item.user.name,
                        opening_bal: array_item.opening_balance,
                        closing_bal: array_item.closing_balance,
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
                    "data/petty_cash/destroy/" + id,
                    {}
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.s("deleted");
                    this.fetch(1);
                } else {
                    this.form_error(res);
                }
            }
        },
        async fetch(page) {
            this.showLoader();
            const res = await this.getApi("data/petty_cash/fetch", {
                params: {
                    page,
                    search: this.search,
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.expenses = res.data.results;
                this.total_expenses = res.data.total.total_amount;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        
    },
};
</script>
