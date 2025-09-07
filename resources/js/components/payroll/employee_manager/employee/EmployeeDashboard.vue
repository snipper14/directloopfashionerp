<template>
    <div class="container">
        <div v-if="isReadPermitted">
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-md-2">
                    <EmployeeManagerNav />
                </div>
                <div class="col-md-10">
                    <ImportExcel v-if="isWritePermitted" />
                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <div class="col-md-3">
                                <v-btn
                                    v-if="isWritePermitted"
                                    class="ma-2 v-btn-primary"
                                    @click="setActiveComponent('add_employee')"
                                    color="primary"
                                    dark
                                >
                                    <v-icon medium>{{
                                        icons.mdiPlusThick
                                    }}</v-icon>
                                    Add
                                </v-btn>
                            </div>
                            <div class="col-md-5">
                                <h5>Employee Management</h5>
                            </div>
                            <div class="col-md-4 float-md-right">
                                <input
                                    type="text"
                                    placeholder="Search.."
                                    class="form-control small-input"
                                    v-model="params.search"
                                />
                            </div>
                        </div>

                        <div class="card-body">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            Name
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_employee
                                                "
                                                sort-key="sort_employee"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">DOB</th>
                                        <th scope="col">ID</th>
                                        <th scope="col">Other</th>
                                        <th scope="col">Department</th>
                                        <th scope="col">Employee Type</th>
                                        <th scope="col">Payment Type</th>
                                        <th scope="col">
                                            Salary
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_salary
                                                "
                                                sort-key="sort_salary"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Hourly Rate</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in employee_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    editEmployee(data)
                                                "
                                            >
                                                {{
                                                    data.first_name +
                                                    " " +
                                                    data.last_name
                                                }}</router-link
                                            >
                                        </td>
                                        <td>{{ data.email }}</td>
                                        <td>{{ data.phone }}</td>
                                        <td>{{ data.dob }}</td>
                                        <td>{{ data.id_no }}</td>
                                        <td>{{ data.other }}</td>
                                        <td>
                                            {{ data.department.department }}
                                        </td>

                                        <td>{{ data.employee_type.name }}</td>
                                        <td>{{ data.payment_type }}</td>
                                        <td>
                                            {{ cashFormatter(data.salary) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.hrly_rate) }}
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-danger btn-sm"
                                                @click="
                                                    deleteRecord(data.id, i)
                                                "
                                                v-if="isDeletePermitted"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination
                                v-if="employee_data !== null"
                                v-bind:results="employee_data"
                                v-on:get-page="getPage"
                            ></Pagination>
                            Items Count {{ employee_data.total }}

                            <div class="row">
                                <div class="col-4 d-flex">
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="generateExcel"
                                        worksheet="My Worksheet"
                                        name="employeelist.xls"
                                        v-if="isDownloadPermitted"
                                    >
                                        <button
                                            class="btn btn-secondary btn-sm"
                                        >
                                            Export Excel
                                        </button>
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="generateExcel"
                                        v-if="isDownloadPermitted"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="emplooyeelist.xls"
                                    >
                                        <button
                                            class="btn btn-secondary btn-sm"
                                        >
                                            Export CSV
                                        </button>
                                    </export-excel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <unauthorized />
        </div>

        <add-employee
            v-if="active.add_employee"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-employee>
        <update-employee
            v-if="active.edit_employee"
            v-bind:data="edit_employee_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </update-employee>
    </div>
</template>

<script>
import AddEmployee from "./AddEmployee.vue";
import UpdateEmployee from "./UpdateEmployee.vue";
import Pagination from "../../../utilities/Pagination.vue";
import ImportExcel from "./ImportExcel.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import EmployeeManagerNav from "../EmployeeManagerNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import SortButtons from "../../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_employee: false,
                edit_employee: false,
            },
            sort_params: {
                sort_employee: "",
                sort_salary: "",
            },
            employee_data: [],
            edit_employee_data: null,
            pdf_data: [],

            params: { search: "" },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        AddEmployee,
        UpdateEmployee,
        Pagination,
        ImportExcel,
        Unauthorized,
        EmployeeManagerNav,
        SortButtons,
    },
    mounted() {
        this.fetchEmployee(1);
    },
    watch: {
        params: {
            handler() {
                this.fetchEmployee(1);
            },
            deep: true,
        },
        sort_params: {
            handler() {
                this.fetchEmployee(1);
            },
            deep: true,
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async generateExcel() {
             const res = await this.getApi("data/employee/fetch", {
                params: {
                  
                    ...this.params,
                    ...this.sort_params
                },
            });
            var data_array = [];
            res.data.results.map((array_item) => {
                data_array.push({
                     id: array_item.id,
                    first_name: array_item.first_name,
                    last_name: array_item.last_name,
                    id_no: array_item.id_no,
                    dob: array_item.dob,
                    phone: array_item.phone,
                    email: array_item.email,
                    other: array_item.other,
                    department: array_item.department.department,
                    employment_type: array_item.employee_type.name,
                    salary: array_item.salary,
                    hrly_rate: array_item.hrly_rate,
                    kra_pin: "",
                    job_no: array_item.job_no,
                    start_contract: array_item.start_contract,
                   
                    bank_branch: array_item.bank_branch,
                    bank_name: array_item.bank_name,
                    account_name: array_item.account_name,
                    account_no: array_item.account_no,
                    payment_phone: array_item.payment_phone,
                     payment_type: array_item.payment_type,
                     deduct_nssf:array_item.deduct_nssf,
                     deduct_nhif:array_item.deduct_nhif,
                     deduct_paye:array_item.deduct_paye,
                     deduct_housinglevy:array_item.deduct_housinglevy
                });
            });

            return data_array;
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/employee/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.employee_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        getPage(event) {
            //event;
            window.scrollTo(0, 0);
            this.fetchEmployee(event);
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllEmployees();
            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllEmployees() {
            const res = await this.getApi("data/employee/getAll", {
                params: {
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                    ...this.sort_params
                },
            });

            return res.data.results;
        },

        editEmployee(data) {
            this.edit_employee_data = data;

            this.setActiveComponent("edit_employee");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        async fetchEmployee(page) {
            this.showLoader();
            const res = await this.getApi("data/employee/fetch", {
                params: {
                    page,
                    ...this.sort_params,
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.employee_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },

        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchEmployee(1);
        },
    },
};
</script>
<style scoped>
.pdf-header {
    display: flex;
    flex-direction: column;
    justify-content: center !important;
    margin-top: 90px;
    margin-left: 25%;
}
</style>
