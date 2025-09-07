<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-9">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                            v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_deduction')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5 style="color:red">Deductions Records</h5>
                        </div>
                        <div class="col-md-4 float-md-right ">
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
                                    <th scope="col">Deduction Type</th>
                                    <th scope="col">Employee</th>
                                    <th scope="col">
                                        Monthly Deduction Amount
                                    </th>
                                     <th scope="col">Outstanding Amount</th>
                                    <th scope="col">Total Deduction</th>
                                    <th scope="col">No of Months</th>
                                    <th scope="col">Effective Date</th>
                                    <th scope="col">Completion Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in deduction_data.data"
                                    :key="i"
                                >
                                    <td>
                                       
                                            {{ data.deduction_type }}
                                       
                                    </td>
                                    <td>
                                        {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            cashFormatter(data.monthy_deduction)
                                        }}
                                    </td>
                                      <td>
                                        {{
                                            cashFormatter(data.outstanding_balance)
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.deduction_total)
                                        }}
                                    </td>
                                    <td>{{ data.no_of_months }}</td>
                                    <td>{{ data.effective_date }}</td>
                                    <td>{{ data.end_date }}</td>
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
                            v-if="deduction_data !== null"
                            v-bind:results="deduction_data"
                            v-on:get-page="fetchDeduction"
                        ></Pagination>
                        Items Count {{ deduction_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
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

        <add-deduction
            v-if="active.add_deduction"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-deduction>
        <edit-deduction
            v-if="active.edit_deduction"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-deduction>
    </div>
</template>

<script>
import AddDeduction from "./AddDeduction.vue";
import EditDeduction from "./EditDeduction.vue";
import Pagination from "../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_deduction: false,
                edit_deduction: false
            },
            deduction_data: [],
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
        AddDeduction,
        EditDeduction,
        VueHtml2pdf,
        Pagination
    },
    mounted() {
        this.fetchDeduction(1);
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
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/deductions/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.deduction_data.data.splice(i, 1);
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
                    deduction_type: array_item.deduction_type,
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,
                    monthy_deduction: array_item.monthy_deduction,
                    deduction_total: array_item.deduction_total,
                    outstanding_balance:array_item.outstanding_balance,
                    effective_date: array_item.effective_date
                });
            });

            return data_array;
        },
        editDeduction(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_deduction");
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
            const res = await this.getApi("data/deductions/fetchAll", "");

            return res.data.results;
        },

        async searchService() {
            let page = 1;
            const res = await this.getApi("data/deductions/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.deduction_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchDeduction(1);
        },

        async fetchDeduction(page) {
            this.showLoader();
            const res = await this.getApi("data/deductions/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.deduction_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
