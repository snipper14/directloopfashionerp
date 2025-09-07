<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <etim-stock-nav />
            </div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-2 d-flex flex-column">
                         
                            <v-btn
                                v-if="isDeletePermitted"
                                color="danger"
                                title="DELETE"
                                @click="deleteAllStock()"
                                >Delete All Records</v-btn
                            >
                            <v-btn v-if="false" @click="apiTest()">TEST API</v-btn>
                        </div>

                        <div class="col-md-3">
                            <h5>ETIMS Products/Stock Management</h5>
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
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Unit</th>

                                        <th scope="col">Category</th>
                                        <th scope="col">Purchase P</th>
                                        <th scope="col">Selling P</th>
                                        <th>Item ID</th>
                                        <th scope="col">Add</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in stock_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.id }}</td>
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
                                                    editMenuStock(data, i)
                                                "
                                                >{{ data.name }}</router-link
                                            >
                                        </td>
                                        <td>
                                            {{
                                                data.unit ? data.unit.name : ""
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                data.product_category
                                                    ? data.product_category.name
                                                    : ""
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.purchase_price
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.selling_price
                                                )
                                            }}
                                        </td>
                                        <td>{{ data.item_id }}</td>
                                        <td>
                                            <v-btn
                                                color="info"
                                                x-small
                                                @click="addEtims(data, i)"
                                                v-if="!data.item_id"
                                                >Add Etims</v-btn
                                            >
                                            <v-btn
                                                color="warning"
                                                x-small
                                                @click=" editMenuStock(data, i)"
                                               v-else
                                                >Update Etims</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="stock_data !== null"
                            v-bind:results="stock_data"
                            v-on:get-page="fetchProduct"
                        ></Pagination>
                        Items Count {{ stock_data.total }}

                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    type="xls"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="erp_stock.csv"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiFileExcel
                                    }}</v-icon>
                                    Export CSV
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
        <Modal v-model="add_modal" width="1200px">
            <AddEtimsStock
                v-if="add_modal"
                v-on:dashboard-active="setActiveRefresh"
                :details_data="details_data"
            ></AddEtimsStock
        ></Modal>

        <Modal v-model="update_modal" width="1200px">
            <UpdateEtimsStock
                v-if="update_modal"
                v-bind:details_data="details_data"
                v-on:update-active="setActiveRefresh"
            >
            </UpdateEtimsStock>
        </Modal>

        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";

import AddEtimsStock from "./AddEtimsStock.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import UpdateEtimsStock from "./UpdateEtimsStock.vue";
import Pagination from "../../../utilities/Pagination.vue";
import EtimStockNav from "./EtimStockNav.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_menu_item: false,
                edit_stock: false,
                stock_take_component: false,
                create_ingredients: false,
            },
            editIndex: null,
            details_data: "",
            update_modal: false,

            add_modal: false,
            stock_data: [],

            edit_data: null,

            stock_category_data: [],
            search: "",
            params: {
                name: "",
                description: "",
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
        AddEtimsStock,
        Unauthorized,
        UpdateEtimsStock,
        Pagination,
        EtimStockNav,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true,
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        },
    },
    methods: {
        addEtims(data, i) {
            this.editIndex = i;
            this.details_data = data;
            this.add_modal = true;
        },
        async apiTest() {
            const res = await this.callApi(
                "POST",
                "data/digitax_customer/sendApiRequest",
                ""
            );

            if (res.status === 200) {
            } else {
                this.swr("Server error try again later");
            }
        },

        async concurrentApiReq() {
            const res = await Promise.all([this.fetchProduct(1)]).then(
                function (results) {
                    return results;
                }
            );
        },

        editMenuStock(data, i) {
            this.details_data = data;
            this.editIndex = i;
            this.update_modal = true;
        },

        async fetchExcel() {
            const res = await this.getApi("data/stock/fetchAll", "");
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    id: data.id,
                    code: data.code,
                    name: data.product_name,
                    unit: data.unit.name,
                    category: data.product_category.name,

                    reorder_qty: data.reorder_qty,
                    purchase_price: data.purchase_price,
                    selling_price: data.selling_price,
                    wholesale_price: data.wholesale_price,
                    tax_rate: data.tax_dept.tax_rate,
                    tax_code: data.tax_dept.tax_code,
                    location: "Default",
                    qty: 0,
                    inventoryornon_inventory: data.inventory_type,
                    description: data.description,
                });
            });
            return data_array;
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/stock/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.stock_data = res.data;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function (data) {
            this.stock_data.data[this.editIndex] = data;
            this.add_modal = false;
            this.update_modal = false;
        },
        

        async fetchProduct(page) {
            this.showLoader();
            const res = await this.getApi("data/stock/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.stock_data = res.data;
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
</style>
