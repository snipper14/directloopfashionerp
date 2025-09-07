<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Add VSCU Etims Stock for/</div>

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
                                    <label for="">
                                        Search Item Classification</label
                                    >
                                    <treeselect
                                        width="300"
                                        :load-options="fetchItemClassCode"
                                        :options="item_classcode_select"
                                        :auto-load-root-options="false"
                                        v-model="
                                            form_data.item_classification_id
                                        "
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Search Item Classification "
                                    />
                                </div>
                                <div
                                    class="col-md-6 form-group d-flex flex-column"
                                >
                                    <label for=""> Search Item Type</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchItemType"
                                        :options="item_type_select"
                                        :auto-load-root-options="false"
                                        v-model="form_data.etims_item_type_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Search Item Type "
                                    />
                                </div>

                                <div
                                    class="col-md-6 form-group d-flex flex-column"
                                >
                                    <label for=""> Search Country Origin</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchOrigin"
                                        :options="item_origin_select"
                                        :auto-load-root-options="false"
                                        v-model="
                                            form_data.etims_origin_country_id
                                        "
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Search Origin "
                                    />
                                </div>
                                <div
                                    class="col-md-6 form-group d-flex flex-column"
                                >
                                    <label for=""> Search Packaging</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchPackaging"
                                        :options="item_packaging_select"
                                        :auto-load-root-options="false"
                                        v-model="
                                            form_data.etims_item_packaging_code_id
                                        "
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Search  "
                                    />
                                </div>
                               <div
                                    class="col-md-6 form-group "
                                >
                                <label for="">Current Qty</label>
                                <input type="number" v-model="form_data.current_qty" class="form-control">
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
                etims_code:this.details_data.etims_code,
                id:this.details_data.id,
                item_classification_id: this.details_data.item_classification_id,
                etims_item_type_id:  this.details_data.etims_item_type_id,
                etims_origin_country_id: this.details_data.etims_origin_country_id,
                etims_item_packaging_code_id: this.details_data.etims_item_packaging_code_id,
                is_stock_item: true,
                test: "",
                current_qty:100
            },
        };
    },
    mounted() {
        console.log("Component mounted."+JSON.stringify(this.details_data));
        this.concurrentApiReq()
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
            const res = await Promise.all([
                this.fetchPackaging(),
                this.fetchOrigin(),
                this.fetchItemType(),
                this.fetchItemClassCode()
            ]).then(function (results) {
                return results;
            });
        },
        async handleSubmit() {
            this.is_loading = true;
           
            const combinedObj = Object.assign(
                {},
                this.details_data,
                this.form_data
            );
          
            const res = await this.callApi(
                "PUT",
                "data/pgm_stock/updateItems",
                combinedObj
            );
            this.is_loading = false;
            if (res.status === 200) {
                this.s("Added to etims successfully");
                this.$emit("dashboard-active", res.data);
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
