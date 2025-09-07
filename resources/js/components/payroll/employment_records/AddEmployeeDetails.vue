<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
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
                    <h4>Add Employee Details</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="first_name"> Employee*</label>
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
                                    <label>Department*</label>
                                    <select
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.department_id"
                                        placeholder="---Select Dept"
                                    >
                                        <option
                                            v-for="(data,
                                            i) in select_data.dept_data"
                                            :key="i"
                                            v-bind:value="data.id"
                                            >{{ data.department }}</option
                                        >
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Employee Type*</label>
                                    <select
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.employee_type_id"
                                    >
                                        <option
                                            v-for="(data,
                                            i) in select_data.employee_type_data"
                                            :key="i"
                                            v-bind:value="data.id"
                                            >{{ data.name }}</option
                                        >
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Salary *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.salary"
                                        placeholder="Basic Salary"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>HSE Allowance *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.house_allowance"
                                        placeholder="HSE Allowance"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>NHIF *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.nhif"
                                        placeholder="NHIF"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label>NSSF *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.nssf"
                                        placeholder="NSSF"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>PAYE *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.paye"
                                        placeholder="PAYE"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>OTHER DEDCUTIONS *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.other_deductions"
                                        placeholder="OTHER DEDUCTIONS"
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="submitEmpType()"
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
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            select_data: {
                employee_data: "",
                dept_data: "",
                employee_type_data: ""
            },
            form_data: {
                employee_id: null,
                department_id: null,
                employee_type_id: null,
                salary: 0,
                house_allowance: 0,
                nhif: 0,
                nssf: 0,
                paye: 0,
                other_deductions: 0
            }
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getAllEmployees(),
                this.getDept(),
                this.getEmplType()
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
            this.select_data.employee_data = res[0].data.results;
            this.select_data.dept_data = res[1].data.results;
            this.select_data.employee_type_data = res[2].data.results;
        },
        async getAllEmployees() {
            return await this.getApi("data/employee/getAll", "");
        },

        async getDept() {
            return  await this.getApi("data/dept/fetch", "");
          },

        async getEmplType() {
            return await this.getApi("data/employee_type/fetch", "");
        },

        async submitEmpType() {
            const res = await this.callApi(
                "post",
                "data/employee_details/create",
                this.form_data
            );

            if (res.status === 200) {
                this.s(" Employee Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                // this.$emit("dashboard-active");
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
