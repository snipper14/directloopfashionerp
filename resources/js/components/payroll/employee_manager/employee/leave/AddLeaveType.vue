<template>
    <div>
        <v-btn
            class="ma-2 v-btn-primary"
            @click="$emit('dashboard-active')"
            color="primary"
            dark
        >
            <v-icon dark left> mdi-arrow-left </v-icon>
            Back
        </v-btn>
        <div class="card">
            <div class="card-header">
                <h5>Add Leave Type</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="">Leave Type</label>
                        <input
                            type="text"
                            v-model="form_data.leave_type"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="">Leave Count</label>
                        <input
                            type="text"
                            v-model="form_data.leave_count"
                            class="form-control"
                        />
                    </div>
                    <div class="form-group col-md-3 nopadding">
                        <label>Interval*</label>
                        <Select v-model="form_data.interval">
                            <Option
                                v-bind:value="data.name"
                                v-for="(data, i) of leave_period"
                                :key="i"
                            >
                                {{ data.name }}
                            </Option>
                        </Select>
                    </div>
                    <v-btn color="primary" small @click="submitEmpType()"
                        >Save</v-btn
                    >
                </div>
                <div class="row">
                    <table class="table table-sm table-striped table-bordered">
                        <tbody>
                            <tr v-for="(data, i) in type_data" :key="i">
                                <td>{{ data.leave_type }}</td>
                                <td>{{ data.leave_count }}</td>
                                <td>{{ data.interval }}</td>
                                <td><v-btn color="danger" x-small v-if="isDeletePermitted" @click="deleteRecord(data.id,i)"> Delete</v-btn></td>
                            </tr>
                        </tbody>
                    </table>
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
            type_data: [],
            leave_period: [
                { name: "Random" },
                { name: "Daily" },
                { name: "Monthly" },
                { name: "Yearly" },
            ],

            form_data: {
                leave_type: "",
                leave_count: 0,
                interval: "",
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            await Promise.all([this.getType()]);
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/leave_type/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.type_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getType() {
            const res = await this.getApi("data/leave_type/fetch", "");

            this.type_data = res.data;
        },

        async submitEmpType() {
            const res = await this.callApi(
                "post",
                "data/leave_type/create",
                this.form_data
            );

            if (res.status === 200) {
                this.s(" added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
<style scoped>
.nav-tabs {
    color: #000 !important;
    font-weight: bold !important;
}
label {
    color: #000 !important;
}
.payment_info {
    border: 1px solid #000;
    padding: 2rem;
    margin: 2rem;
    border-radius: 4px;
}
</style>
