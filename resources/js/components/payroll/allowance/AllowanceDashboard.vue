<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-7">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                            v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_allowance')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5 style="color:red">Allowance Records</h5>
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
                                    <th scope="col">Allowance Amount</th>
                                    <th scope="col"> Allowance Type</th>
                                  
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in allowance_data.data"
                                    :key="i"
                                >
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="editAllowance(data)"
                                        >
                                           {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                          </router-link
                                        >
                                    </td>

                                  
                                    <td>{{cashFormatter( data.amount) }}</td>
                                    
                                    <td>{{ data.allowance_type }}</td>
                                    
                                   
                                    <td>
                                        <button
                                         v-if="isDeletePermitted"
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
                            v-if="allowance_data !== null"
                            v-bind:results="allowance_data"
                            v-on:get-page="fetchllowance"
                        ></Pagination>
                        Items Count {{ allowance_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                    Export Excel</button>
                                </export-excel>

                                <export-excel
                                v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                <button class="btn btn-primary btn-sm">
                                    Export CSV</button>
                                </export-excel>
                              
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      
        <add-allowance
            v-if="active.add_allowance"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-allowance>
        <edit-allowance
            v-if="active.edit_allowance"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-allowance>
    </div>
</template>

<script>
import AddAllowance from "./AddAllowance.vue";
import EditAllowance from "./EditAllowance.vue";
import Pagination from "../../utilities/Pagination.vue";
import GenerateAllowancePdf from "./GenerateAllowancePdf.vue";
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
                add_allowance: false,
                edit_allowance: false
            },
            allowance_data: [],
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
        AddAllowance,
        EditAllowance,
        VueHtml2pdf,
        Pagination,
        GenerateAllowancePdf
    },
    mounted() {
        this.fetchllowance(1);
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
                    "data/allowance/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.allowance_data.data.splice(i, 1);
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
                    allowance_type: array_item.allowance_type,
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,

                    amount: array_item.amount,
                 
                });
            });

            return data_array;
        },
        editAllowance(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_allowance");
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
            const res = await this.getApi("data/allowance/fetchAll",{
                params: {
                  
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });

            return res.data.results;
        },

        async searchService() {
            let page = 1;
            const res = await this.getApi("data/allowance/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.allowance_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchllowance(1);
        },

        async fetchllowance(page) {
            this.showLoader();
            const res = await this.getApi("data/allowance/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.allowance_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
