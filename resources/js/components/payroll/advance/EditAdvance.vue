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
                    <h4>Edit Advance</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                  <div class="form-group">
                                    <label> Date *</label
                                    ><date-picker
                                        v-model="form_data.payment_month "
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label>Employee *</label>
                                       <input
                                        type="text"
                                        disabled
                                        class="form-control"
                                        v-model="full_name"
                                        placeholder=""
                                    />
                                   
                                                                 </div>
                                <div></div>
                                <div class="form-group">
                                    <label> Amount *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.amount"
                                        placeholder=""
                                    />
                                </div>
                                
                            </div>
                        </div>

                        <button
                        v-if="isUpdatePermitted && data.isDeducted==0"
                            type="button"
                            class="btn btn-primary"
                            @click="submitAllowance()"
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
    props:['data'],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,

            form_data: {
                id:'',
                employee_id: "",
                amount: "",
                allowance_type: ""
            },
            full_name:'',
        };
    },
   
    mounted() {
        this.form_data=this.data
         this.full_name=this.data.employee.first_name+ ' ' +this.data.employee.last_name
    },
    methods: {
      

        async submitAllowance() {
            const res = await this.callApi(
                "put",
                "data/advance/update",
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
