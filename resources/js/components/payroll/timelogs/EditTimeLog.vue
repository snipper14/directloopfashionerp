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
                    <h4>Edit Time Logs</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label> Date *</label
                                    ><date-picker
                                        v-model="form_data.logged_on"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label>Employee *</label>
                                    <select
                                        type="text"
                                        class="form-control"
                                        id="first_name"
                                        v-model="form_data.employee_id"
                                        placeholder="First Name"
                                    >
                                        <option
                                            v-for="(data,
                                            i) in select_data.employee_data"
                                            :key="i"
                                            v-bind:value="data.id"
                                            >{{
                                                data.first_name +
                                                    " " +
                                                    data.last_name
                                            }}</option
                                        >
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>No Of HRS *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.no_hours"
                                        placeholder="HRS"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Time In *</label>
                                   <date-picker v-model="form_data.time_in" type="datetime"></date-picker>
                                    <label>Time Out *</label>
                                   <date-picker v-model="form_data.time_out" type="datetime"></date-picker>
                                </div>

                                <div class="form-group">
                                    <label>Total Amount *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.total_amount"
                                        placeholder="Amount"
                                    />
                                </div>

                            
                            </div>
                        </div>

                        <button
                        v-if="isUpdatePermitted && data.isPaid==0"
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
export default {
    
        props:['data'],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            select_data: {
                employee_data: "",
              
            },
            form_data: {
                id:'',
              logged_on:'',
              employee_id:'',
              no_hours:'',
              time_in:'',
              time_out:'',
              total_amount:''
            }
        };
    },
    mounted() {
        this.form_data=this.data
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getAllEmployees(),
              
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
            this.select_data.employee_data = res[0].data.results;
          
        },
        async getAllEmployees() {
            return await this.getApi("data/employee/getAll", "");
        },
       
        async submitLogs() {
            const res = await this.callApi(
                "put",
                "data/time_logs/update",
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
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
