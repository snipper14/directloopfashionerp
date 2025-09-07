<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="goBack()"
                color="primary"
                dark
            >
                <v-icon dark left> mdi-arrow-left </v-icon>
                Back
            </v-btn>
            <div class="row">
                <div class="col-md-5">
                    <import-edit-stock-component
                        v-on:refresh-data="refreshStock"
                    />
                </div>
            </div>
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">
                            Update Stock Details
                        </legend>
                        <div>
                            <v-btn
                                color="info"
                                small
                                @click="mappings_modal = true"
                            >
                                Conversions
                            </v-btn>
                            <v-btn
                                color="primary"
                                small
                                @click="aggregate_modal = true"
                            >
                                Add BOM
                            </v-btn>
                        </div>
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
                                        <label for=""> Search Supplier</label>
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
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group d-flex flex-column">
                                        <label for=""> Search Category</label>
                                        <treeselect
                                            width="300"
                                            :load-options="fetchCategory"
                                            :options="stock_category_data"
                                            :auto-load-root-options="false"
                                            v-model="
                                                form_data.product_category_id
                                            "
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Search Category "
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
                                        <div class="d-flex">
                                            <div class="form-group">
                                                <label for=""
                                                    >Min Profit Margin*</label
                                                >

                                                <input
                                                    type="number"
                                                    v-model="
                                                        form_data.min_profit_margin
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                              <div class="form-group">
                                                <label for=""
                                                    >% Discount*</label
                                                >

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

                            <button
                                type="button"
                                v-if="isWritePermitted"
                                class="btn btn-primary"
                                @click="submitData()"
                            >
                                Save
                            </button>
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>
        <Modal v-model="accomp_modal" title="Add Accompaniment" width="60px">
            <AddAccompanimentModal
                v-if="accomp_modal"
                :product_data="form_data"
            />
        </Modal>
        <Modal v-model="aggregate_modal" title="Add Aggregates" width="1200px">
            <add-ingredients
                v-if="aggregate_modal"
                :product_data="form_data"
                v-on:edit-active="dismissModal"
            />
        </Modal>
        <Modal v-model="mappings_modal" title="Add Mappings" width="60px">
            <product-mapping-modal
                v-if="mappings_modal"
                :product_data="form_data"
            />
        </Modal>
        <Modal
            v-model="production_cost_modal"
            title="Add Production Cost"
            width="800px"
        >
            <add-production-cost
                v-if="production_cost_modal"
                :product_data="form_data"
                v-on:edit-active="dismissModal"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import AddAccompanimentModal from "./AddAccompanimentModal.vue";
import ImportStock from "./ImportStock";
import ProductMappingModal from "./ProductMappingModal.vue";
import AddIngredients from "./stock_production/AddIngredients.vue";
import AddProductionCost from "./stock_production/AddProductionCost.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ImportEditStockComponent from "./ImportEditStockComponent.vue";
export default {
    props: ["edit_data", "unit_data", "stock_category_data", "stock_type_data"],
    data() {
        return {
            tax_deduction_select: [{ name: "vatable" }, { name: "exempted" }],
            production_cost_modal: false,
            mappings_modal: false,
            aggregate_modal: false,
            accomp_modal: false,
            value: null,
            window: 0,
            showContent: true,
            tax_data: [],
            dept_data: null,
            shelf_select_data: null,
            composition_array: ["whole", "aggregate"],
            form_data: {
                supplier_id: null,
                id: null,
                product_category_id: null,
                product_name: "",
                portion_name: "",
                unit_id: null,
                name: "",
                code: "",
                composition: "",
                product_type: "",
                tax_dept_id: null,
                selling_price: 0,
                cost_price: 0,
                wholesale_price: 0,
                production_cost: 0,
                qty: 0,
                reorder_qty: 0,
                description: "",
                image: "",
                show_img: true,
                department_id: null,
                purchase_price: 0,
                store_qty: 0,
                mapping_value: 1,
                is_active: true,
                menu_description: "",
                inventory_type: "",
                shelf_id: null,
                min_profit_margin: 0,
                min_profit_margin_field: 0,
                item_discount: 0,
                hs_code: "",
            },
        };
    },
    mounted() {
        this.concurrentApiReq();

        this.form_data = this.edit_data;
        this.form_data.image = null;
        // if(this.edit_data.min_profit_margin==0){  this.form_data.min_profit_margi=this.$store.state.branch.min_profit_margin}
    },
    components: {
        ImportStock,
        AddAccompanimentModal,
        AddIngredients,
        AddProductionCost,
        ProductMappingModal,
        Treeselect,
        ImportEditStockComponent,
    },
    watch: {
        form_data: {
            handler() {
                this.form_data.product_name = this.form_data.name.trim();
                // " (" +
                // this.form_data.portion_name +
                // ")";
                this.form_data.cost_price = this.round(
                    this.form_data.purchase_price / this.form_data.mapping_value
                );
                this.changePortion();
            },
            deep: true,
        },
        "form_data.purchase_price": function (newPrice) {
            //  this.calculateSellingPrice();
        },
        "form_data.min_profit_margin": function (newMargin) {
            //  this.calculateSellingPrice();
        },
    },
    methods: {
        calculateSellingPrice() {
            const purchasePrice = parseFloat(this.form_data.purchase_price);
            const profitMargin =
                parseFloat(this.form_data.min_profit_margin_field) / 100;

            if (purchasePrice > 0 && profitMargin > 0 && profitMargin < 1) {
                // Using the updated formula to calculate selling price
                this.form_data.selling_price = (
                    purchasePrice /
                    (1 - profitMargin)
                ).toFixed(2);
            } else {
                //  this.form_data.selling_price = 0;
            }
        },
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
                this.stock_category_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        dismissModal() {
            this.production_cost_modal = false;
            this.mappings_modal = false;
            this.aggregate_modal = false;
            this.accomp_modal = false;
        },
        async showAddAccompanimentModal(e) {
            this.accomp_modal = true;
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                //  this.fetchCategory(),
                this.fetchSupplier(),
                this.fetchShelf(),
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
        goBack() {
            this.$emit("dashboard-active", this.edit_data);
        },
        async submitData() {
            const data = new FormData();

            data.append("file", this.form_data.image);
            const json = JSON.stringify(this.form_data);
            data.append("data", json);
            this.showLoader();
            const res = await this.callApi("post", "data/stock/update", data);
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active", res.data);
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
