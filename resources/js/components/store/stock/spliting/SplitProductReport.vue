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
                                        <th scope="col">Main Product</th>
                                        <th scope="col">Main Product Qty</th>
                                        <th scope="col">Sub Product</th>
                                        <th scope="col">Final Product Qty</th>
                                        <th scope="col">Split Metric</th>
                                        <th scope="col">Date operation</th>
                                        <th scope="col">User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data,
                                        i) in split_product_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.original_product.code }}
                                        </td>

                                        <td>
                                            {{
                                                data.original_product
                                                    .product_name
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.qty_split) }}
                                        </td>
                                        <td>
                                            {{
                                                data.final_product.product_name
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.final_qty) }}
                                        </td>
                                        <td>
                                            1/{{
                                                data.split_product_setting
                                                    .final_qty
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.user.name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="split_product_data !== null"
                            v-bind:results="split_product_data"
                            v-on:get-page="fetchSplitReport"
                        ></Pagination>
                        Items Count {{ split_product_data.total }}

                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="exportSplit"
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
                                    :fetch="exportSplit"
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
import StockNav from "../../StockNav.vue";
import Pagination from "../../../utilities/Pagination.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";
export default {
    data() {
        return {
            search: "",
            params: {},
            isLoading: true,
            split_product_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        StockNav,
        Pagination,
        Unauthorized,
        jsPDF,
        html2canvas
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
        async exportSplit() {
            this.showLoader();
            const res = await this.getApi("data/split_product/fetch", {
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
                        product_code: array_item.original_product.code,
                        original_product_name:
                            array_item.original_product.product_name,
                        qty_split: array_item.qty_split,
                        final_product_code: array_item.final_product.code,
                        final_product: array_item.final_product.product_name,
                        final_qty: array_item.final_qty,

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
            const res = await this.getApi("data/split_product/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.split_product_data = res.data;
            } else {
                this.servo();
            }
        },

        async fetchSplitReport(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/split_product/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.isLoading ? this.hideLoader() : "";

            if (res.status === 200) {
                this.split_product_data = res.data;
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
