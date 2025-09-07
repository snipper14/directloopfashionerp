<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Sickoff for <b> {{ form_data.patient_name }}</b>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="">Patients Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.patient_name"
                                />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">Patients Age</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.age"
                                />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">Gender</label>

                                <select
                                    class="form-control"
                                    v-model="form_data.gender"
                                >
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="">Price</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.cost"
                                />
                            </div>
                            <div class="d-flex flex-column col-md-3">
                                <label> Date *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="form_data.currentdate"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column col-md-3">
                                <label> Start Date *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="form_data.startdate"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column col-md-3">
                                <label> End Date *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="form_data.enddate"
                                ></date-picker>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label for="">No of Sick Days</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.sickoff_days"
                                />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">Doctors Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.doctors_name"
                                />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Doctors Report</label>
                                <textarea
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.doctors_report"
                                />
                            </div>
                        </div>
                        <Button
                            title="write"
                            type="primary"
                            :loading="isSending"
                            :disabled="isSending"
                            v-if="isWritePermitted"
                            @click="saveInfo()"
                            >SAVE</Button
                        >
                    </div>
                </div>
            </div>
        </div>
        <PrintSickoff
            v-if="show_print"
            ref="PrintSickoff"
            :print_data="print_data"
            :branch_info="branch_info"
            :logo="logo"
            :user="user"
        />
    </div>
</template>

<script>
import PrintSickoff from "./PrintSickoff.vue";
export default {
    components: { PrintSickoff },
    props: ["edit_data"],
    data() {
        return {
            isSending: false,
            logo: null,
            show_print: false,
            print_data: {},
            branch_info: null,
            user: null,
            form_data: {
                customer_id: null,
                age: "",
                reg_no: "",
                gender: null, //['male', 'female'],
                sickoff_days: 0,
                startdate: null,
                enddate: null,
                doctors_name: "",
                doctors_report: "",
                cost: 0,
                patient_name: "",
                currentdate: null,
            },
        };
    },
    mounted() {
        this.branch_info = this.$store.state.branch;
        this.logo = this.$store.state.branch.img_url;
        this.user = this.$store.state.user;
        this.form_data.currentdate = this.getCurrentDate();
        this.form_data.customer_id = this.edit_data.id;
        this.form_data.patient_name = this.edit_data.company_name;
        this.form_data.doctors_name = this.$store.state.user.name;
    },
    methods: {
        async saveInfo() {
            this.isSending = true;
            const res = await this.callApi(
                "POST",
                "data/sickoff/create",
                this.form_data
            );
            this.isSending = false;
            if (res.status == 200) {
                this.print_data = res.data;

                this.show_print = true;
                setTimeout(() => {
                    this.$refs.PrintSickoff.printBill();
                    this.$emit("dismiss-diag");
                    this.show_print = false;
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
