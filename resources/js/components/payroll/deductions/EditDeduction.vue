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
                    <h4>Update Deductions</h4>
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
                                        v-model="form_data.deduction_type"
                                        placeholder="Deduction Type"
                                    />
                                </div>
                                <div class="form-group">
                                    <label> Employee *</label>
                                    <input
                                        disabled
                                        type="text"
                                        class="form-control"
                                        v-model="fullName"
                                        placeholder="Deduction Type"
                                    />
                                </div>
                                <div></div>
                                <div class="form-group">
                                    <label> Monthly Deduction *</label>
                                    <input
                                        type="number"
                                        class="form-control"
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
                        v-if="isUpdatePermitted"
                            type="button"
                            class="btn btn-primary"
                            @click="updateDeduction()"
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
    props: ["data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            fullName: "",
            form_data: {
                id: "",
                deduction_type: "",
                employee_id: "",
                monthy_deduction: 0,
                deduction_total: 0,
                effective_date: "",
                 end_date:'',
                 no_of_months:1
            }
        };
    },

    mounted() {
       
        this.fullName =
            this.data.employee.first_name + " " + this.data.employee.last_name;
        this.form_data = this.data;
    },
     watch: {
        form_data: {
            handler(val) {
               
            const date = val.effective_date;
            const T=parseFloat(val.no_of_months)
           this.form_data.deduction_total=parseFloat(val.monthy_deduction)*T
           if(date!="" && T!='') {
                  var arr1 = date.split("-");
               var d = new Date( arr1[0],  arr1[1]-1,  arr1[2]);
                d.setMonth(d.getMonth() + T);
                  this.form_data.end_date=this.formatDate(d)
           }
            },
            deep: true
        }
    },
    methods: {
        async updateDeduction() {
            const res = await this.callApi(
                "put",
                "data/deductions/update",
                this.form_data
            );

            if (res.status === 200) {
                this.s("  Details has been updated successfully!");
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
        }
    }
};
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
