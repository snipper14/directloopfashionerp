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
                    <h4>Add Deductions</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label> Deduction Type*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.deduction_type"
                                        placeholder="Deduction Type"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Employee</label>

                                    <input
                                        type="text"
                                        v-model="employee_name"
                                        disabled
                                        class="form-control"
                                    />
                                </div>

                                <div></div>
                                <div class="form-group">
                                    <label> Monthly Deduction *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.monthy_deduction"
                                        placeholder=""
                                    />
                                </div>

                                <div class="form-group">
                                    <label> No of Months *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.no_of_months"
                                        placeholder=""
                                    />
                                </div>

                                <div class="form-group">
                                    <label> Deduction Total*</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.deduction_total"
                                        placeholder=""
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Effective Date *</label
                                    ><date-picker
                                        v-model="form_data.effective_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>

                                <div class="form-group">
                                    <label>End Date *</label
                                    ><date-picker
                                        v-model="form_data.end_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="submitDeduction()"
                        >
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    props: ["deduction_data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            employee_name: "",
            form_data: {
                deduction_type: "Salary Sales Deduction",
                employee_id: null,
                monthy_deduction: 0,
                deduction_total: 0,
                effective_date: "",
                end_date: "",
                no_of_months: 1
            }
        };
    },
    components: {},
    mounted() {
        this.employee_name =
            this.deduction_data.employee.first_name +
            " " +
            this.deduction_data.employee.last_name;
        this.form_data.employee_id = this.deduction_data.employee_id;
        this.form_data.deduction_total = this.deduction_data.total_amount;
    },
    watch: {
        form_data: {
            handler(val) {
                const date = val.effective_date;
                const T = parseFloat(val.no_of_months);
                //  this.form_data.deduction_total=parseFloat(val.monthy_deduction)*T
                this.form_data.monthy_deduction =
                    this.form_data.deduction_total / T;
                if (date != "" && T != "") {
                    const startDate = this.formatDate(date);

                    const futureMonth = this.$moment(startDate)
                        .add(parseFloat(val.no_of_months) - 1, "M")
                        .format("YYYY-MM-DD");
                    this.form_data.end_date = this.formatDate(futureMonth);
                }
            },
            deep: true
        }
    },
    methods: {
        modifyProductKey() {
            let modif = this.employee_data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.first_name + "  " + obj.last_name
                };
            });
            return modif;
        },
        employeeIdFromComponent(value) {
            this.form_data.employee_id = value;
        },

        async submitDeduction() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/deductions/create",
                this.form_data
            );

            if (res.status === 200) {
                const update_res = await this.callApi(
                    "post",
                    "data/pos_sale/update_salary_deduction",
                    {
                        order_no: this.deduction_data.order_no
                    }
                );
                this.hideLoader();
                if (update_res.status === 200) {
                    this.successNotif("  Details has been added successfully!");
                    Object.keys(this.form_data).forEach(
                        key => (this.form_data[key] = "")
                    );
                    this.$emit("dashboard-active");
                } else {
                    this.servo();
                }
            } else {
                this.hideLoader();
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
