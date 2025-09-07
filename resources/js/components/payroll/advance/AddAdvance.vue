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
                    <h4>Add Advance</h4>
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
                                    <label> Employee *</label>
                                    <custom-select
                                        @employeeIdFromComponent="
                                            employeeIdFromComponent
                                        "
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
                                <div class="form-group">
                                                <label for=""
                                                    >Payroll  Accounts</label
                                                >
                                                <treeselect
                                                    :load-options="
                                                        fetchAccounts
                                                    "
                                                    :options="
                                                        accounts_select_data
                                                    "
                                                    :auto-load-root-options="
                                                        false
                                                    "
                                                    v-model="
                                                        form_data.payroll_account_id
                                                    "
                                                    :multiple="false"
                                                    :show-count="true"
                                                    placeholder="Select   Account "
                                                />
                                            </div>
                                              <div class="form-group">
                                    <label> Payment Ref *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.ref_no"
                                        placeholder=""
                                    />
                                </div>
                               
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="submitAdvance()"
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
import CustomSelect from "../../utilities/CustomSelect.vue";
export default {
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,

            form_data: {
                employee_id: "",
                amount: "",
                payment_month: "",
                payroll_account_id:null,
                ref_no:""

            }
        };
    },
    components: {
        CustomSelect,
        Treeselect
    },
    mounted() {},
    methods: {
        employeeIdFromComponent(value) {
            this.form_data.employee_id = value;
        },

        async submitAdvance() {
            const res = await this.callApi(
                "post",
                "data/advance/create",
                this.form_data
            );

            if (res.status === 200) {
                this.s("  Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                //    this.$emit("dashboard-active");
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
