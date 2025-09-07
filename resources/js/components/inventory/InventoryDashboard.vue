<template>
    <div class="container">
        <div v-if="isReadPermitted">
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-md-2"><InventoryNav /></div>
                <div class="col-md-10">
                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <div class="col-md-3"></div>
                            <div class="col-md-5">
                                <h3>Inventory</h3>
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
                                            <Option value="">All</Option>
                                            <Option
                                                v-for="item in department_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.department }}</Option
                                            >
                                        </Select>
                                    </div>
                                    <div>
                                        <v-btn
                                            v-if="isDeletePermitted"
                                            color="danger"
                                            title="DELETE"
                                            @click="emptyInventory()"
                                            >Empty All Records</v-btn
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-dark">
                                        <tr>
                                            <td>Item Count</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        inventory_value.sum_qty
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Value On Cost</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        inventory_value.value_on_pp
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Value On SP</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        inventory_value.value_on_sp
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </table>
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
                                            <th scope="col">Branch</th>
                                            <th scope="col">Location</th>
                                            <th scope="col">
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
                                                Purchase P<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_purchase_price
                                                    "
                                                    sort-key="sort_purchase_price"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                />
                                            </th>
                                            <th scope="col">
                                                Selling P<sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_selling_price
                                                    "
                                                    sort-key="sort_selling_price"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                />
                                            </th>
                                            <th scope="col">reorder qty</th>
                                            <th scope="col">
                                                qty
                                                <sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_qty
                                                    "
                                                    sort-key="sort_qty"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                />
                                            </th>
                                            <th>
                                                Value on CP
                                                <sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_purchase_value
                                                    "
                                                    sort-key="sort_purchase_value"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                />
                                            </th>
                                            <th>
                                                Value on SP
                                                <sort-buttons
                                                    :initial-sort="
                                                        sort_params.sort_selling_value
                                                    "
                                                    sort-key="sort_selling_value"
                                                    @update-sort="
                                                        updateSortParameter
                                                    "
                                                />
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in inventory_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td>
                                                {{ data.stock.name }}
                                            </td>
                                            <td>
                                                {{ data.stock.unit.name }}
                                            </td>
                                            <td>
                                                {{ data.branch.branch }}
                                            </td>
                                            <td>
                                                {{ data.department.department }}
                                            </td>
                                            <td>
                                                {{
                                                    data.stock.branchshelves
                                                        ? data.stock
                                                              .branchshelves
                                                              .shelf.name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.stock
                                                            .purchase_price
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.stock.selling_price
                                                    )
                                                }}
                                            </td>
                                            <td
                                                :class="{
                                                    isLate:
                                                        data.stock
                                                            .reorder_qty >=
                                                        data.qty,
                                                }"
                                            >
                                                <p>
                                                    {{ data.stock.reorder_qty }}
                                                </p>
                                            </td>
                                            <td>
                                                {{ data.qty }}
                                            </td>
                                            <th>
                                                {{
                                                    cashFormatter(
                                                        data.total_purchase_value
                                                    )
                                                }}
                                            </th>
                                            <th>
                                                {{
                                                    cashFormatter(
                                                        data.total_selling_value
                                                    )
                                                }}
                                            </th>
                                            <td>
                                                <v-btn
                                                    title="DELETE"
                                                    v-if="isDeletePermitted"
                                                    @click="deleteData(data, i)"
                                                    color="danger"
                                                    x-small
                                                    >delete</v-btn
                                                >
                                            </td>
                                            <td>
                                                <v-btn
                                                    color="info"
                                                    v-if="false"
                                                    x-small
                                                    @click="
                                                        getStockMovementHistory(
                                                            data
                                                        )
                                                    "
                                                    >check history</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                          
                                <pagination
                                    v-if="inventory_data !== null"
                                    v-bind:results="inventory_data"
                                    v-on:get-page="fetchInventory"
                                ></pagination>
                                Items Count {{ inventory_data.total }}
                              
                          
                            <div class="row">
                                <div
                                    class="col-4 d-flex"
                                    v-if="isDownloadPermitted"
                                >
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="fetchExcel"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                       <v-btn color="primary" x-small>    Export Excel</v-btn>
                                    
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="fetchExcel"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="filename.xls"
                                    >
                                        <v-icon class="v-icon" medium>{{
                                            icons.mdiFileExcel
                                        }}</v-icon>
                                        Export CSV
                                    </export-excel>

                                    <v-btn
                                        color="success"
                                        x-small
                                        @click="printInv"
                                        >Print Inventory</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <center v-else>
            <unauthorized />
        </center>
        <inventory-print-component
            ref="InventoryReportPrintComponent"
            v-if="print_report"
            :inventory="print_data_array"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import InventoryNav from "./InventoryNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Pagination from "../utilities/Pagination.vue";
