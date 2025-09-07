<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Add Etims Stock Movement for/</div>

                        <div class="card-body">
                            <div class="row">
                                <h3>
                                    <b>{{ details_data.name }}</b>
                                </h3>
                            </div>
                            <div class="row">
                                <div
                                    class="col-md-6 form-group d-flex flex-column"
                                >
                                    <label for=""> Etims Code</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="form_data.etims_code"
                                        class="form-control"
                                    />
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for=""> Qty</label>
                                    <input
                                        type="number"
                                        v-model="form_data.qty"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <v-btn
                                type="submit"
                                @click="handleSubmit"
                                :loading="is_loading"
                                color="primary"
                            >
                                Submit
                            </v-btn>
                        </div>
                    </div>
                </div>
            </div></v-app
        >
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    components: {
        Treeselect,
    },
    props: ["details_data"],
    data() {
        return {
            is_loading: false,

            form_data: {
                etims_code: this.details_data.etims_code,
                id: this.details_data.id,
                qty: 0,
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.concurrentApiReq();
    },
    watch: {
        form_data: {
            handler() {
                console.log(JSON.stringify(this.form_data));
            },
            deep: true,
        },
    },
    methods: {
        async concurrentApiReq() {
            const res = await Promise.all([]).then(function (results) {
                return results;
            });
        },
        async handleSubmit() {
            this.is_loading = true;

            const res = await this.callApi(
                "POST",
                "data/pgm_stock/stockMovt",
                this.form_data
            );
            this.is_loading = false;
            if (res.status === 200) {
                this.s("Updated to etims successfully");
                this.$emit("dashboard-active",{});
            } else {
                this.form_error(res);
            }
        },
        isRequiredField(key) {
            // You can customize this method to determine which fields are required
            // For example, you can have an array of required field names and check against it
            const requiredFields = ["item_class_code", "item_name"];
            return requiredFields.includes(key);
        },
    },
};
</script>
