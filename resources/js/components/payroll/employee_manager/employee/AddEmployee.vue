<template>
    <div>
        <v-btn
            class="ma-2 v-btn-primary"
            @click="$emit('dashboard-active')"
            color="primary"
            dark
        >
            <v-icon dark left> mdi-arrow-left </v-icon>
            Back
        </v-btn>
        <div class="card">
            <div class="card-header">
                <h5>Add Employee</h5>
            </div>

            <div class="card-body">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a
                            class="nav-item nav-link active"
                            id="nav-home-tab"
                            data-toggle="tab"
                            href="#nav-home"
                            role="tab"
                            aria-controls="nav-home"
                            aria-selected="true"
                            >Personal Details</a
                        >
                        <a
                            class="nav-item nav-link"
                            id="nav-profile-tab"
                            data-toggle="tab"
                            href="#nav-profile"
                            role="tab"
                            aria-controls="nav-profile"
                            aria-selected="false"
                            >Employment Details</a
                        >
                        <a
                            class="nav-item nav-link"
                            id="nav-contact-tab"
                            data-toggle="tab"
                            href="#nav-contact"
                            role="tab"
                            aria-controls="nav-contact"
                            aria-selected="false"
                            >Salary Info</a
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
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="first_name"> First Name*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.first_name"
                                        placeholder="First Name"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.last_name"
                                        placeholder="Last Name"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone *</label>
                                    <input
                                        type="phone"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.phone"
                                        placeholder="Phone"
                                    />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.email"
                                        placeholder="Email"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="email">DOB *</label>
                                    <date-picker
                                        v-model="form_data.dob"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label for="ID">ID NO *</label>
                                    <input
                                        type="ID"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.id_no"
                                        placeholder="ID NO."
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="other">Other Details *</label>
                                    <input
                                        type="other"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.other"
                                        placeholder="Other Details"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="nav-profile"
                        role="tabpanel"
                        aria-labelledby="nav-profile-tab"
                    >
                        <!-- category B -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Location*</label>
                                    <select
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.department_id"
                                        placeholder="---Select Location"
                                    >
                                        <option
                                            v-for="(
                                                data, i
                                            ) in select_data.dept_data"
                                            :key="i"
                                            v-bind:value="data.id"
                                        >
                                            {{ data.department }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Designation*</label>
                                    <select
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.employee_type_id"
                                    >
                                        <option
                                            v-for="(
                                                data, i
                                            ) in select_data.employee_type_data"
                                            :key="i"
                                            v-bind:value="data.id"
                                        >
                                            {{ data.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Payment Type *</label>
                                    <select
                                        v-model="form_data.payment_type"
                                        class="form-control"
                                    >
                                        <option
                                            v-bind:value="data.value"
                                            v-for="(
                                                data, i
                                            ) of payment_type_select"
                                            :key="i"
                                        >
                                            {{ data.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="other">KRA PIN *</label>
                                    <input
                                        type="other"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.kra_pin"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="other">JOB NO *</label>
                                    <input
                                        type="other"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.job_no"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="email"
                                        >Contract Start Date *</label
                                    >
                                    <date-picker
                                        v-model="form_data.start_contract"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>
                        </div>
                        <!-- END CATEGORY B -->
                    </div>
                    <div
                        class="tab-pane fade"
                        id="nav-contact"
                        role="tabpanel"
                        aria-labelledby="nav-contact-tab"
                    >
                        <!-- CAEGORY C -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Salary *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.salary"
                                        placeholder="Basic Salary"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="phone">Hrly Rate *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.hrly_rate"
                                        placeholder="Rate/hr"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="phone">Deduct NSSF *</label>
                                    <input
                                        type="checkbox"
                                        v-model="form_data.deduct_nssf"
                                    />
                                    <label for="checkbox">{{
                                        form_data.deduct_nssf
                                    }}</label>

                                    <label for="" class="ml-4"
                                        >Deduct SHIF *</label
                                    >
                                    <input
                                        type="checkbox"
                                        v-model="form_data.deduct_nhif"
                                    />
                                    <label for="checkbox">{{
                                        form_data.deduct_nhif
                                    }}</label>

                                    <label for="" class="ml-4"
                                        >Deduct PAYE *</label
                                    >
                                    <input
                                        type="checkbox"
                                        v-model="form_data.deduct_paye"
                                    />
                                    <label for="checkbox">{{
                                        form_data.deduct_paye
                                    }}</label>
                                     <label for="" class="ml-4"
                                        >Deduct HousingLevy *</label
                                    >
                                     <input
                                        type="checkbox"
                                        v-model="form_data.deduct_housinglevy"
                                    />
                                    <label for="checkbox">{{
                                        form_data.deduct_housinglevy
                                    }}</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="payment_info">
                                    <div class="form-group">
                                        <label>Payment Type *</label>
                                        <select
                                            @change="onPayMethodChange($event)"
                                            v-model="form_data.pay_method"
                                            class="form-control"
                                        >
                                            <option
                                                v-bind:value="data.value"
                                                v-for="(
                                                    data, i
                                                ) of payment_method_select"
                                                :key="i"
                                            >
                                                {{ data.name }}
                                            </option>
                                        </select>
                                        <div v-if="pay_method.bank_transfer">
                                            <div class="form-group">
                                                <label for="phone"
                                                    >Account Name *</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    autocomplete="new-user-street-address"
                                                    v-model="
                                                        form_data.account_name
                                                    "
                                                    placeholder=""
                                                />
                                            </div>
                                            <div class="form-group">
                                                <label for="phone"
                                                    >Bank Account No. *</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    autocomplete="new-user-street-address"
                                                    v-model="
                                                        form_data.account_no
                                                    "
                                                    placeholder=""
                                                />
                                            </div>

                                            <div class="form-group">
                                                <label for="phone"
                                                    >Bank Name. *</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    autocomplete="new-user-street-address"
                                                    v-model="
                                                        form_data.bank_name
                                                    "
                                                    placeholder=""
                                                />
                                            </div>

                                            <div class="form-group">
                                                <label for="phone"
                                                    >Bank Branch *</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    autocomplete="new-user-street-address"
                                                    v-model="
                                                        form_data.bank_branch
                                                    "
                                                    placeholder=""
                                                />
                                            </div>
                                        </div>
                                        <div v-if="pay_method.mobile_money">
                                            <div class="form-group">
                                                <label for="phone"
                                                    >Phone No *</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    autocomplete="new-user-street-address"
                                                    v-model="
                                                        form_data.payment_phone
                                                    "
                                                    placeholder=""
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- END CATEGORY C -->
                    </div>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    @click="submitEmpType()"
                >
                    Save Employee Details
                </button>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            select_data: {
                dept_data: "",
                employee_type_data: "",
            },
            payment_type_select: [
                { name: "Piece Work", value: "piece_work" },
                { name: "Monthly", value: "monthly" },
                { name: "Casual", value: "casual" },
            ],
            payment_method_select: [
                { name: "Bank Transfer", value: "bank_transfer" },
                { name: "Cash", value: "cash" },
                { name: "Mobile Money", value: "mobile_money" },
                { name: "Cheque", value: "cheque" },
            ],
            pay_method: {
                bank_transfer: true,
                cash: false,
                mobile_money: false,
                cheque: false,
            },
            form_data: {
                first_name: "",
                last_name: "",
                dob: "",
                other: "",
                phone: "",
                email: "",
                id_no: "",
                department_id: "",
                employee_type_id: "",
                salary: 0,
                hrly_rate: 0,
                payment_type: "",
                kra_pin: "",
                job_no: "",
                start_contract: "",
                deduct_nssf: true,
                deduct_nhif: true,
                deduct_paye: true,
                deduct_housinglevy:true,
                pay_method: "bank_transfer",
                bank_branch: "",
                bank_name: "",
                account_name: "",
                account_no: "",
                payment_phone: "",
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        onPayMethodChange(event) {
            const type = event.target.value;
            Object.keys(this.pay_method).forEach((key) => {
                this.pay_method[key] = false;

                if (key == type) {
                    this.pay_method[key] = true;
                }
            });
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getDept(),
                this.getEmplType(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();

            this.select_data.dept_data = res[0].data;
            this.select_data.employee_type_data = res[1].data.results;
        },

        async getDept() {
            return await this.getApi("data/dept/fetchAll", "");
        },

        async getEmplType() {
            return await this.getApi("data/employee_type/fetch", "");
        },

        async submitEmpType() {
            const res = await this.callApi(
                "post",
                "data/employee/create",
                this.form_data
            );

            if (res.status === 200) {
                this.s(" Employee has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active");
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
    },
};
</script>
<style scoped>
.nav-tabs {
    color: #000 !important;
    font-weight: bold !important;
}
label {
    color: #000 !important;
}
.payment_info {
    border: 1px solid #000;
    padding: 2rem;
    margin: 2rem;
    border-radius: 4px;
}
</style>
