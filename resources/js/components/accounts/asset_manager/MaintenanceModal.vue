<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header">Send for Maintenance</div>

                        <div class="card-body">
                            <div class="row">
                               
                                  <div class="form-group col-md-3">
                                    <label>Status*</label>
                                    <Select v-model="form_data.type">
                                        <Option
                                            v-bind:value="data.name"
                                            v-for="(
                                                data, i
                                            ) of maintenance_status_select"
                                            :key="i"
                                        >
                                            {{ data.name }}
                                        </Option>
                                    </Select>
                                </div>
                                <div
                                    class="col-md-3 form-group d-flex flex-column"
                                >
                                    <label for="">Priority*</label>

                                    <Select v-model="form_data.priority">
                                        <Option
                                          v-bind:value="item.name"
                                            v-for="(
                                                item, i
                                            ) of priority_select_data"
                                            :key="i"
                                            >{{ item.name }}</Option
                                        >
                                    </Select>
                                </div>

                                <div
                                    class="col-md-3 form-group date-picker-wrapper"
                                >
                                    <label for="">Start Date</label>
                                    <date-picker
                                        v-model="
                                            form_data.maintenance_start_date
                                        "
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for="">Notes:</label>

                                    <input
                                        type="text"
                                        placeholder="Description"
                                        v-model="form_data.description"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-10 form-group">
                                    <v-btn
                                        color="primary"
                                        :loading="btn_loading"
                                        @click="saveMaintenance()"
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
export default {
    props:['edit_data'],
    data() {
        return {
            showLoading: false,
            dept_data: null,
            asset_cate_data: null,
            maintenance_status_select: [
                { name: "New" },
                { name: "In Progress" },
                { name: "Completed" },
                { name: "Cancelled" },
            ],
            priority_select_data: [
                { name: "Low" },
                { name: "Medium" },
                { name: "High" },
                
            ],
           
            btn_loading: false,
            form_data: {
                maintenance_start_date: null,
                type: null,
                priority: null,
                description: null,
                asset_id:null,
            },
        };
    },
    mounted() {
       this.form_data.asset_id=this.edit_data.id
    },
    methods: {
        async saveMaintenance() {
            const res = await this.callApi(
                "POST",
                "data/asset_maintenance/create",
                this.form_data
            );

            if (res.status == 200) {
                this.s("saved");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchAssetCategory() {
            const res = await this.getApi("data/assets_category/fetch", "");

            if (res.status == 200) {
                this.asset_cate_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
     
    },
};
</script>
