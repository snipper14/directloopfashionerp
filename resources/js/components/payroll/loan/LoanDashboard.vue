<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                    
                        <div class="col-md-2">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_loans')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-2">
                            <h5 style="color:red">Loan Records</h5>
                        </div>
                        <div class="col-md-4">
                           
                        </div>
                        <div class="col-md-4  ">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Employee</th>

                                    <th scope="col">Loan Date</th>

                                    <th scope="col">Loan Amount</th>
                                    <th scope="col">Installment Amount</th>
                                    <th scope="col">Outstanding Balance</th>
                                    <th scope="col">Loan Period</th>
                                    <th scope="col">Percentage Interest</th>
                                    <th scope="col">Amount+Interest</th>

                                    <th scope="col">Loan Type</th>
                                    <th scope="col">Completion Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in loans_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                    </td>

                                    <td>
                                        {{ data.loan_date }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.loan_amount) }}
                                    </td>
                                    <td>
                                        <strong>
                                            {{
                                                cashFormatter(
                                                    data.installment_amount
                                                )
                                            }}
                                        </strong>
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.outstanding_balance
                                            )
                                        }}
                                    </td>
                                    <td>{{ data.loan_period }}</td>
                                    <td>{{ data.percent_interest }}</td>
                                    <td>
                                        <strong>
                                            {{
                                                cashFormatter(
                                                    data.total_payable_amount
                                                )
                                            }}
                                        </strong>
                                    </td>
                                    <td>{{ data.loan_type }}</td>
                                    <td>{{ data.completion_date }}</td>
                                    <td>
                                        <button
                                            v-if="isDeletePermitted"
                                            class="btn btn-danger btn-small"
                                            @click="deleteRecord(data.id, i)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="loans_data !== null"
                            v-bind:results="loans_data"
                            v-on:get-page="fetchLoans"
                        ></Pagination>
                        Items Count {{ loans_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                 v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
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
        <add-loan
            v-if="active.add_loans"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-loan>
        <edit-loan
            v-if="active.edit_loans"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-loan>
    </div>
</template>

<script>
import AddLoan from "./AddLoan.vue";
import EditLoan from "./EditLoan.vue";
import Pagination from "../../utilities/Pagination.vue";
import GenerateLoansPdf from "./GenerateLoansPdf.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import CustomSelect from "../../utilities/CustomSelect.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_loans: false,
                edit_loans: false
            },
            loans_data: [],
            edit_data: null,
            pdf_data: [],

            search: "",
            params: {},
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        AddLoan,
        EditLoan,
        VueHtml2pdf,
        Pagination,
        GenerateLoansPdf,
        CustomSelect,
        Treeselect
    },
    mounted() {
        this.fetchLoans(1);
    },

    watch: {
        params: {
            handler() {
                this.searchService();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchService();
            }
        }
    },
    methods: {
     async   employeeIdFromComponent(value) {
          this.showLoader()
            const res = await this.getApi("data/loans/fetchByUser", {
                params: {
                    id:value,
                  
                }
            });
            this.hideLoader()
            if (res.status === 200) {
                this.loans_data = res;
            }else{
                this.swr("Server error occured");
            }
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/loans/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.loans_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async generateExcel() {
            const res = await this.fetchAll();
            var data_array = [];
            res.map(array_item => {
                data_array.push({
                    loan_date: array_item.loan_date,
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,

                    description: array_item.description,
                    loan_date: array_item.loan_date,
                    loan_amount: array_item.loan_amount,
                    installment_amount: array_item.installment_amount,
                    outstanding_balance: array_item.outstanding_balance,
                    completion_date: array_item.completion_date,

                    percent_interest: array_item.percent_interest,
                    loan_period: array_item.loan_period,
                    loan_type: array_item.loan_type,
                    total_payable_amount: array_item.total_payable_amount
                });
            });

            return data_array;
        },
        editLoans(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_loans");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.fetchAll();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async fetchAll() {
            const res = await this.getApi("data/loans/fetchAll",  {
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });

            return res.data.results;
        },

        async searchService() {
            let page = 1;
            const res = await this.getApi("data/loans/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.loans_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchLoans(1);
        },

        async fetchLoans(page) {
            this.showLoader();
            const res = await this.getApi("data/loans/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.loans_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