import Unauthorized from "../utilities/Unauthorized.vue";
import SortButtons from "../utilities/SortButtons.vue";
import InventoryPrintComponent from "./InventoryPrintComponent.vue";

export default {
    data() {
        return {
            check_stock_level: false,
            active: {
                dashboard: true,
                add_menu_item: false,
                edit_stock: false,
                stock_take_component: false,
                create_ingredients: false,
            },
      
            department_data: null,
            stock_take_modal: false,
            print_data_array: [],
            print_report: false,
            stocktake_form_data: null,
            stock_type_data: [{ name: "general" }, { name: "menu" }],
            inventory_data: [],
            product_data: null,
            edit_data: null,
            inventory_value: {},
            unit_data: [],
            stock_category_data: [],
            search: "",
            params: {
                name: "",
                description: "",
                department_id: "",
                show_desc: false,
            },
            sort_params: {
                sort_shelf: "",
                sort_qty: "",
                sort_purchase_value: "",
                sort_selling_value: "",
                sort_selling_price: "",
                sort_purchase_price: "",
            },
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
        SortButtons,
        InventoryPrintComponent,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        check_stock_level: {
            handler: _.debounce(function (val, old) {
                console.log(this.check_stock_level);
                this.searchProducts();
            }, 500),
        },
        params: {
            handler() {
                this.searchProducts();
                this.fetchInventoryValue();
            },
            deep: true,
        },

        search: {
            handler: _.debounce(function (val, old) {
                this.searchProducts();
                this.fetchInventoryValue();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.searchProducts();
            }, 500),
        },
    },
    methods: {
      
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        reorder() {
            this.params.show_desc = !this.params.show_desc;
        },
        async emptyInventory() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/inventory/emptyInventory",
                    { department_id: this.params.department_id }
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.concurrentApiReq();
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetchAll", "");

            this.department_data = res.data;
        },
        async getStockMovementHistory(data) {
            this.showLoader();
            const res = await this.getApi(
                "data/stock_history/getStockMovementHistory",
                {
                    params: {
                        stock_id: data.stock_id,
                    },
                }
            );
            this.hideLoader();
        },

        async concurrentApiReq() {
            const res = await Promise.all([
                this.fetchInventory(1),
                this.fetchInventoryValue(),
                this.fetchCategory(),
                this.fetchDepartment(),
            ]).then(function (results) {
                return results;
            });
        },

        async deleteData(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/inventory/destroy",
                    data
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.inventory_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async deleteImg(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/stock/delete_img/" + data.id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.inventory_data.data[i].img_url = null;
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        editMenuStock(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_stock");
        },
        async printInv() {
            this.showLoader();
            const res = await this.getApi("data/inventory/fetch", {
                params: {
                    check_stock_level: this.check_stock_level,

                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                    ...this.sort_params,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.print_data_array = res.data;
                this.print_report = true;
                setTimeout(() => {
                    this.$refs.InventoryReportPrintComponent.printTable();

                    this.print_report = false;
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async fetchExcel() {
            const res = await this.getApi("data/inventory/fetch", {
                params: {
                    check_stock_level: this.check_stock_level,

                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                    ...this.sort_params,
                },
            });

            var data_array = [];
            res.data.map((array_item) => {
                data_array.push({
                    product_code: array_item.stock.code,
                    product_name: array_item.stock.product_name,
                    unit: array_item.stock.unit.name,

                    location: array_item.department.department,
                    shelf: array_item.stock.branchshelves
                        ? array_item.stock.branchshelves.shelf.name
                        : "NA",
                    purchase_price: array_item.stock.purchase_price,
                    selling_price: array_item.stock.selling_price,
                    qty: array_item.qty,

                    value_on_cost:
                        array_item.stock.purchase_price * array_item.qty,
                    value_on_sp:
                        array_item.stock.selling_price * array_item.qty,
                });
            });
            return data_array;
        },
        async fetchCategory() {
            const res = await this.getApi("data/product_category/fetchAll", "");

            this.stock_category_data = res.data;
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/inventory/fetch", {
                params: {
                    check_stock_level: this.check_stock_level,
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                    ...this.sort_params,
                },
            });
            if (res.status === 200) {
                this.inventory_data = res.data;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchInventory();
        },

        async fetchInventory(page) {
            this.showLoader();
            const res = await this.getApi("data/inventory/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                    ...this.sort_params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.inventory_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        async fetchInventoryValue() {
            const res = await this.getApi(
                "data/inventory/fetchInventoryValue",
                {
                    params: {
                        search: this.search.length >= 4 ? this.search : "",
                        ...this.params,
                        ...this.sort_params,
                    },
                }
            );

            if (res.status === 200) {
                this.inventory_value = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
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
