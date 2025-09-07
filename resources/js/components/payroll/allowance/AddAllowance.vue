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
                    <h4>Add Allowance</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Employee</label>
                                    <div class="form-group">
                                        <treeselect
                                            v-model="form_data.employee_id"
                                            :multiple="false"
                                            :options="employee_data"
                                            :show-count="true"
                                            placeholder="Select "
                                        />
                                    </div>
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
                                <div class="form-group">
                                    <label> Allowance Type*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.allowance_type"
                                        placeholder=""
                                    />
                                </div>
                            </div>
                        </div>

                        <button
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
                amount: "",
                allowance_type: ""
            }
        };
    },
    components: {
        Treeselect
    },
    mounted() {
        this.getAllEmployees()
    },
    methods: {
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
        async submitAllowance() {
            const res = await this.callApi(
                "post",
                "data/allowance/create",
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
