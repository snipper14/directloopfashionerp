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
            <div class="row">
                <div class="col-md-5">
                    <import-stock-component v-on:refresh-data="refreshStock" />
                </div>
            </div>
            <!-- <ImportStock/> -->
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">Stock Details</legend>
                        <form>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Item Name *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.name"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label>Code(Unique) *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.code"
                                            placeholder="Product Code"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.description"
                                            placeholder="Description"
                                        />
                                    </div>
                                    <div class="form-group d-flex flex-column">
                                        <label for=""> Search Category*</label>
                                        <treeselect
                                            width="300"
                                            :load-options="fetchCategory"
                                            :options="category_select_data"
                                            :auto-load-root-options="false"
                                            v-model="
                                                form_data.product_category_id
                                            "
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Search Category "
                                        />
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group d-flex flex-column">
                                        <label for=""> Select Supplier</label>
                                        <treeselect
                                            width="300"
                                            :load-options="fetchSupplier"
                                            :options="supplier_select_data"
                                            :auto-load-root-options="false"
                                            v-model="form_data.supplier_id"
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Search Supplier "
                                        />
                                    </div>
                                    <div class="form-group d-flex flex-column">
                                        <label for=""> Portions/Unit*</label>

                                        <Select
                                            v-model="form_data.unit_id"
                                            @on-change="changePortion"
                                        >
                                            <Option
                                                v-for="item in unit_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.name }}</Option
                                            >
                                        </Select>
                                    </div>
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Tax Details*</label>

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
                                    <div class="form-group">
                                        <label for="">HS Code</label>

                                        <input
                                            type="text"
                                            v-model="form_data.hs_code"
                                            class="form-control"
                                        />
                                    </div>
                                     <div class="col-md-12">
                                    <div class="d-flex justify-content-between">
                                    <div class="form-group">
                                        <label for="">Min Profit Margin*</label>

                                        <input
                                            type="number"
                                            v-model="
                                                form_data.min_profit_margin
                                            "
                                            class="form-control"
                                        />
                                    </div>
                                      <div class="form-group">
                                        <label for="">% Discount*</label>

                                        <input
                                            type="number"
                                            v-model="
                                                form_data.item_discount
                                            "
                                            
                                            class="form-control"
                                        />
                                    </div>
                                    </div>
                                     </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="d-flex">
                                        <div class="form-group">
                                            <label
                                                >Purchase Price(store) *</label
                                            >
                                            <input
                                                type="number"
                                                class="form-control"
                                                autocomplete="new-user-street-address"
                                                v-model="
                                                    form_data.purchase_price
                                                "
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Selling P. *</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                autocomplete="new-user-street-address"
                                                v-model="
                                                    form_data.selling_price
                                                "
                                            />
                                        </div>
                                    </div>

                                    <div class="d-flex">
                                        <div class="form-group">
                                            <label>Reorder Qty </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                autocomplete="new-user-street-address"
                                                v-model="form_data.reorder_qty"
                                            />
                                        </div>

                                        <div
                                            class="form-group d-flex flex-column"
                                        >
                                            <label for=""
                                                >Manage Inventory *</label
                                            >

                                            <Select
                                                v-model="
                                                    form_data.inventory_type
                                                "
                                            >
                                                <Option
                                                    selected
                                                    value="inventory"
                                                >
                                                    Inventory</Option
                                                >
                                                <Option value="non_inventory"
                                                    >Non Inventory</Option
                                                >
                                            </Select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group">
                                            <label for="">Image *</label>
                                            <input
                                                ref="fileInput"
                                                type="file"
                                                class="form-control"
                                                v-on:change="selectFile"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <v-btn
                                type="button"
                                v-if="isWritePermitted"
                                color="primary"
                                @click="submitData(false)"
                                :loading="btn_loading"
                            >
                                Save
                            </v-btn>
                            <v-btn
                                title="write"
                                :loading="btn_loading"
                                color="secondary"
                                v-if="isWritePermitted"
                                class="btn btn-primary"
                                @click="submitData(true)"
                            >
                                Save $ Copy
                            </v-btn>
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ImportStock from "./ImportStock";
import ImportStockComponent from "./ImportStockComponent.vue";
export default {
    props: ["unit_data", "stock_category_data", "stock_type_data", "edit_data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            tax_data: [],
            dept_data: null,
            composition_array: ["whole", "aggregate"],
            category_select_data: null,
            btn_loading: false,
            shelf_select_data: null,
            form_data: {
            item_discount: 0,
                supplier_id: null,
                shelf_id: null,
                inventory_type: "inventory",
                tax_dept_id: null,
                product_category_id: null,
                product_name: "",
                portion_name: "",
                unit_id: null,
                name: "",
                code: "",
                composition: "",
                product_type: "",
                selling_price: 0,
                cost_price: 0,
                wholesale_price: 0,
                production_cost: 0,
                qty: 0,
                reorder_qty: 0,
                description: "",
                image: null,
                show_img: true,
                department_id: 1,
                purchase_price: 0,
                store_qty: 0,
                mapping_value: 1,
                is_active: true,
                min_profit_margin: 0,
                hs_code: "",
            },
        };
    },
    components: {
        ImportStock,
        ImportStockComponent,
        Treeselect,
    },
    created() {
        if (this.edit_data) {
            this.form_data = this.edit_data;
            if (this.form_data.product_category_id) {
                this.fetchCategory();
            }
            if (this.form_data.supplier_id) {
                this.fetchSupplier();
            }
        }
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        form_data: {
            handler() {
                this.form_data.product_name = this.form_data.name.trim();
                //     this.form_data.name.trim() +
                //     " (" +
                //     this.form_data.portion_name.trim() +
                //     ")";
                this.form_data.cost_price = this.round(
                    this.form_data.purchase_price / this.form_data.mapping_value
                );
            },
            deep: true,
        },
    },
    methods: {
        refreshStock() {
            this.$emit("dashboard-active");
        },
        async fetchShelf() {
            const res = await this.getApi("data/shelves/fetch", {});

            if (res.status == 200) {
                this.shelf_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchCategory() {
            const res = await this.getApi("data/product_category/fetchAll", {});

            if (res.status == 200) {
                this.category_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.fetchDept(),
                this.fetchTaxData(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchTaxData() {
            const res = await this.getApi("data/tax/fetch", "");

            if (res.status === 200) {
                this.tax_data = res.data;
            } else {
                this.servo();
            }
        },
        selectFile(event) {
            this.form_data.image = event.target.files[0];
        },
        changePortion() {
            let index = this.unit_data.findIndex(
                (unit) => unit.id == this.form_data.unit_id
            );
            let portion = this.unit_data[index].name;
            this.form_data.portion_name = portion;
        },
        async submitData(save_copy) {
            const data = new FormData();
            data.append("file", this.form_data.image);
            const json = JSON.stringify(this.form_data);
            data.append("data", json);
            this.btn_loading = true;
            const res = await this.callApi("post", "data/stock/create", data);
            this.btn_loading = false;
            if (res.status === 200) {
                this.successNotif(
                    " Records Details has been added successfully!"
                );
                this.$refs.fileInput.value = "";
                this.form_data.image = null;
                if (save_copy) {
                } else {
                    this.clearFields();
                }

                //  this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        clearFields() {
            Object.keys(this.form_data).forEach((key) => {
                if (key != "tax_dept_id") {
                    this.form_data[key] = "";
                }
            });
            this.form_data.image = null;
            this.form_data.department_id = null;
            //this.form_data.tax_dept_id = null;
            this.form_data.product_category_id = null;
            this.form_data.shelf_id = null;
            this.form_data.unit_id = null;
            this.form_data.is_active = true;
            this.form_data.show_img = true;
            this.form_data.qty = 0;
            this.form_data.cost_price = 0;
            this.form_data.production_cost = 0;
            this.form_data.reorder_qty = 0;
            this.form_data.store_qty = 0;
            this.form_data.selling_price = 0;
            this.form_data.wholesale_price = 0;
            this.form_data.purchase_price = 0;
            this.form_data.mapping_value = 1;
            this.form_data.description = "";
            this.form_data.min_profit_margin = 0;
            this.form_data.item_discount = 0;
            this.form_data.hs_code = "";
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
