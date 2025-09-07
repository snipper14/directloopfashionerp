<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                class="ma-2 v-btn-primary"
                                @click="
                                    setActiveComponent('add_employee_details')
                                "
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Employee Details Records</h5>
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
                                    <th scope="col">Name</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Employee Type</th>
                                    <th scope="col">Salary</th>
                                    <th scope="col">NHIF</th>
                                    <th scope="col">NSSF</th>
                                    <th scope="col">PAYE</th>
                                    <th scope="col">House Allowance</th>
                                    <th scope="col">Other Deductions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data,
                                    i) in employee_details_data.data"
                                    :key="i"
                                >
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="
                                                editEmployeeDetails(data)
                                            "
                                        >
                                            {{
                                                data.employee.first_name +
                                                    " " +
                                                    data.employee.last_name
                                            }}</router-link
                                        >
                                    </td>
                                    <td>{{ data.department.department }}</td>
                                    <td>{{ data.employee_type.name }}</td>
                                    <td>{{ cashFormatter(data.salary) }}</td>
                                    <td>{{ cashFormatter(data.nhif) }}</td>
                                    <td>{{ cashFormatter(data.nssf) }}</td>
                                    <td>{{ cashFormatter(data.paye) }}</td>
                                    <td>
                                        {{
                                            cashFormatter(data.house_allowance)
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.other_deductions)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="employee_details_data !== null"
                            v-bind:results="employee_details_data"
                            v-on:get-page="fetchEmployeeDetails"
                        ></Pagination>
                        Items Count {{ employee_details_data.total }}

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
                                <button
                                    class="btn btn-default btn-small"
                                    @click="generateReport"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiFilePdfBoxOutline
                                    }}</v-icon>
                                    Export pdf
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <vue-html2pdf
                :show-layout="false"
                :float-layout="true"
                :enable-download="true"
                :preview-modal="true"
                :paginate-elements-by-height="1400"
                filename="employee_details"
                :pdf-quality="2"
                :manual-pagination="false"
                pdf-format="a4"
                pdf-orientation="landscape"
                pdf-content-width="95%"
                ref="html2Pdf"
            >
                <section slot="pdf-content">
                    <generate-pdf v-bind:pdf_data="pdf_data" />
                </section>
            </vue-html2pdf>
        </div>
        <add-employee-details
            v-if="active.add_employee_details"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-employee-details>
        <edit-employee-details
            v-if="active.edit_employee_details"
            v-bind:data="edit_employee_details_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-employee-details>
    </div>
</template>

<script>
import AddEmployeeDetails from "./AddEmployeeDetails.vue";
import EditEmployeeDetails from "./EditEmployeeDetails.vue";
import Pagination from "../../utilities/Pagination.vue";
import GeneratePdf from "./GeneratePdf.vue";
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
                add_employee_details: false,
                edit_employee_details: false
            },
            employee_details_data: [],
            edit_employee_details_data: null,
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
        AddEmployeeDetails,
        EditEmployeeDetails,
        VueHtml2pdf,
        Pagination,
        GeneratePdf
    },
    mounted() {
        this.fetchEmployeeDetails(1);
    },
    watch: {
        params: {
            handler() {
                this.serchEmployeeRecords();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.serchEmployeeRecords();
            }
        }
    },
    methods: {
        editEmployeeDetails(data) {
            this.edit_employee_details_data = data;

            this.setActiveComponent("edit_employee_details");
        },
        async generateExcel() {
            const res = await this.getAllEmployeesDetails();

            var data_array = [];
            res.map(array_item => {
                data_array.push({
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,
                    first_name: array_item.employee.first_name,
                    department: array_item.department.department,
                    employment_type: array_item.employee_type.name,
                    salary: array_item.salary,
                    nssf: array_item.nssf,
                    nhif: array_item.nhif,
                    paye: array_item.paye,
                    other_deductions: array_item.other_deductions
                });
            });

            return data_array;
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllEmployeesDetails();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllEmployeesDetails() {
            const res = await this.getApi(
                "data/employee_details/getAllEmpDetails",
                ""
            );

            return res.data.results;
        },

        async serchEmployeeRecords() {
            let page = 1;
            const res = await this.getApi("data/employee_details/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.employee_details_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchEmployeeDetails();
        },

        async fetchEmployeeDetails(page) {
            this.showLoader();
            const res = await this.getApi("data/employee_details/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.employee_details_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
