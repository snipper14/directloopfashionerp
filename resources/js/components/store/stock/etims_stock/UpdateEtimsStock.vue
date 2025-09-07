<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Add Etims Stock</div>

                        <div class="card-body">
                            <div class="row">
                                 <div class="col-md-6 form-group">
                                    <label for="">Product Name *</label>
                                    <input
                                        type="text"
                                        v-model="form_data.product_name"
                                        class="form-control"
                                    />
                                </div>
                                  <div class="col-md-6 form-group">
                                    <label for="">Selling Price</label>
                                    <input
                                        type="number"
                                        v-model="form_data.selling_price"
                                        class="form-control"
                                    />
                                </div>
                                   <div class="col-md-6 form-group d-flex flex-column">
                                        <label for="">Tax Code*</label>

                                        <Select v-model="form_data.tax_dept_id">
                                            <Option
                                                v-for="item in tax_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{
                                                    item.tax_rate +
                                                    " -" +
                                                    item.tax_code
                                                }}</Option
                                            >
                                        </Select>
                                    </div>
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
            tax_data: [],
            is_loading: false,
            form_data: {
                item_classification_id: null,
                etims_item_type_id: null,
                etims_origin_country_id: null,
                etims_item_packaging_code_id: null,
                is_stock_item: true,

                item_id: null,
                selling_price: 0,
            },
        };
    },
    mounted() {
        this.form_data.item_id = this.details_data.item_id;

        this.concurrentApiReq();
        this.form_data = this.details_data;

        console.log(JSON.stringify(this.form_data));
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([
                this.fetchPackaging(),
                this.fetchOrigin(),
                this.fetchItemType(),
                this.fetchItemClassCode(),
                this.fetchTaxData()
            ]);
            this.isLoading ? this.hideLoader() : "";
        },
          async fetchTaxData() {
            const res = await this.getApi("data/tax/fetch", "");

            if (res.status === 200) {
                this.tax_data = res.data;
            } else {
                this.servo();
            }
        },
        async handleSubmit() {
            this.is_loading = true;
            const res = await this.callApi(
                "PUT",
                "data/digitax_stock/updateItems",
                this.form_data
            );
            this.is_loading = false;
            if (res.status === 200) {
                this.s("Update to etims successfully");
                this.$emit("update-active", res.data);
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
