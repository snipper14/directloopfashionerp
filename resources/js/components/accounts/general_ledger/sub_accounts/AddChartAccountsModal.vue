<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
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
                <div class="card-header"></div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">Add Chart of Accounts </legend>
                        <form>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Category Name(Unique) *</label>
                                    <input
                                    placeholder="e.g Assets, Liabilities"
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.name"
                                    />
                                </div>
                                <div class="form-group col-md-12">
                                    <label> Description </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.description"
                                    />
                                </div>
                                 <div class="form-group col-md-12 ">
                                <label>Account Type*</label>
                                <Select v-model="form_data.type">
                                    <Option
                                        v-bind:value="data.name"
                                        v-for="(
                                            data, i
                                        ) of acc_type_select"
                                        :key="i"
                                    >
                                        {{ data.name }}
                                    </Option>
                                </Select>
                            </div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="submitAccount()"
                            >
                                Save Record
                            </button>
                        </form>
                    </fieldset>
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
            form_data: {
                account: "",
                description: "",
                type:null
            },
             acc_type_select: [
                { name: "Debit " },
                { name: "Credit" },
             
            ],
        };
    },
    mounted() {},
    methods: {
        async submitAccount() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/chart_accounts/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
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
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
</style>
