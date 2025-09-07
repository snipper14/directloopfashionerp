<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Add Lab Test Results for
                        <b> {{ form_data.patient_name }}</b>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="">Patient Name</label>
                                <input
                                    type="text"
                                    v-model="form_data.patient_name"
                                    class="form-control"
                                />
                            </div>
                            <div
                                v-for="(value, key) in formVitalSigns"
                                :key="key"
                                class="col-md-6 form-group"
                            >
                                <label :for="key">{{
                                    key.replace(/_/g, " ").toUpperCase()
                                }}</label>

                                <input
                                    v-model="formVitalSigns[key]"
                                    type="text"
                                    class="form-control"
                                    :id="key"
                                />
                            </div>
                            <div class="form-group col-md-12">
                                <label for="">Lab Tech Name</label>
                                <input
                                    v-model="form_data.lab_tech"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Blood tests</label>
                                <textarea
                                    rows="5"
                                    v-model="form_data.blood_test"
                                    class="form-control"
                                ></textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Other Test Results</label>
                                <textarea
                                    rows="5"
                                    v-model="form_data.other_test_results"
                                    class="form-control"
                                ></textarea>
                            </div>
                            <div class="form-group col-md-5">
                                <label for="">Imaging results Upload</label>
                                <input
                                    type="file"
                                    class="form-control"
                                    v-on:change="selectImagingResult"
                                />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <h3>Search Products</h3>
                            <div class="col-md-12">
                                <ProductSearchLabComponents
                                    :edit_data="edit_data"
                                 :form_data="form_data"
                                   
                                />
                            </div>
                        </div>
                        <div class="center_button">
                            <Button
                                type="primary"
                                :loading="isSending"
                                :disabled="isSending"
                                v-if="isWritePermitted"
                                @click="saveLabresult"
                                >Save Lab Results</Button
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ProductSearchLabComponents from './ProductSearchLabComponents.vue';
export default {
  components: { ProductSearchLabComponents },
    props: ["edit_data"],
    data() {
        return {
            isSending: false,
            form_data: {
                customer_id: null,
                patient_name: "",
                blood_test: "",
                imaging_results: null,
                other_test_results: "",
                lab_tech: "",
            },
            formVitalSigns: {
                blood_pressure: "",
                heart_rate: "",
                respiratory_rate: "",
                temperature: "",
                height_and_weight: "",
            },
        };
    },
    mounted() {
        this.branch_info = this.$store.state.branch;
        this.logo = this.$store.state.branch.img_url;
        this.user = this.$store.state.user;
        this.form_data.patient_name = this.edit_data.company_name;
        this.form_data.customer_id = this.edit_data.id;
        this.form_data.lab_tech = this.user.name;
        console.log("Component mounted." + JSON.stringify(this.edit_data));
    },
    methods: {
        async saveLabresult() {
            const data = new FormData();
            data.append("imaging_results", this.form_data.imaging_results);
            const mergedObject = Object.assign(
                {},
                this.form_data,
                this.formVitalSigns
            );
            const json = JSON.stringify(mergedObject);
            data.append("data", json);
            this.btn_loading = true;
            const res = await this.callApi(
                "post",
                "data/lab/create",
                data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");
                this.$emit("dismiss-diag");
            } else {
                this.form_error(res);
            }
        },
        selectImagingResult(event) {
            this.form_data.imaging_results = event.target.files[0];
        },
    },
};
</script>
