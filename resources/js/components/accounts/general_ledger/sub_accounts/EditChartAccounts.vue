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
                                        v-bind:value="data.type"
                                        v-for="(
                                            data, i
                                        ) of acc_type_select"
                                        :key="i"
                                    >
                                        {{ data.type }}
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
    props:['data'],
    data() {
        return {
            form_data: {
                id:null,
                account: "",
                description: "",
                type:null
            },
             acc_type_select: [
                { type: "debit" },
                { type: "credit" },
             
            ],
        };
    },
    mounted() {this.form_data=this.data
    console.log(JSON.stringify(this.form_data));},
    methods: {
        async submitAccount() {
            this.showLoader();
            const res = await this.callApi(
                "put",
                "data/chart_accounts/update",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been updated successfully!");
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
