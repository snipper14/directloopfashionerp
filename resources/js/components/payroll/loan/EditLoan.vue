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
                    <h4>Update Loans</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Employee *</label>
                                    <select
                                        type="text"
                                        class="form-control"
                                        id="first_name"
                                        disabled
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
                                <div class=custom-row>
                                <div class="form-group">
                                    <label>Loan Date *</label
                                    ><date-picker
                                        v-model="form_data.loan_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label>Completion Date *</label
                                    ><date-picker
                                        v-model="form_data.completion_date"
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
                                        v-model="form_data.installment_amount"
                                        placeholder=""
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Outstanding Balance *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.outstanding_balance"
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
                                 <div class="form-group">
                                    <label>Percentage Interest (For company loans)</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.percent_interest"
                                        placeholder=""
                                    />
                                </div>
                            
                            </div>
                        </div>

                        <button
                         v-if="isUpdatePermitted"
                            type="button"
                            class="btn btn-primary"
                            @click="editLoans()"
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
                employee_id: "",
                description: "",
                loan_date: "",
                loan_amount: "",
                installment_amount: "",
                outstanding_balance: "",
                completion_date: "",
                     percent_interest:0,
             
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
            const res = await Promise.all([this.getAllEmployees()]).then(
                function(results) {
                    return results;
                }
            );
            this.hideLoader();
            this.select_data.employee_data = res[0].data.results;
        },
        async getAllEmployees() {
            return await this.getApi("data/employee/getAll", "");
        },

        async editLoans() {
            const res = await this.callApi(
                "put",
                "data/loans/update",
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
