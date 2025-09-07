<template>
    <div class="container">
        <div>
            <div class="row justify-content-center">
                <div class="col-md-2"><InventoryNav /></div>
                <div class="col-md-10">
                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <div class="col-md-3"></div>
                            <div class="col-md-5">
                                <h3>Bulk Inventory Stocktake</h3>
                            </div>
                            <div class="col-md-4 float-md-right">
                                <input
                                    type="text"
                                    placeholder="Search.."
                                    class="form-control small-input"
                                    v-model="search"
                                />
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 d-flex flex-column">
                                    <div
                                        class="form-group d-flex flex-column mr-2"
                                    >
                                        <label for=""> Location*</label>

                                        <Select v-model="params.department_id">
                                            <Option
                                                v-for="item in department_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.department }}</Option
                                            >
                                        </Select>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex flex-column">
                                    <label for=""> Search Category</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchCategory"
                                        :options="category_select_data"
                                        :auto-load-root-options="false"
                                        v-model="category_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Search Category "
                                    />
                                </div>
                                 <div class="col-md-3">
                                      
                                    <v-btn color="primary" @click="stock_sheet_modal = true"
                                        > Stocksheet</v-btn
                                    >
                                </div>
                                <div class="col-md-2">
                                    <v-btn
                                        x-small
                                        @click="syncInventoryFromStockList()"
                                        >Pull From Stocklist</v-btn
                                    >
                                </div>
                                <div class="col-md-6">
                                    <ImportBulkStocktakeCSV />
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>
                                            <th scope="col">Unit</th>
                                            <th>
                                                Shelf
                                                <v-btn x-small @click="sortAsc"
                                                    >ASC</v-btn
                                                >
                                                <v-btn x-small @click="sortDesc"
                                                    >DESC</v-btn
                                                >
                                            </th>
                                            <th>Category</th>
                                            <th scope="col">Current qty</th>
                                            <th>Counted Qty</th>
                                            <th>Deficit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in currentPageData"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.code }}
                                            </td>
                                            <td>
                                                {{ data.product_name }}
                                            </td>
                                            <td>
                                                {{ data.unit }}
                                            </td>
                                            <td>{{ data.shelf}}</td>
                                            <td>{{ data.category }}</td>

                                            <td>
                                                <input
                                                    disabled
                                                    type="number"
                                                    v-model="data.current_qty"
                                                    class="form-control"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="data.counted_qty"
                                                    class="form-control"
                                                />
                                            </td>
                                            <td>
                                                {{
                                                    data.counted_qty -
                                                    data.current_qty
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <v-btn
                                    x-small
                                    color="primary"
                                    @click="prevPage"
                                    :disabled="currentPage === 1"
                                >
                                    Previous
                                </v-btn>
                                <span
                                    >Page {{ currentPage }} of
                                    {{ totalPages }}</span
                                >
                                <v-btn
                                    x-small
                                    color="info"
                                    @click="nextPage"
                                    :disabled="currentPage === totalPages"
                                >
                                    Next
                                </v-btn>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <v-btn
                                        color="primary"
                                        :loading="btn_loading"
                                        @click="saveStockTake"
                                        >SAVE STOCKTAKE</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   <Modal width="1200px" v-model="stock_sheet_modal"
            ><stock-sheet-modal v-if="stock_sheet_modal"
        /></Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import InventoryNav from "./InventoryNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Pagination from "../utilities/Pagination.vue";
import Unauthorized from "../utilities/Unauthorized.vue";
import ImportBulkStocktakeCSV from "./ImportBulkStocktakeCSV.vue";
import StockSheetModal from './StockSheetModal.vue';

export default {
    data() {
        return {
            stock_sheet_modal:false,
            itemsPerPage: 50,
            currentPage: 1,
            check_stock_level: false,

            department_data: null,

            inventory_data: [],
            original_array_before_search: [],
            show_loader: true,
            category_select_data: null,
            search: "",
            category_id: null,
            params: {
                name: "",
                description: "",
                department_id: "",
            },
            btn_loading: false,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
        InventoryNav,
        Unauthorized,
        Treeselect,
        ImportBulkStocktakeCSV,
        StockSheetModal,
    },
    computed: {
        totalPages() {
            return Math.ceil(this.inventory_data.length / this.itemsPerPage);
        },
        currentPageData() {
            const startIndex = (this.currentPage - 1) * this.itemsPerPage;
            const endIndex = startIndex + this.itemsPerPage;
            return this.inventory_data.slice(startIndex, endIndex);
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.currentPage = 1;
                this.inventory_data = this.original_array_before_search;
                this.inventory_data = this.searchElement(val);
            }, 500),
        },
        category_id: {
            handler: _.debounce(function (val, old) {
                this.currentPage = 1;
                this.inventory_data = this.original_array_before_search;
                this.inventory_data = this.searchElementByCategoryId(val);
            }, 500),
        },
        params: {
            handler() {
                this.fetchInventory();
            },
            deep: true,
        },
    },
    methods: {
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        async saveStockTake() {
            if (this.category_id) {
                this.errorNotif("please clear filter category");
                return false;
            }
            if (this.search) {
                this.errorNotif("please clear search");
                return false;
            }

            this.showLoader();

            const res = await this.callApi("post", "data/stock_stake/create", {
                original_array_before_search: this.inventory_data,
                ...this.params,
            });
            this.hideLoader();
            if (res.status == 200) {
                this.s("updated successfully");
                this.$router.push("/stock_take_variation");
            } else {
                this.form_error(res);
            }
        },
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },

        async concurrentApiReq() {
            const res = await Promise.all([this.fetchDepartment()]).then(
                function (results) {
                    return results;
                }
            );
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

        async fetchInventory() {
            this.showLoader();
            const res = await this.getApi("data/inventory/fetch", {
                params: {
                    ...this.params,
                },
            });

            this.hideLoader();
            if (res.status === 200) {
                this.inventory_data = this.modifyResData(res.data);
                this.original_array_before_search = this.modifyResData(
                    res.data
                );
            } else {
                this.swr("Server error try again later");
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    stock_id: obj.stock_id,
                    code: obj.stock.code,
                    product_name: obj.stock.product_name,
                    category: obj.stock.product_category?obj.stock.product_category.name:"",
                    category_id: obj.stock.product_category_id,
                    unit: obj.stock.unit?obj.stock.unit.name:"",
                    selling_price: obj.stock.selling_price,
                    purchase_price: obj.stock.purchase_price,
                    shelf: obj.stock.branchshelves
                        ? obj.stock.branchshelves.shelf.name
                        : "NA" ,
                    current_qty: obj.qty,
                    counted_qty: obj.qty,
                };
            });
            return modif;
        },
        sortAsc() {
            this.inventory_data.sort((a, b) => {
                if (!a.shelf && !b.shelf) return 0; // Both have blank shelf, no change in order
                if (!a.shelf) return 1; // Only a has blank shelf, move it to the end
                if (!b.shelf) return -1; // Only b has blank shelf, move it to the end
                return a.shelf.localeCompare(b.shelf); // Compare other shelves using localeCompare
            });
        },

        // Function to sort array by shelf in descending order
        sortDesc() {
            this.inventory_data.sort((a, b) => {
                if (!a.shelf && !b.shelf) return 0; // Both have blank shelf, no change in order
                if (!a.shelf) return 1; // Only a has blank shelf, move it to the end
                if (!b.shelf) return -1; // Only b has blank shelf, move it to the end
                return b.shelf.localeCompare(a.shelf); // Compare other shelves using localeCompare
            });
        },
        searchElement(search) {
            const lowercasedInput = search.toLowerCase();
            if (lowercasedInput.trim() === "") {
                return this.original_array_before_search; // Return the original array when the input is empty
            }
            return this.inventory_data.filter((item) =>
                item.product_name.toLowerCase().includes(lowercasedInput)
            );
        },
        async syncInventoryFromStockList() {
            if (!this.params.department_id) {
                this.errorNotif("select location first");
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/inventory/syncInventoryFromStockList",
                {
                    department_id: this.params.department_id,
                }
            );
            this.hideLoader();
            console.log(JSON.stringify(res));
            if (res.status === 200) {
                this.successNotif("Completed syncing");
                window.location.reload();
            } else {
                this.form_error(res);
            }
        },
        fetchExcel() {
            if (this.inventory_data.length > 0) {
                const data_array = [];
                this.inventory_data.map((data) => {
                    data_array.push({
                        STOCK_ID: data.stock_id,
                        LOCATION_ID: this.params.department_id,
                        CODE: data.code,
                        ITEM: data.product_name,
                        UNIT: data.unit,
                        CATEGORY: data.category,

                        "CURRENT QTY ": data.current_qty,

                        "COUNTED QTY": data.counted_qty,
                        SHELF: data.shelf,
                    });
                });
                return data_array;
            } else {
                this.errorNotif("select location first");
                return;
            }
        },
        searchElementByCategoryId(search) {
            console.log("reeee" + search);
            const lowercasedInput = search;
            if (!lowercasedInput) {
                console.log(">>>>>>>>>>");
                return this.original_array_before_search; // Return the original array when the input is empty
            }
            return this.inventory_data.filter(
                (item) => item.category_id == lowercasedInput
            );
        },
    },
};
</script>
<style scoped>
.show-img {
    border-color: #ff6f00;
    background: #ff6f00;
}
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
.isLate {
    background: rgb(129, 31, 50);
    color: #fff;
}
</style>
