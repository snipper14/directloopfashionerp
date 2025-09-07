<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2">
                <inventory-nav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                    <div class="row">
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                          <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter From Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="from_time"
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter To Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="to_time"
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                                <v-btn
                                    x-small
                                    color="info"
                                    @click="concurrentApiReq()"
                                    >Filter Date $ Time</v-btn
                                >
                    </div></div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                                id="content"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Product</th>
                                        <th scope="col">Dept</th>
                                            <th>method</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Original Qty</th>
                                        <th scope="col">
                                            Updated Qty<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_qty
                                                "
                                                sort-key="sort_qty"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Qty Variation<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_variation_qty
                                                "
                                                sort-key="sort_variation_qty"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            P.P<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_purchase_price
                                                "
                                                sort-key="sort_purchase_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            S.P<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_selling_price
                                                "
                                                sort-key="sort_selling_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            Variation on P.P<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_variation_purchase_price
                                                "
                                                sort-key="sort_variation_purchase_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Variation on S.P<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_variation_selling_price
                                                "
                                                sort-key="sort_variation_selling_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                    
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in stocktake_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.stock.code }}
                                        </td>

                                        <td>
                                            {{ data.stock.product_name }}
                                        </td>

                                        <td>
                                            {{ data.department.department }}
                                        </td>
                                         <th>{{ data.type }}</th>

                                        <td>{{ data.user.name }}</td>

                                        <td>
                                            {{
                                                formatMonthDateTime(
                                                    data.created_at
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.original_qty)
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.qty) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.variation_qty
                                                )
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
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.variation_purchase_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.variation_selling_price
                                                )
                                            }}
                                        </td>
                                       
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="stocktake_data !== null"
                            v-bind:results="stocktake_data"
                            v-on:get-page="fetchSplitReport"
                        ></Pagination>
                        Items Count {{ stocktake_data.total }}

                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exporExcel"
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
                                    :fetch="exporExcel"
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
                    </div>
                </div>

                <center v-else>
                    <unauthorized />
                </center>
            </div>
        </div>

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
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";

import StockUpdateNav from "./StockUpdateNav.vue";
import InventoryNav from "../../inventory/InventoryNav.vue";
import SortButtons from "../../utilities/SortButtons.vue";

export default {
    components: {
        Unauthorized,
        Pagination,

        StockUpdateNav,
        InventoryNav,
        SortButtons,
    },
    data() {
        return {
            search: "",
            params: {},
            isLoading: true,
            stocktake_data: [],
            sort_params: {
                sort_qty: "",
                sort_variation_qty: "",
                sort_purchase_price: "",
                sort_selling_price: "",
                sort_variation_selling_price:"",
                sort_variation_purchase_price:""
            },
            from_time:null,
            to_time:null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },

    mounted() {
        this.concurrentApiReq();
        //  this.createPDF();
    },
    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.searchProducts();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetchSplitReport(1)]);
            this.isLoading ? this.hideLoader() : "";
        },
        async exporExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock_update/fetch", {
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        product_code: data.stock.code,
                        product_name: data.stock.product_name,
                        original_qty: data.original_qty,
                        qty: data.qty,
                        variation_qty: data.variation_qty,
                        purchase_price: data.purchase_price,
                        selling_price: data.selling_price,
                        variation_purchase_price: data.variation_purchase_price,
                        variation_selling_price: data.variation_selling_price,
                        type: data.type,
                        date: this.formatDateTime(data.created_at),
                        user: data.user.name,
                    });
                });
                return data_array;
            } else {
                this.servo();
            }
        },
        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/stock_update/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.stocktake_data = res.data;
            } else {
                this.servo();
            }
        },

        async fetchSplitReport(page) {
            const res = await this.getApi("data/stock_update/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                    ...this.sort_params,
                    from_time: this.formatDateTime(this.from_time),
                        to_time: this.formatDateTime(this.to_time),
                },
            });

            if (res.status === 200) {
                this.stocktake_data = res.data;
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
</style>
