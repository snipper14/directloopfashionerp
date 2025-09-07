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
                            v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_timelogs')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5 style="color:red"><b> Overtime Time Logs</b></h5>
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
                                    <th scope="col">Logs Date</th>
                                    <th scope="col">Employee</th>
                                    <th scope="col">Time In</th>
                                    <th scope="col">Time Out</th>
                                    <th scope="col">HRS</th>
                                    <th scope="col">HRLY RATE</th>
                                    <th scope="col">Amount Total</th>
                                    <th>Payment Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in timelogs_data.data"
                                    :key="i"
                                >
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="editLogs(data)"
                                        >
                                            {{ data.logged_on }}</router-link
                                        >
                                    </td>

                                    <td>
                                        {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                    </td>
                                    <td>{{ data.time_in }}</td>
                                    <td>{{ data.time_out }}</td>
                                    <td>{{ data.no_hours }}</td>
                                    <td>{{cashFormatter( data.employee.hrly_rate) }}</td>
                                    <td>{{cashFormatter(data.total_amount) }}</td>
                                      <td>{{data.isPaid==1? "Paid":"Unpaid"}}</td>
                                    <td>
                                        <button
                                        v-if="isDeletePermitted && data.isPaid==0"
                                            class="btn btn-danger btn-sm"
                                            @click="deleteRecord(data.id, i)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="timelogs_data !== null"
                            v-bind:results="timelogs_data"
                            v-on:get-page="fetchTimeLogs"
                        ></Pagination>
                        Items Count {{ timelogs_data.total }}

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

        <div>
            <vue-html2pdf
                :show-layout="false"
                :float-layout="true"
                :enable-download="true"
                :preview-modal="true"
                :paginate-elements-by-height="1400"
                filename="logs"
                :pdf-quality="2"
                :manual-pagination="false"
                pdf-format="a4"
                pdf-orientation="landscape"
                pdf-content-width="95%"
                ref="html2Pdf"
            >
                <section slot="pdf-content">
                    <generate-time-logs-pdf v-bind:pdf_data="pdf_data" />
                </section>
            </vue-html2pdf>
        </div>
        <add-time-log
            v-if="active.add_timelogs"
            v-on:dashboard-active="setActiveRefresh"
            :employee_data="employee_select_data"
        >
        </add-time-log>
        <edit-time-log
            v-if="active.edit_timelogs"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-time-log>
    </div>
</template>

<script>
import AddTimeLog from "./AddTimeLog.vue";
import EditTimeLog from "./EditTimeLog.vue";
import Pagination from "../../utilities/Pagination.vue";
import GenerateTimeLogsPdf from "./GenerateTimeLogsPdf.vue";
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
                add_timelogs: false,
                edit_timelogs: false
            },
            timelogs_data: [],
            edit_data: null,
            pdf_data: [],
         employee_data:null,
         employee_select_data:null,
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
        AddTimeLog,
        EditTimeLog,
        VueHtml2pdf,
        Pagination,
        GenerateTimeLogsPdf
    },
    mounted() {
        this.fetchTimeLogs(1)
        this.fetchEmployees();
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
    
           async fetchEmployees() {
           
            const res = await this.getApi("data/employee/getAll", "");
           
            if(res.status==200){
            this.employee_data=res.data.results
            this.employee_select_data=this.modifyProductKey()
            }else{
                this.swr('Server error could not fetch employees')
            }
        },
              modifyProductKey() {
            let modif = this.employee_data.map(obj => {
                return {
                    id: obj.id,
                    label:
                        obj.first_name +
                        "  " +
                        obj.last_name 
                       
                };
            });
            return modif;
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/time_logs/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.timelogs_data.data.splice(i, 1);
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
                    logged_on: array_item.logged_on,
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,

                    no_hours: array_item.no_hours,
                    time_in: array_item.time_in,
                    time_out: array_item.time_out,
                    total_amount: array_item.total_amount
                });
            });

            return data_array;
        },
        editLogs(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_timelogs");
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
            const res = await this.getApi("data/time_logs/fetchAll", {
                params: {
                  
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });

            return res.data.results;
        },

        async searchService() {
            let page = 1;
            const res = await this.getApi("data/time_logs/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.timelogs_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchTimeLogs(1);
        },

        async fetchTimeLogs(page) {
            this.showLoader();
            const res = await this.getApi("data/time_logs/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                 this.timelogs_data= res.data.results;
            } else {
               
                this.swr("Server error try again later");
            }
        }
        
    }
};
</script>
<style scoped></style>
