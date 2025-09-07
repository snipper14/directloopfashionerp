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
                                @click="$emit('dismiss-diag')"
                                color="primary"
                                dark
                            >
                                Back
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>
                                Lab Reports for {{ edit_data.company_name }}
                            </h5>
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
                        <div class="row">
                            <div class="d-flex justify-content around mt-3">
                                <div class="d-flex flex-column">
                                    <label> From Datetime *</label>

                                    <date-picker
                                        type="datetime"
                                        width="300px"
                                        v-model="params.from"
                                    ></date-picker>
                                </div>
                                <div class="d-flex flex-column">
                                    <label> To Datetime *</label>
                                    <date-picker
                                        type="datetime"
                                        width="300px"
                                        v-model="params.to"
                                    ></date-picker>
                                </div>
                                <div></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Code</th>

                                            <th>Blood Result</th>
                                            <th>Blood Pressure</th>
                                            <th>Heart Rate</th>
                                            <th>Respiratory Rate</th>
                                            <th>Temprature</th>
                                            <th>Height/Weight</th>
                                            <th>Date Created</th>

                                            <th>Update</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in lab_report.data"
                                            :key="i"
                                        >
                                            <td>{{ data.entry_code }}</td>
                                            <td>{{ data.blood_test }}</td>
                                            <td>{{ data.blood_pressure }}</td>
                                            <td>{{data.heart_rate}}</td>
                                            <td>{{data.respiratory_rate}}</td>
                                            <td>{{data.temperature}}</td>
                                            <td>{{data.height_and_weight}}</td>
                                            <td>
                                                {{
                                                    formatDateTime(
                                                        data.created_at
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <v-btn
                                                    v-if="isUpdatePermitted"
                                                    @click="
                                                        updateLabHistory(data)
                                                    "
                                                    title="UPDATE"
                                                    color="primary"
                                                    x-small
                                                    >View/Update</v-btn
                                                >
                                                  <v-btn
                                                    v-if="isDownloadPermitted"
                                                    @click="
                                                        printLabResult(data)
                                                    "
                                                    title="DOWNLOAD"
                                                    color="primary"
                                                    x-small
                                                    >Print Results</v-btn
                                                >
                                            </td>
                                            <td>
                                                <v-btn
                                                    v-if="isReadPermitted"
                                                    @click="deleteData(data, i)"
                                                    title="DELETE"
                                                    color="danger"
                                                    x-small
                                                >
                                                    Delete</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <pagination
                                v-if="lab_report !== null"
                                v-bind:results="lab_report"
                                v-on:get-page="fetchLabHistory"
                            ></pagination>
                            Items Count {{ lab_report.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
       <Modal v-model="add_lab_result_modal" width="800px">
            <update-labresult-modal
                v-if="add_lab_result_modal"
                :details_data="details_data"
                :edit_data="edit_data"
                @dismiss-diag="dismissDialog"
            />
        </Modal>
           <PrintLabResult
            v-if="show_print"
            ref="PrintLabResult"
            :details_data="details_data"
            :patient_info="patient_info"
           
           
        />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";

import Pagination from "../../utilities/Pagination.vue";

import UpdateLabresultModal from './UpdateLabresultModal.vue';
import PrintLabResult from './PrintLabResult.vue';

export default {
    props: ["edit_data"],
    data() {
        return {
            active: {
                dashboard: true,

                update_report: false,
            },
            patient_info:null,
            add_lab_result_modal:false,
            lab_report: "",
            customer_data: [],
show_print:false,

            show_loader: true,
            details_data: null,
            search: "",
            params: {
                name: "",
                description: "",

                from: null,
                to: null,
            },
            index: null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
       
        UpdateLabresultModal,
        PrintLabResult
    },
    mounted() {
        
        this.concurrentApiReq();
    },
    watch: {
        params: {
            handler() {
                // this.show_loader = false;
                this.concurrentApiReq();
            },
            deep: true,
        },
    },
    methods: {
        async printLabResult(data){

            this.details_data=data
            const res = await this.getApi("data/medical/fetchPatient", {
                params: {
                    customer_id: data.customer_id,
                },
            });
            if (res.status == 200) {
                this.show_print = true;
                this.patient_info = res.data;
                setTimeout(() => {
                    this.$refs.PrintLabResult.printBill();
                    this.show_print = false;
                }, 1000);
            } else {
                this.form_error(res);
            } 
        },
        async deleteData(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (!shouldDelete) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/lab/deleteLabRecord",
                data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.lab_report.data.splice(i, 1);
            }
        },
        dismissDialog() {
           this.add_lab_result_modal=false
            this.concurrentApiReq();
        },
        updateLabHistory(data) {
            this.details_data = data;
            this.add_lab_result_modal=true
        },

        async concurrentApiReq() {
            //  this.show_loader ? this.showLoader() : "";

            await Promise.all([this.fetchLabHistory(1)]);
            // this.show_loader ? this.hideLoader() : "";
        },

        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/customers/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.customer_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async fetchLabHistory(page) {
            this.params.from = this.formatDateTime(this.params.from);
            this.params.to = this.formatDateTime(this.params.to);
            const mergedObject = Object.assign({}, this.params, {
                customer_id: this.edit_data.id,
            });

            const res = await this.getApi("data/lab/fetchLabHistory", {
                params: {
                    page,
                    ...mergedObject,
                },
            });

            if (res.status === 200) {
                this.lab_report = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
