<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12">
                <stock-nav />
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_menu_item')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Menu Item Management</h5>
                        </div>
                        <div class="col-md-4 float-md-right ">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Menu Name</th>
                                        <th scope="col">Selling Price</th>
                                        <th scope="col">Wholesale P</th>
                                        <th scope="col">Cost Price</th>
                                        <th scope="col">Production Cost</th>
                                        <th scope="col">qty</th>
                                        <th scope="col">reorder qty</th>
                                        <th scope="col">Unit</th>

                                        <th scope="col">Branch</th>
                                        <th scope="col">Category</th>

                                        <th scope="col">Description</th>
                                        <th scope="col">Production</th>
                                        <th scope="col">Stock Take</th>

                                        <th scope="col">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data,
                                        i) in menu_product_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    editMenuStock(data)
                                                "
                                            >
                                                {{ data.code }}</router-link
                                            >
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    editMenuStock(data)
                                                "
                                                >{{ data.name }}</router-link
                                            >
                                        </td>
                                        <td>
                                            {{ data.menu_name }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.selling_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.wholesale_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.cost_price) }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.production_cost
                                                )
                                            }}
                                        </td>

                                        <td>{{ data.qty }}</td>
                                        <td>{{ data.reorder_qty }}</td>
                                        <td>
                                            {{
                                                data.unit
                                                    ? data.unit.name
                                                    : ""
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                data.branch
                                                    ? data.branch.branch
                                                    : ""
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                data.product_category
                                                    ? data.product_category.name
                                                    : ""
                                            }}
                                        </td>
                                        <td>{{ data.description }}</td>
                                        <td> <router-link
                                                to="#"
                                                @click.native="
                                                   menuProduction(data)
                                                "
                                            >Production</router-link>
                                          
                                        </td>
                                        <td>
                                            <button
                                                v-if="isUpdatePermitted"
                                                @click="stockTakeDiag(data)"
                                                class="btn btn-secondary btn-sm"
                                            >
                                                StockTake
                                            </button>
                                        </td>
                                        <td>
                                            <button
                                                v-if="isDeletePermitted"
                                                @click="deleteData(data.id, i)"
                                                class="btn btn-danger btn-sm"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="menu_product_data !== null"
                            v-bind:results="menu_product_data"
                            v-on:get-page="fetchMenuProduct"
                        ></Pagination>
                        Items Count {{ menu_product_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchAllMenuItems"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchAllMenuItems"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiFileExcel
                                    }}</v-icon>
                                    Export CSV
                                </export-excel>
                            </div>
                        </div>
                        <Modal v-model="stock_take_modal" title="Update Stock">
                            <menu-stock-take
                                v-if="active.stock_take_component"
                                :stocktake_form_data="stocktake_form_data"
                                v-on:dismss-stocktake="dismss_stocktake"
                            />
                        </Modal>
                    </div>
                </div>

                <center v-else>
                    <b style="color:red;font-size:1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>

        <AddMenuItem
            v-if="active.add_menu_item"
            v-on:dashboard-active="setActiveRefresh"
            :menu_category_data="menu_category_data"
            :unit_data="unit_data"
        >
        </AddMenuItem>
        <EditMenuItem
            v-if="active.edit_stock"
            v-bind:edit_data="edit_data"
            :menu_category_data="menu_category_data"
            :unit_data="unit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </EditMenuItem>
        <notifications group="foo" />
    </div>
</template>

<script>
import AddMenuItem from "./AddMenuItem.vue";
import EditMenuItem from "./EditMenuItem.vue";
import Pagination from "../../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import StockNav from "../../StockNav.vue";
import MenuStockTake from "./MenuStockTake.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_menu_item: false,
                edit_stock: false,
                stock_take_component: false
            },
            stock_take_modal: false,
            stocktake_form_data: null,

            menu_product_data: [],
            edit_data: null,
            pdf_data: [],
            unit_data: [],
            menu_category_data: [],
            search: "",
            params: {
                name: "",
                description: ""
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        AddMenuItem,
        EditMenuItem,
        VueHtml2pdf,
        Pagination,
        StockNav,
        MenuStockTake
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        }
    },
    methods: {
        dismss_stocktake() {
            this.stock_take_modal = false;
            this.active.stock_take_component = false;
            this.fetchMenuProduct();
        },
        stockTakeDiag(data) {
            this.stock_take_modal = true;
            this.active.stock_take_component = true;
            this.stocktake_form_data = data;
        },
        async concurrentApiReq() {
            const res = await Promise.all([
                this.fetchMenuProduct(1),
                this.fetchCategory(),
                this.fetchUnit()
            ]).then(function(results) {
                return results;
            });
        },
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/menu_item/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.menu_product_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        editMenuStock(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_stock");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.fetchAllMenuItems();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async fetchAllMenuItems() {
            const res = await this.getApi("data/menu_product/fetchAll", "");

            var stock_array = res.data;
            for (var i = 0; i < stock_array.length; i++) {
                stock_array[i].branch = stock_array[i].branch
                    ? stock_array[i].branch.branch
                    : "";
                stock_array[i].menu_category = stock_array[i].menu_category
                    ? stock_array[i].menu_category.name
                    : "";
                stock_array[i].portion = stock_array[i].portion
                    ? stock_array[i].portion.name
                    : "";
            }

            return stock_array;
        },
        async fetchCategory() {
            const res = await this.getApi("data/product_category/fetchAll", "");

            
            this.menu_category_data = res.data;
        },
        async fetchUnit() {
            const res = await this.getApi("data/unit/fetchAll", "");
           
            this.unit_data = res.data;
          
        },
        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/menu_product/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.menu_product_data = res.data;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchMenuProduct();
        },

        async fetchMenuProduct(page) {
            this.showLoader();
            const res = await this.getApi("data/menu_product/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.menu_product_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
