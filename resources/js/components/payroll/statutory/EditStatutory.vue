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
                    <h4>Add Statutory</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Statutory Type</label>
                                    <select
                                        class="form-control"
                                        :required="true"
                                        v-model="form_data.statutory_type"
                                    >
                                        <option :selected="true"
                                            >Choose </option
                                        >
                                        <option
                                            v-for="(option,
                                            i) in statutory_options"
                                            :key="i"
                                            v-bind:value="option"
                                            >{{ option }}</option
                                        >
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label> Salary From *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.salary_from"
                                        placeholder=""
                                    />
                                </div>

                                <div class="form-group">
                                    <label> Salary To *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.salary_to"
                                        placeholder=""
                                    />
                                </div>

                                <div class="form-group">
                                    <label> Amount Deducted*</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.amount_deducted"
                                        placeholder=""
                                    />
                                </div>
                                <div class="form-group">
                                    <label> Percentage Deduction*</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.percentage_deduction"
                                        placeholder=""
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                        v-if="isUpdatePermitted"
                            type="button"
                            class="btn btn-primary"
                            @click="submitDetails()"
                        >
                            Save Info
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
    props:['data'],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            statutory_options: ["PAYE", "NSSF", "NHIF",'HOUSINGLEVY'],
            form_data: {
                statutory_type: "",
                salary_from: 0,
                salary_to: 0,
                amount_deducted: 0,
                percentage_deduction: 0
            }
        };
    },

    mounted() {
        this.form_data=this.data
    },
    methods: {
        async submitDetails() {
            if(this.form_data.statutory_type=="PAYE" &&  this.form_data.percentage_deduction==0)
            {
                this.e("PAYE required percentage deductions")
                return
            }
            if(parseFloat(this.form_data.salary_from)>parseFloat(this.form_data.salary_to)){
                  this.e("Incorrect salary range")
                return
            }
              
             
            
            const res = await this.callApi(
                "put",
                "data/statutory/update",
                this.form_data
            );

            if (res.status === 200) {
                this.s("Details has been updated successfully!");
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
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
