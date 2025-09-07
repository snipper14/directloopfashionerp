<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12" v-if="isReadPermitted">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_customer')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Medical Management</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Client</th>

                                        <th scope="col">Co. Contacts</th>

                                        <th>Date Created</th>

                                        <th>Generate Sickoff</th>
                                        <th>Lab Info</th>
                                        <th>Diagnosis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in customer_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.company_name }}
                                        </td>

                                        <td>{{ data.company_phone }}</td>

                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>

                                        <td>
                                            <v-btn
                                                v-if="isUpdatePermitted"
                                                @click="addSickoff(data)"
                                                title="UPDATE"
                                                color="secondary"
                                                x-small
                                                >Create Sickoff</v-btn
                                            >
                                            <v-btn
                                                v-if="isDisplayPermitted"
                                                @click="sickOffReport(data)"
                                                title="DISPLAY"
                                                color="primary"
                                                x-small
                                            >
                                                Sickoff Report</v-btn
                                            >
                                        </td>
                                        <td>
                                            <v-btn
                                                v-if="isUpdatePermitted"
                                                @click="addLabResults(data)"
                                                title="UPDATE"
                                                color="primary"
                                                x-small
                                                >Create Lab Results</v-btn
                                            >
                                            <v-btn
                                                v-if="isUpdatePermitted"
                                                @click="labResults(data)"
                                                title="update"
                                                color="info"
                                                x-small
                                            >
                                                Lab Result History</v-btn
                                            >
                                        </td>
                                        <td>
                                            <v-btn
                                                v-if="isUpdatePermitted"
                                                @click="addDiagnosis(data)"
                                                title="UPDATE"
                                                color="primary"
                                                x-small
                                                >Create Medical Info</v-btn
                                            >
                                            <v-btn
                                                v-if="isAdmin"
                                                @click="addMedicalHistory(data)"
                                                title="ADMIN"
                                                color="info"
                                                x-small
                                            >
                                                Medical History</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <pagination
                            v-if="customer_data !== null"
                            v-bind:results="customer_data"
                            v-on:get-page="fetchCustomers"
                        ></pagination>
                        Items Count {{ customer_data.total }}
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>

        <add-customer
            v-if="active.add_customer"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-customer>
        <edit-customer
            v-if="active.edit_customer"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-customer>

        <AddPatientDiagnosisComponent
            v-if="active.medical_info"
            :edit_data="edit_data"
            @dismiss-diag="dismissDialog"
        />
        <MedicalHistoryReportComponent
            v-if="active.medical_history_report"
            :edit_data="edit_data"
            @dismiss-diag="dismissDialog"
        />
        <lab-result-report-component
        v-if="active.lab_history_report"
         :edit_data="edit_data"
            @dismiss-diag="dismissDialog"
        />
        <Modal v-model="sickoff_modal" width="1000px">
            <add-sickoff-modal
                v-if="sickoff_modal"
                :edit_data="edit_data"
                @dismiss-diag="dismissDialog"
            />
        </Modal>
        <Modal v-model="sickoff_report_modal" width="1200px">
            <SickoffReportModal
                v-if="sickoff_report_modal"
                :edit_data="edit_data"
                @dismiss-diag="dismissDialog"
            />
        </Modal>
        <Modal v-model="add_lab_result_modal" width="800px">
            <add-labresult-modal
                v-if="add_lab_result_modal"
                :edit_data="edit_data"
                @dismiss-diag="dismissDialog"
            />
        </Modal>
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import AddCustomer from "../../customers/AddCustomer.vue";
import EditCustomer from "../../customers/EditCustomer.vue";
import AddPatientDiagnosisComponent from "./AddPatientDiagnosisComponent.vue";
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import MedicalHistoryReportComponent from "./MedicalHistoryReportComponent.vue";
import AddSickoffModal from "../sickoff/AddSickoffModal.vue";
import SickoffReportModal from "../sickoff/SickoffReportModal.vue";
import AddLabresultModal from "../lab/AddLabresultModal.vue";
import LabResultReportComponent from '../lab/LabResultReportComponent.vue';

export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                medical_info: false,
                medical_history_report: false,
                lab_history_report:false,
            },
            add_lab_result_modal: false,
            sickoff_report_modal: false,
            customer_data: [],
            edit_data: null,
            pdf_data: [],
            sickoff_modal: false,
            search: "",
            params: {
                name: "",
                description: "",
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
        AddCustomer,
        EditCustomer,
        AddPatientDiagnosisComponent,
        Pagination,
        Unauthorized,
        MedicalHistoryReportComponent,
        AddSickoffModal,
        SickoffReportModal,
        AddLabresultModal,
        LabResultReportComponent,
    },
    mounted() {
        this.fetchCustomers(1);
    },
    watch: {
        params: {
            handler() {
                this.searchRecords();
            },
            deep: true,
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchRecords();
            }
        },
    },
    methods: {
        labResults(data) {
            this.edit_data = data;
            this.setActiveComponent("lab_history_report");
        },

        addLabResults(data) {
            this.edit_data = data;
            this.add_lab_result_modal = true;
        },
        sickOffReport(data) {
            this.edit_data = data;
            this.sickoff_report_modal = true;
        },
        addSickoff(data) {
            this.edit_data = data;
            this.sickoff_modal = true;
        },
        addDiagnosis(data) {
            this.edit_data = data;
            this.setActiveComponent("medical_info");
        },

        addMedicalHistory(data) {
            this.edit_data = data;
            this.setActiveComponent("medical_history_report");
        },

        dismissDialog() {
            this.sickoff_modal = false;
            this.add_lab_result_modal = false;
            this.setActive(this.active, "dashboard");
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
        editCustomer(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_customer");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllCustomers();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllCustomers() {
            const res = await this.getApi("data/customers/fetchAll", "");

            return res.data.results;
        },

        async searchRecords() {
            let page = 1;
            const res = await this.getApi("data/customers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.customer_data = res.data.results;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchCustomers(1);
        },

        async fetchCustomers(page) {
            this.showLoader();
            const res = await this.getApi("data/customers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.customer_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
