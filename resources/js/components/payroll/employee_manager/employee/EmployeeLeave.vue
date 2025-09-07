<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <EmployeeManagerNav/>
            </div>
            <div class="col-md-10">
              
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                            v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_leave_type')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add Leave Type
                            </v-btn>
                        </div>
                         <div class="col-md-3">
                            <v-btn
                            v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="leave_modal=true"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add Leave
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Employee Leave Management</h5>
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
                                    <th scope="col">Employee</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Start</th>
                                    <th scope="col">End</th>
                                   

                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in leave_data.data"
                                    :key="i"
                                >
                                    <td>
                                        
                                            {{
                                                data.employee.name 
                                            }}
                                    </td>
                                    <td>{{ data.leave_type.leave_type }}</td>
                                    <td>{{ data.start_from }}</td>
                                    <td>{{ data.end_at }}</td>
                                   
                                    <td>
                                        <button
                                            class="btn btn-danger btn-sm"
                                            @click="deleteRecord(data.id, i)"
                                              v-if="isDeletePermitted"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="leave_data !== null"
                            v-bind:results="leave_data"
                            v-on:get-page="getPage"
                        ></Pagination>
                        Items Count {{ leave_data.total }}

                  
                    </div>
                </div>
                <div v-else>
                    <unauthorized/>
                </div>
            </div>
        </div>

        <add-leave-type
            v-if="active.add_leave_type"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-leave-type>
       
        <Modal v-model="leave_modal" width="800px">
        <add-leave-modal
       v-if="leave_modal" 
       v-on:dashboard-active="setActiveRefresh"
        />
        </Modal>
      
    </div>
</template>

<script>

import UpdateEmployee from "./UpdateEmployee.vue";
import Pagination from "../../../utilities/Pagination.vue";
import ImportExcel from './ImportExcel.vue'
import Unauthorized from "../../../utilities/Unauthorized.vue";
import EmployeeManagerNav from "../EmployeeManagerNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import AddLeaveType from './leave/AddLeaveType.vue';
import AddLeaveModal from './leave/AddLeaveModal.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_leave_type: false,
                edit_employee: false
            },
            leave_modal:false,
            leave_data: [],
            edit_leave_data: null,
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
      
        UpdateEmployee,
        Pagination,
        ImportExcel,
        Unauthorized,
        EmployeeManagerNav,
        AddLeaveType,
        AddLeaveModal,
    },
    mounted() {
        this.fetchLeave(1);
    },
    watch: {
        params: {
            handler() {
                this.serchEmployee();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.serchEmployee();
            }
        }
    },
    methods: {
        async generateExcel() {
            const res = await this.getAllEmployees();
            var data_array = [];
            res.map(array_item => {
                data_array.push({
              
                    first_name: array_item.first_name,
                    last_name: array_item.last_name,
                    id_no: array_item.id_no,
                    dob: array_item.dob,
                    phone: array_item.phone,
                    email: array_item.email,
                    other: array_item.other,
                     department:array_item.department.department,
                     employment_type:array_item.employee_type.name,
                    salary: array_item.salary,
                     hrly_rate: array_item.hrly_rate,
                      kra_pin: "",
                job_no:  array_item.job_no,
                start_contract: array_item.start_contract,
                pay_method:  array_item.pay_method,
                bank_branch:  array_item.bank_branch,
                bank_name:  array_item.bank_name,
                account_name:  array_item.account_name,
                account_no:  array_item.account_no,
                payment_phone:  array_item.payment_phone
                });
            });
           
            return data_array;
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/leave/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.leave_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async serchEmployee() {
            let page = 1;
            const res = await this.getApi("data/employee/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.leave_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },
        getPage(event) {
            //event;
            window.scrollTo(0, 0);
            this.fetchLeave(event);
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
            const res = await this.getApi("data/employee/getAll",{
                params: {
                  
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });

            return res.data.results;
        },

        editEmployee(data) {
            this.edit_leave_data = data;

            this.setActiveComponent("edit_employee");
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        async fetchLeave(page) {
            this.showLoader();
            const res = await this.getApi("data/leave/fetch", {
                params: {
                    page,
                   
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.leave_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },

        setActiveRefresh: function() {
            this.leave_modal=false
            this.setActive(this.active, "dashboard");
            this.fetchLeave(1);
        }
    }
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
