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
                                Medical Report for {{ edit_data.company_name }}
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
                                            <th scope="col">Symptoms</th>

                                            <th scope="col">Provider Report</th>
                                            <th>Current Medication</th>
                                            <th>Allergies</th>
                                            <th>
                                                Surgeries / Medical Procedures
                                            </th>
                                            <th>Family Medical Hist.</th>
                                            <th>Date Created</th>

                                            <th>Update</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in medical_report.data"
                                            :key="i"
                                        >
                                            <td>{{ data.entry_code }}</td>
                                            <td>
                                                {{ data.symptoms_complaints }}
                                            </td>

                                            <td>
                                                {{
                                                    data.healthcare_provider_report
                                                }}
                                            </td>
                                            <td>
                                                {{ data.current_medication }}
                                            </td>
                                            <td>{{ data.allergies }}</td>
                                            <td>
                                                {{
                                                    data.past_surgeries_medical_procedures
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.family_medical_history
                                                }}
                                            </td>
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
                                                        addMedicalHistory(data)
                                                    "
                                                    title="UPDATE"
                                                    color="primary"
                                                    x-small
                                                    >Update
                                                </v-btn>
                                                <v-btn
                                                    v-if="isDownloadPermitted"
                                                    @click="printRes(data)"
                                                    title="DOWNLOAD"
                                                    color="secondary"
                                                    x-small
                                                    >Print
                                                </v-btn>
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
                                v-if="medical_report !== null"
                                v-bind:results="medical_report"
                                v-on:get-page="fetchMedicalHistory"
                            ></pagination>
                            Items Count {{ medical_report.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <UpdateMedicalInfo
            v-if="active.update_report"
            :details_data="details_data"
            @dismiss-diag="dismissDialog"
        />
        <PrintMedicalReport
            v-if="show_print"
            ref="PrintMedicalReport"
            :details_data="details_data"
            
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
import UpdateMedicalInfo from "./UpdateMedicalInfo.vue";
import PrintMedicalReport from "./PrintMedicalReport.vue";

export default {
    props: ["edit_data"],
    data() {
        return {
            active: {
                dashboard: true,

                update_report: false,
            },
            medical_report: "",
            customer_data: [],
            show_print: false,
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
        UpdateMedicalInfo,
        PrintMedicalReport,
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
        async printRes(data) {
            this.show_print = true;
            this.details_data =data;
            setTimeout(() => {
                this.$refs.PrintMedicalReport.printBill();
                this.show_print = false;
            }, 1000);
        },
        async deleteData(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (!shouldDelete) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/medical/deleteMedicalRecord",
                data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.medical_report.data.splice(i, 1);
            }
        },
        dismissDialog() {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        addMedicalHistory(data) {
            this.details_data = data;
            this.setActiveComponent("update_report");
        },

        async concurrentApiReq() {
            //  this.show_loader ? this.showLoader() : "";

            await Promise.all([this.fetchMedicalHistory(1)]);
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

        async fetchMedicalHistory(page) {
            this.params.from = this.formatDateTime(this.params.from);
            this.params.to = this.formatDateTime(this.params.to);
            const mergedObject = Object.assign({}, this.params, {
                customer_id: this.edit_data.id,
            });

            const res = await this.getApi("data/medical/fetchMedicalHistory", {
                params: {
                    page,
                    ...mergedObject,
                },
            });

            if (res.status === 200) {
                this.medical_report = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
