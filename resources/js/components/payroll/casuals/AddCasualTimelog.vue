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
               <h4>Add Time Logs</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label> Date *</label
                                    ><date-picker
                                        v-model="form_data.date_recorded"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label for="">Employee</label>
                                    <div class="form-group">
                                        <treeselect
                                            v-model="employee_id"
                                            :multiple="false"
                                            :options="employee_data"
                                            :show-count="true"
                                            placeholder="Select "
                                        />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>No Of HRS *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.no_hrs"
                                        placeholder="HRS"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Time In *</label>
                                    <date-picker
                                        v-model="form_data.time_in"
                                        type="datetime"
                                    ></date-picker>
                                    <label>Time Out *</label>
                                    <date-picker
                                        v-model="form_data.time_out"
                                        type="datetime"
                                    ></date-picker>
                                </div>
   <div class="form-group">
                                    <label>Hrly Rate *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.hrly_rate"
                                        placeholder=""
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Total Amount *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.amount"
                                        placeholder="Amount"
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="submitLogs()"
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
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    props: ["employee_data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            employee_id:null,
            employee_select_value: "",
            // format: 'dd-MMM-yyyy',
            form_data: {
                date_recorded: "",
                employee_id: null,
                no_hrs: 0,
                hrly_rate:0,
                time_in: null,
                time_out: null,
                amount: 0
            }
        };
    },
    components: {
        Treeselect
    },
    mounted() {
        this.form_data.date_recorded=this.getCurrentDate()
    },
   watch:{
       employee_id:{
           handler(val){
               
               this.form_data.employee_id=val
               this.getById(val)
           }
  
       },
          form_data: {
            handler(val) {
            
              const hrly_rate=parseFloat(val.hrly_rate)
              const no_hrs=parseFloat(val.no_hrs)
              this.form_data.amount=no_hrs*hrly_rate

            },
            deep: true
        },

   },
    methods: {
        async getById(id){
            this.showLoader()
             const res= await this.getApi("data/employee/getById/"+id, "");
             this.hideLoader()
             if(res.status==200){
             this.form_data.hrly_rate=res.data.hrly_rate
             }else{
                 this.swr('Cant get hrly salary rate')
             }
           
        },
        async getAllEmployees() {
            return await this.getApi("data/employee/getAll", "");
        },
        employeeIdFromComponent(value) {
            this.form_data.employee_id = value;
        },

        async submitLogs() {
            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/casuals_timelogs/create",
                this.form_data
            );
            this.hideLoader();
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
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
