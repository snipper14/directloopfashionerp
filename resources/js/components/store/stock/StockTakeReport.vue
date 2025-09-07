<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <StockNav />
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
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
                        <div class="table-responsive ">
                            <table
                                class="table table-sm table-striped table-bordered"
                                id="content"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Product</th>
                                        <th scope="col">Qty</th>
                                        <th scope="col">Store</th>
                                        <th scope="col">StockTake Type</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Date</th>
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
                                            {{ cashFormatter(data.qty) }}
                                        </td>
                                        <td>{{ data.type }}</td>
                                        <td>
                                            {{ data.stock_take_type }}
                                        </td>
                                        <td>{{ data.user.name }}</td>

                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
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
                                    class="btn btn-default btn-export ml-2 "
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
                                    class="btn btn-default btn-export ml-2 "
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
    mdiFilePdfBoxOutline
} from "@mdi/js";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";
import StockNav from "../StockNav.vue";

export default {
    components: {
        Unauthorized,
        Pagination,
        StockNav
    },
    data() {
        return {
            search: "",
            params: {},
            isLoading: true,
            stocktake_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },

    mounted() {
        this.concurrentApiReq();
        //  this.createPDF();
    },
    watch: {
        search: {
            handler: _.debounce(function() {
                this.searchProducts();
            }, 500)
        }
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([this.fetchSplitReport(1)]).then(
                function(results) {
                    return results;
                }
            );
            this.hideLoader();
        },
        async exporExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock/fetchStockTake", {
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();
            if (res.status === 200) {
                var data_array = [];
                res.data.map(array_item => {
                    data_array.push({
                        product_code: array_item.stock.code,
                        product_name: array_item.stock.product_name,
                        qty: array_item.qty,
                        stock_take_type: array_item.stock_take_type,

                        date: this.formatDateTime(array_item.created_at),
                        user: array_item.user.name
                    });
                });
                return data_array;
            } else {
                this.servo();
            }
        },
        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/stock/fetchStockTake", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.stocktake_data = res.data;
            } else {
                this.servo();
            }
        },

        async fetchSplitReport(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/stock/fetchStockTake", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.isLoading ? this.hideLoader() : "";

            if (res.status === 200) {
                this.stocktake_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped>
.show-img {
    border-color: #ff6f00;
    background: #ff6f00;
}
</style>
