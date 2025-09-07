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
                    <h4>Update Products</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Product Name *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.name"
                                        placeholder="Product Name"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Size *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.size"
                                        placeholder="Size"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Pay Rate *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.pay_rate"
                                        placeholder="Pay Rate"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Description *</label>
                                    <textarea
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.description"
                                        placeholder="Description"
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>

                        <button
                         v-if="isUpdatePermitted"
                            type="button"
                            class="btn btn-primary"
                            @click="submitEmpType()"
                        >
                            Update Records
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
                dept_data: "",
                employee_type_data: ""
            },
            form_data: {
                name: "",
                description: "",
                size: "",
                pay_rate: ""
            }
        };
    },
    mounted() {
        this.form_data=this.data
    },
    methods: {
        async submitEmpType() {
            const res = await this.callApi(
                "put",
                "data/products/update",
                this.form_data
            );

            if (res.status === 200) {
                this.s(" Products Details has been updated successfully!");
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
