<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
            <div class="card">
                <div class="card-header">
                    <h4>Add Loans</h4>
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
                                >Helb Loan</a
                            >
                            <a
                                class="nav-item nav-link"
                                id="nav-company-loan-tab"
                                data-toggle="tab"
                                href="#nav-comapny-loan"
                                role="tab"
                                aria-controls="nav-comapny-loan"
                                aria-selected="false"
                                >Company Loan</a
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
                            <form>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <treeselect
                                                v-model="form_data.employee_id"
                                                :multiple="false"
                                                :options="employee_data"
                                                :show-count="true"
                                                placeholder="Select "
                                            />
                                        </div>
                                        <div class="custom-row">
                                            <div class="form-group">
                                                <label>Loan Date *</label
                                                ><date-picker
                                                    v-model="
                                                        form_data.loan_date
                                                    "
                                                    valueType="format"
                                                ></date-picker>
                                            </div>
                                            <div class="form-group">
                                                <label>Completion Date *</label
                                                ><date-picker
                                                    v-model="
                                                        form_data.completion_date
                                                    "
                                                    valueType="format"
                                                ></date-picker>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Loan Amount *</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="form_data.loan_amount"
                                                placeholder=""
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Installment Amount *</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    form_data.installment_amount
                                                "
                                                placeholder=""
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label>Outstanding Balance *</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    form_data.outstanding_balance
                                                "
                                                placeholder=""
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Loan Description *</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="form_data.description"
                                                placeholder=""
                                            />
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    @click="submitLoans()"
                                >
                                    Save
                                </button>
                            </form>
                        </div>
                        <!-- end tab1 -->

                        <div
                            class="tab-pane fade"
                            id="nav-comapny-loan"
                            role="tabpanel"
                            aria-labelledby="nav-company-loan-tab"
                        >
                            <div class="row">
                                <div class="col-12">
                                    <form>
                                        <div class="form-group">
                                            <label for="">Employee</label>
                                            <div class="form-group">
                                                <treeselect
                                                    v-model="
                                                        company_form.employee_id
                                                    "
                                                    :multiple="false"
                                                    :options="employee_data"
                                                    :show-count="true"
                                                    placeholder="Select "
                                                />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Start Date *</label
                                            ><date-picker
                                                v-model="company_form.loan_date"
                                                valueType="format"
                                            ></date-picker>
                                        </div>

                                        <div class="form-group">
                                            <label>Loan Amount</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    company_form.loan_amount
                                                "
                                                placeholder=""
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label
                                                >Percentage Interest (For
                                                company loans)</label
                                            >
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    company_form.percent_interest
                                                "
                                                placeholder=""
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Loan Period (monthly)</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    company_form.loan_period
                                                "
                                                placeholder=""
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label>Description</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="
                                                    company_form.description
                                                "
                                                placeholder=""
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Monthly Installment</label>
                                            <input
                                                type="number"
                                                disabled
                                                class="form-control"
                                                v-model="
                                                    company_form.installment_amount
                                                "
                                                placeholder=""
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label
                                                >Pay amount (+interest)</label
                                            >
                                            <input
                                                type="number"
                                                disabled
                                                class="form-control"
                                                v-model="
                                                    company_form.total_payable_amount
                                                "
                                                placeholder=""
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Completion Date</label>
                                            <input
                                                type="text"
                                                disabled
                                                class="form-control"
                                                v-model="
                                                    company_form.completion_date
                                                "
                                                placeholder=""
                                            />
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-primary"
                                            @click="submiCoLoans()"
                                        >
                                            Save Company Loans
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import CustomSelect from "../../utilities/CustomSelect.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,

            employee_data: null,

            form_data: {
                employee_id: null,
                description: "",
                loan_date: "",
                loan_amount: "",
                installment_amount: "",
                outstanding_balance: "",
                completion_date: "",

                loan_type: "helb"
            },
            company_form: {
                loan_date: "",
                employee_id: null,
                loan_amount: 0,
                installment_amount: 0,
                percent_interest: 0,
                loan_period: 0,
                description: "",
                total_payable_amount: 0,
                completion_date: "",
                loan_type: "company_loan"
            }
        };
    },
    components: {
        CustomSelect,
        Treeselect
    },

    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        company_form: {
            handler(val) {
                const P = parseFloat(val.loan_amount);
                const R = parseFloat(val.percent_interest);
                const T = parseFloat(val.loan_period) / 12;
                const A = P * (1 + (R / 100) * T);
                this.company_form.total_payable_amount = A.toFixed(2);
                if (T > 0) {
                    this.company_form.installment_amount = (
                        A /
                        (T * 12)
                    ).toFixed(2);
                }
                const date = val.loan_date;
                if (date != "" && T != "") {
                    const startDate = this.formatDate(val.loan_date);

                    const futureMonth = this.$moment(startDate)
                        .add(parseFloat(val.loan_period) - 1, "M")
                        .format("YYYY-MM-DD");
                    this.company_form.completion_date = this.formatDate(
                        futureMonth
                    );
                }
            },
            deep: true
        }
    },
    methods: {
        employeeIdFromComponent(value) {
            this.form_data.employee_id = value;
            this.company_form.employee_id = value;
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getAllEmployees()]).then(
                function(results) {
                    return results;
                }
            );
            this.hideLoader();
        },
        async getAllEmployees() {
            const res = await this.getApi("data/employee/getAll", "");
            if (res.status == 200) {
                this.employee_data = res.data.results;
                this.employee_data = this.modifyProductKey();
               
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
        modifyProductKey() {
            let modif = this.employee_data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.first_name + "  " + obj.last_name
                };
            });
            return modif;
        },
        async submitLoans() {
            const res = await this.callApi(
                "post",
                "data/loans/create",
                this.form_data
            );

            if (res.status === 200) {
                this.s("  Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
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
        async submiCoLoans() {
            const res = await this.callApi(
                "post",
                "data/loans/createCoLoans",
                this.company_form
            );

            if (res.status === 200) {
                this.s("  Details has been added successfully!");
                Object.keys(this.company_form).forEach(
                    key => (this.company_form[key] = "")
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
        }
    }
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
