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
                                        v-model="form_data.deduction_type"
                                        placeholder="Deduction Type"
                                    />
                                </div>
                                <div class="form-group">
                                    <label> Employee *</label>
                                    <custom-select
                                        @employeeIdFromComponent="
                                            employeeIdFromComponent
                                        "
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
                                        v-model="form_data.effective_date "
                                        valueType="format"
                                    ></date-picker>
                                </div>

                                  <div class="form-group">
                                    <label>End Date *</label
                                    ><date-picker
                                        v-model="form_data.end_date "
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
import CustomSelect from "../../utilities/CustomSelect.vue";
export default {
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,

            form_data: {
                 deduction_type:'',
                 employee_id:'',
                 monthy_deduction:0,
                 deduction_total:0,
                 effective_date:'',
                 end_date:'',
                 no_of_months:1
            }
        };
    },
    components: {
        CustomSelect
    },
    mounted() {},
    watch: {
        form_data: {
            handler(val) {
               
            const date = val.effective_date;
            const T=parseFloat(val.no_of_months)
           this.form_data.deduction_total=parseFloat(val.monthy_deduction)*T
           if(date!="" && T!='') {
                const startDate=this.formatDate(date)
             
               const futureMonth = this.$moment(startDate).add((parseFloat(val.no_of_months)-1), 'M').format('YYYY-MM-DD');
                  this.form_data.end_date=this.formatDate(futureMonth)
                 
           }
            },
            deep: true
        }
    },
    methods: {
        employeeIdFromComponent(value) {
            this.form_data.employee_id = value;
        },

        async submitDeduction() {
            const res = await this.callApi(
                "post",
                "data/deductions/create",
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
