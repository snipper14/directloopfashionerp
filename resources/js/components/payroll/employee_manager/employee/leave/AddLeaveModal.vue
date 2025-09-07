<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header">Leave Allocation</div>

                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6 nopadding">
                                    <label for="">Search Employee</label>
                                    <treeselect
                                        :load-options="fetchEmployee"
                                        :options="employee_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.employee_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select  Employee "
                                    />
                                </div>
                                <div
                                    class="col-md-6 form-group d-flex flex-column"
                                >
                                    <label for="">Leave Type *</label>

                                    <Select v-model="form_data.leave_type_id">
                                        <Option
                                            v-for="item in leave_type_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.leave_type }}</Option
                                        >
                                    </Select>
                                </div>

                                <div
                                    class="col-md-6 form-group date-picker-wrapper"
                                >
                                    <label for="">Start From </label>
                                    <date-picker
                                        v-model="form_data.start_from"
                                       type="datetime"
                                    ></date-picker>
                                </div>
                                <div
                                    class="col-md-6 form-group date-picker-wrapper"
                                >
                                    <label for="">End At</label>
                                    <date-picker
                                        v-model="form_data.end_at"
                                       type="datetime"
                                    ></date-picker>
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for="">Reason</label>

                                    <input
                                        type="text"
                                        placeholder="Reason"
                                        v-model="form_data.reason"
                                        class="form-control"
                                    />
                                </div>

                                <div class="col-md-10 form-group">
                                    <v-btn
                                        color="primary"
                                        :loading="btn_loading"
                                        @click="saveLeave()"
                                        small
                                        >Save</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </v-app>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

export default {
    data() {
        return {
            employee_select_data: null,
            showLoading: false,
            dept_data: null,
            leave_type_data: null,

            purchase_type_select: [
                { name: "Owned" },
                { name: "Rented" },
                { name: "Leased" },
            ],
            btn_loading: false,
            form_data: {
                employee_id: null,
                leave_type_id: null,
                start_from: null,
                end_at: null,
                reason: "",
            },
        };
    },
    components: {
        Treeselect,
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async saveLeave() {
            const res = await this.callApi(
                "POST",
                "data/leave/create",
                this.form_data
            );

            if (res.status == 200) {
                this.s("saved");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        async fetchEmployee() {
            const res = await this.getApi("data/employee/fetch", "");

            if (res.status == 200) {
                this.employee_select_data = res.data.results.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.first_name + " " + obj.last_name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchLeaveType() {
            const res = await this.getApi("data/leave_type/fetch", "");

            this.leave_type_data = res.data;
        },

        async concurrentApiReq() {
            this.showLoading ? this.showLoader() : "";
            const res = await Promise.all([this.fetchLeaveType()]);
            this.showLoading ? this.hideLoader() : "";
        },
    },
};
</script>
