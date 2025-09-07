<template>
    <div class="row">
        <div class="col-md-2 nopadding">
            <inventory-nav />
        </div>
        <div class="col-md-10 nopadding">
            <div class="container" v-if="isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <!-- <ImportExcel/> -->
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            ></div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5">
                                        <h5>Stocktake Stock</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <div
                                            class="form-group d-flex flex-column mr-2"
                                        >
                                            <label for=""> Location*</label>

                                            <Select
                                                v-model="
                                                    search_query.department_id
                                                "
                                            >
                                                <Option value="">All</Option>
                                                <Option
                                                    v-for="item in department_data"
                                                    :value="item.id"
                                                    :key="item.id"
                                                    >{{
                                                        item.department
                                                    }}</Option
                                                >
                                            </Select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <!-- <v-btn
                                            x-small
                                            color="primary"
                                            @click="bulkInventoyEtimsSync"
                                            >Sync all Inventory to etims</v-btn
                                        ><br />
                                        -->
                                        <v-btn
                                            x-small
                                            color="info"
                                            @click="
                                                bulkInventoyEtimsSyncZeroQty(0)
                                            "
                                            >Sync Inventory to etims
                                        </v-btn>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div class="row">
                                    <div class="col-2 border border-secondary">
                                        <div class="form-group">
                                            <label>Search Code</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="search_query.code"
                                                autocomplete="new-user-street-address"
                                                placeholder="Code"
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Product </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="search_query.search"
                                                autocomplete="new-user-street-address"
                                                placeholder="Search"
                                            />
                                        </div>
                                        <div class="form-group">
                                            <v-btn
                                                x-small
                                                color="secondary"
                                                @click="search_results = []"
                                            >
                                                Clear Search
                                            </v-btn>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-10 border border-secondary"
                                    >
                                        <div class="table-responsive">
                                            <table
                                                class="table table-sm table-striped table-bordered custom-table"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th
                                                          
                                                            style="width: 80px; min-width: 80px; max-width: 80px;"
                                                        >
                                                            Etims ID
                                                            <sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_taxid
                                                                "
                                                                sort-key="sort_taxid"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            />
                                                        </th>
                                                        <th>
                                                            Shelf<sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_shelf
                                                                "
                                                                sort-key="sort_shelf"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            />
                                                        </th>
                                                        <th scope="col">
                                                            Code
                                                            <sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_code
                                                                "
                                                                sort-key="sort_code"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            />
                                                        </th>
                                                        <th scope="col">
                                                            Item Name
                                                            <sort-buttons
                                                                :initial-sort="
                                                                    sort_params.sort_name
                                                                "
                                                                sort-key="sort_name"
                                                                @update-sort="
                                                                    updateSortParameter
                                                                "
                                                            />
                                                        </th>

                                                        <th>Curr. Qty</th>
                                                        <th scope="col">
                                                            Update Stock
                                                        </th>
                                                        <th
                                                            scope="col"
                                                            v-if="
                                                                branch.etims_type ==
                                                                'oscu'
                                                            "
                                                        >
                                                            Update Etims Stock
                                                        </th>

                                                        <th scope="col">
                                                            Add/Deduct Stock
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        class="small-tr"
                                                        v-for="(
                                                            data, i
                                                        ) in search_results.data"
                                                        :key="i"
                                                    >
                                                        <td style="width: 80px; min-width: 80px; max-width: 80px;"
                                                           
                                                        >
                                                            {{ data.item_id ? data.item_id.toString().substring(0, 10) : '' }}
                                                        </td>
                                                        <td   >
                                                           <span  style="width: 35px; min-width: 35px;"> {{ data.shelf }}</span>
                                                        </td>
                                                        <td  style="width: 50px; min-width: 50px;">
                                                            {{ data.code }}
                                                        </td>
                                                        <td>
                                                            <router-link
                                                                to="#"
                                                                @click.native="
                                                                    checkInventory(
                                                                        data
                                                                    )
                                                                "
                                                            >
                                                                {{
                                                                    data.product_name
                                                                }}</router-link
                                                            >
                                                        </td>

                                                        <td>
                                                            <input
                                                                style="width: 150px; min-width: 150px;"
                                                                class="form-control input-sm"
                                                                type="number"
                                                                v-model="
                                                                    data.curr_qty
                                                                "
                                                            />
                                                        </td>
                                                        <td>
                                                            <v-btn
                                                                :loading="
                                                                    btn_loading
                                                                "
                                                                @click="
                                                                    updateStockQty(
                                                                        data
                                                                    )
                                                                "
                                                                x-small
                                                                class="warning"
                                                            >
                                                                Update
                                                            </v-btn>
                                                        </td>
                                                        <td
                                                            v-if="
                                                                branch.etims_type ==
                                                                'oscu'
                                                            "
                                                        >
                                                            <v-btn
                                                                :loading="
                                                                    btn_loading
                                                                "
                                                                @click="
                                                                    updateStockQtyEtims(
                                                                        data
                                                                    )
                                                                "
                                                                x-small
                                                                class="info"
                                                            >
                                                                Add Etims
                                                            </v-btn>
                                                        </td>
                                                        <td>
                                                            <v-btn
                                                                :loading="
                                                                    btn_loading
                                                                "
                                                                v-if="
                                                                    isUpdatePermitted
                                                                "
                                                                @click="
                                                                    addDeductStock(
                                                                        data,
                                                                        'increment'
                                                                    )
                                                                "
                                                                color="primary"
                                                                x-small
                                                            >
                                                                +ADD
                                                            </v-btn>
                                                            <v-btn
                                                                :loading="
                                                                    btn_loading
                                                                "
                                                                v-if="
                                                                    isUpdatePermitted
                                                                "
                                                                @click="
                                                                    addDeductStock(
                                                                        data,
                                                                        'decrement'
                                                                    )
                                                                "
                                                                x-small
                                                                color="secondary"
                                                            >
                                                                -DEDUCT
                                                            </v-btn>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <pagination
                                            v-if="search_results !== null"
                                            v-bind:results="search_results"
                                            v-on:get-page="searchProduct"
                                        ></pagination>
                                        Items Count {{ search_results.total }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <unauthorized />
            </div>
        </div>
        <Modal v-model="stock_take_modal" title=" Stock Deduction">
            <stock-deduction
                v-if="active.stock_take_component"
                :stocktake_form_data="stocktake_form_data"
                v-on:dismss-stocktake="dismss_stocktake"
        /></Modal>
        <Modal v-model="stock_update_modal" title="Update Stock ">
            <physical-stock-update
                v-if="active.stock_update_component"
                :stocktake_form_data="stocktake_form_data"
                v-on:dismss-stocktake="dismss_update_stock"
        /></Modal>
        <Modal v-model="check_inventory_modal">
            <check-inventory-modal
                v-if="check_inventory_modal"
                :inventory_data="inventory_data"
        /></Modal>
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import StockUpdateNav from "./StockUpdateNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import StockDeduction from "./StockDeduction.vue";
import PhysicalStockUpdate from "./PhysicalStockUpdate.vue";
import InventoryNav from "../../inventory/InventoryNav.vue";
import CheckInventoryModal from "../../pos_retail/CheckInventoryModal.vue";
import Pagination from "../../utilities/Pagination.vue";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    components: {
        StockUpdateNav,
        Unauthorized,
        StockDeduction,
        PhysicalStockUpdate,
        InventoryNav,
        CheckInventoryModal,
        Pagination,
        SortButtons,
    },
    data() {
        return {
            branch: this.$store.state.branch,
            active: {
                stock_take_component: false,
                stock_update_component: false,
            },
            check_inventory_modal: false,
            inventory_data: null,
            btn_loading: false,
            stock_update_modal: false,
            search_results: [],
            stock_take_modal: false,
            department_data: null,

            search_query: {
                code: "",
                search: "",
                department_id: null,
                type: "",
            },
            sort_params: {
                sort_shelf: "",
                sort_taxid: "",
                sort_code: "",
                sort_name: "",
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                if (!this.search_query.department_id) {
                    this.errorNotif("Select department");
                    return;
                }
                this.searchProduct(1);
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.searchProduct(1);
            }, 500),
        },
    },
    mounted() {
        this.fetchDepartment();
    },

    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        reorder() {
            this.search_query.sort_shelf = !this.search_query.sort_shelf;
        },
        async checkInventory(data) {
            console.log(JSON.stringify(data));
            this.showLoader();
            const res = await this.getApi("data/inventory/getItemQty", {
                params: {
                    id: data.stock_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.inventory_data = res.data;
                this.check_inventory_modal = true;
            } else {
                this.form_error(res);
            }
        },
        async updateStockQtyEtims(data) {
            this.btn_loading = true;
            this.search_query.type = "Manual Update";
            const assign = Object.assign(data, this.search_query);
            // const assign = Object.assign(assign1,{'department_id':this.department_id});

            const res = await this.callApi(
                "PUT",
                "data/digitax_stock/updateStockQtyEtims",
                assign
            );
            this.btn_loading = false;

            if (res.status == 200) {
                this.s("Updated ");
            } else {
                this.form_error(res);
            }
        },
        async updateStockQty(data) {
            this.btn_loading = true;
            this.search_query.type = "Manual Update";
            const assign = Object.assign(data, this.search_query);
            // const assign = Object.assign(assign1,{'department_id':this.department_id});

            const res = await this.callApi(
                "POST",
                "data/inventory/updateStockQty",
                assign
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.s("Updated ");
            } else {
                this.form_error(res);
            }
        },
        async addDeductStock(data, type) {
            this.btn_loading = true;
            this.search_query.type = type;
            const assign = Object.assign(data, this.search_query);

            const res = await this.callApi(
                "POST",
                "data/inventory/addDeductStockQty",
                assign
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.s("Updated ");
            } else {
                this.form_error(res);
            }
        },
        async bulkInventoyEtimsSync() {
            this.showLoader();
            const res = await this.getApi(
                "data/digitax_stock/bulkInventoyEtimsSync",
                ""
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Updated ");
            } else {
                this.form_error(res);
            }
        },
        async bulkInventoyEtimsSyncZeroQty(lastId = 0) {
            // Only show loader on the initial call
            if (lastId === 0) {
            this.showLoader();
            }

            try {
            const res = await this.getApi(
                `data/digitax_stock/bulkInventoyEtimsSyncZeroQty?last_id=${lastId}`,
                ""
            );

                if (res.status === 200) {
                    this.s("Batch updated successfully.");

                    // If there's more data, continue syncing
                   
                   
                    if (res.data && res.data.last_id === null) {
                        // Abort if last_id is explicitly null
                        this.successNotif("Sync aborted: No more data to sync.");

                        this.hideLoader();
                    } else if (res.data && res.data.last_id) {
                        this.bulkInventoyEtimsSyncZeroQty(res.data.last_id);
                    } else {
                        this.s("Sync Completed");
                        this.hideLoader();
                    }
                } else {
                    this.form_error(res);
                    this.hideLoader();
                }
            } catch (error) {
                console.error("Sync Error:", error);
                this.form_error(error);
                this.hideLoader();
            }
        },
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },
        dismss_update_stock() {
            this.stock_update_modal = false;
            this.active.stock_update_component = false;
        },
        dismss_stocktake() {
            this.stock_take_modal = false;
            this.active.stock_take_component = false;
        },
        stockTakeDiag(data) {
            this.stock_take_modal = true;
            this.active.stock_take_component = true;
            this.stocktake_form_data = data;
        },
        updateStock(data) {},
        async searchProduct(page) {
            const res = await this.getApi(
                "data/stock/searchShelfSortItemGeneric",
                {
                    params: {
                        page,
                        ...this.search_query,
                        ...this.sort_params,
                    },
                }
            );

            const res2 = this.modifyResData(res.data.data);
            this.search_results = {
                ...res.data, // Keep the original pagination metadata
                data: res2, // Replace the data array with the modified items
            };
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    qty: 1,

                    code: obj.code,
                    product_name: obj.product_name,
                    curr_qty: 0,
                    item_id: obj.item_id,
                    stock_id: obj.id,
                    shelf: obj.branchshelves
                        ? obj.branchshelves.shelf.name
                        : "",
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.approved-selected {
    background: #af4448 !important;
}
.progress-selected {
    background: #00bcd4 !important;
}
</style>
