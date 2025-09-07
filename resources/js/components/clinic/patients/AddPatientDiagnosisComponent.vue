<template>
    <div class="container">
        <v-btn color="primary" @click="$emit('dismiss-diag')">Back</v-btn>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Add Medical Data for {{ edit_data.company_name }}
                    </div>

                    <div class="card-body">
                        <nav>
                            <div
                                class="nav nav-tabs"
                                id="nav-tab"
                                role="tablist"
                            >
                                <a
                                    class="nav-item nav-link active"
                                    id="nav-home-tab"
                                    data-toggle="tab"
                                    href="#nav-home"
                                    role="tab"
                                    aria-controls="nav-home"
                                    aria-selected="true"
                                >
                                    Appointments and Visits</a
                                >

                                <a
                                    class="nav-item nav-link"
                                    id="nav-profile-tab"
                                    data-toggle="tab"
                                    href="#nav-profile"
                                    role="tab"
                                    aria-controls="nav-profile"
                                    aria-selected="false"
                                >
                                    Medical History</a
                                >
                                <a
                                    class="nav-item nav-link"
                                    id="nav-prescription-tab"
                                    data-toggle="tab"
                                    href="#nav-prescription"
                                    role="tab"
                                    aria-controls="nav-prescription"
                                    aria-selected="false"
                                >
                                    Add Prescriptions</a
                                >
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div
                                class="tab-pane fade show active"
                                id="nav-home"
                                role="tabpanel"
                                aria-labelledby="nav-home-tab"
                            >
                                <div class="row mt-3">
                                    <div class="col-md-7 d-flex flex-column">
                                        <label>Next Appointments Date </label>

                                        <date-picker
                                            type="datetime"
                                            width="300px"
                                            v-model="
                                                formData.date_time_appointments
                                            "
                                        ></date-picker>
                                    </div>
                                    <div class="col-md-7 form-group">
                                        <label for=""
                                            >Visiting Health Provider</label
                                        >
                                        <input
                                            type="text"
                                            v-model="
                                                formData.visiting_health_provider
                                            "
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-md-7 form-group">
                                        <label for=""
                                            >Purpose For Visiting</label
                                        >
                                        <input
                                            type="text"
                                            v-model="formData.purpose_of_visit"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-md-7 form-group">
                                        <label for="">Treatment Plans</label>
                                        <textarea
                                            type="text"
                                            rows="5"
                                            v-model="formData.treatment_plans"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-md-7 form-group">
                                        <label for=""
                                            >Healthcare Provider Report</label
                                        >
                                        <textarea
                                            type="text"
                                            rows="5"
                                            v-model="
                                                formData.healthcare_provider_report
                                            "
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-md-7 form-group">
                                        <label for=""
                                            >Symtoms & Complaints</label
                                        >
                                        <textarea
                                            type="text"
                                            rows="5"
                                            v-model="
                                                formData.symptoms_complaints
                                            "
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-md-7 form-group">
                                        <label for="">Current Medication</label>
                                        <textarea
                                            type="text"
                                            rows="5"
                                            v-model="
                                                formData.current_medication
                                            "
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tab-pane fade"
                                id="nav-profile"
                                role="tabpanel"
                                aria-labelledby="nav-profile-tab"
                            >
                                <div class="row mt-3">
                                    <div class="form-group col-md-5">
                                        <label for=""
                                            >Pre-existing medical
                                            conditions</label
                                        >
                                        <textarea
                                            rows="5"
                                            v-model="
                                                formData.pre_existing_medical_conditions
                                            "
                                            class="form-control"
                                        ></textarea>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="">Allergies</label>
                                        <textarea
                                            rows="5"
                                            v-model="formData.allergies"
                                            class="form-control"
                                        ></textarea>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for=""
                                            >Past surgeries or medical
                                            procedures</label
                                        >

                                        <textarea
                                            rows="5"
                                            v-model="
                                                formData.past_surgeries_medical_procedures
                                            "
                                            class="form-control"
                                        ></textarea>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for=""
                                            >Family medical history</label
                                        >

                                        <textarea
                                            rows="5"
                                            v-model="
                                                formData.family_medical_history
                                            "
                                            class="form-control"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tab-pane fade"
                                id="nav-prescription"
                                role="tabpanel"
                                aria-labelledby="nav-prescription-tab"
                            >
                                <div class="row mt-3">
                                    <h3>Search Products</h3>
                                    <div class="col-md-12">
                                        <add-prescription-component
                                            :edit_data="edit_data"
                                            :customer_id="edit_data.id"
                                            @stockSearchResult="submitRecords"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <v-btn
                                @click="completePatientsInfo()"
                                :loading="btn_loading"
                                color="primary"
                                >Complete Patients Information</v-btn
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AddPrescriptionComponent from "./AddPrescriptionComponent.vue";
export default {
    components: { AddPrescriptionComponent },
    props: ["edit_data"],
    data() {
        return {
            prescription_data: [],
            btn_loading: false,
            formData: {
                pre_existing_medical_conditions: "",
                allergies: "",
                past_surgeries_medical_procedures: "",
                family_medical_history: "",

                current_medication: "",

                imaging_results: null,
                other_test_results: "",
                date_time_appointments: "",
                visiting_health_provider: "",
                purpose_of_visit: "",
                treatment_plans: "",
                healthcare_provider_report: "pending",
                symptoms_complaints: "",
            },
            formVitalSigns: {},
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.fetchPatientMedicalHistory();
    },
    methods: {
        async fetchPatientMedicalHistory() {
            const res = await this.getApi(
                "data/medical/fetchPatientMedicalHistory",
                {
                    params: {
                        customer_id: this.edit_data.id,
                    },
                }
            );

            if (res.status === 200) {
                this.formData.allergies = res.data.allergies;
                this.formData.past_surgeries_medical_procedures =
                    res.data.past_surgeries_medical_procedures;
                this.formData.family_medical_history =
                    res.data.family_medical_history;
                this.formData.pre_existing_medical_conditions =
                    res.data.pre_existing_medical_conditions;
            } else {
                this.form_error(res);
            }
        },
        selectImagingResult(event) {
            this.formData.imaging_results = event.target.files[0];
        },
        async completePatientsInfo() {
            const data = new FormData();
            data.append("imaging_results", this.formData.imaging_results);
            const mergedObject = Object.assign(
                {},
                this.formData,
                this.formVitalSigns,
                {
                    customer_id: this.edit_data.id,
                }
            );
            const json = JSON.stringify(mergedObject);
            data.append("data", json);
            this.btn_loading = true;
            const res = await this.callApi("post", "data/medical/create", data);
            this.btn_loading = false;
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");
                this.$emit("dismiss-diag");
            } else {
                this.form_error(res);
            }
        },
        async submitRecords(data) {},
    },
};
</script>
