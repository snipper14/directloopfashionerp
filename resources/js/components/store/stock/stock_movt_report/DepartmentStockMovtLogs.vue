<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <stock-nav />
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h3><b>Department Stock Movement Logs</b></h3>
                        </div>
                        <div class="col-3">
                            <button
                                class="btn btn-primary btn-sm"
                                @click="$router.push('department_stock_movt_report')"
                            >
                                View Stock Movement
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex flex-row justify-content-around">
                                <div class="form-group">
                                    <label> From *</label
                                    ><date-picker
                                        v-model="from"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label> To *</label
                                    ><date-picker
                                        v-model="to"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group mr-4">
                                    <button
                                        class="btn btn-secondary btn-sm"
                                        @click="fetchMovt(1)"
                                    >
                                        Filter Date
                                    </button>
                                </div>
                                <div class="form-group">
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>

                                        <th scope="col">Selling P</th>
                                        <th scope="col">Opening Stock</th>
                                        <th scope="col">Qty  Issued</th>

                                      <th>system qty</th>
                                       <th>Physical qty</th>

                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in report_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.stock.code }}
                                        </td>
                                        <td>
                                            {{ data.stock.product_name }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.stock.selling_price
                                                )
                                            }}
                                        </td>
                                        <td>{{data.opening_dept_stock}}</td>
                                        <td>
                                            {{
                                                data.dept_issued_qty
                                            }}
                                        </td>
                                        <td>
                                            {{ data.dept_system_qty }}
                                        </td>

                                      <td>
                                          {{data.physical_dept_qty}}
                                      </td>

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
                            v-if="report_data !== null"
                            v-bind:results="report_data"
                            v-on:get-page="fetchMovt"
                        ></Pagination>
                        Items Count {{ report_data.total }}

                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchExcel"
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

                                <button
                                    class="btn btn-primary btn-sm"
                                    @click="downLoadPdf()"
                                >
                                    Download pdf
                                </button>
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
import Pagination from "../../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";

export default {
    data() {
        return {
            search: "",
            to: "",
            from: "",
            report_data: [],
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
        Pagination,

        Unauthorized
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        }
    },
    methods: {
        async downLoadPdf(issue_no) {
            this.showLoader();
            axios({
                url: "data/stock_movt/downloadDeptPdfLogs",
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                    to: this.to,
                    from: this.from
                },
                method: "GET",
                responseType: "blob" // important
            }).then(response => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    "stock movement" + this.getTimeStamp() + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock_movt/fetchMovtDeaprtmentLogs", {
                params: {
                    search: this.search.length >= 2 ? this.search : "",

                    to: this.to,
                    from: this.from
                }
            });
            this.hideLoader();
            var stock_array = res.data;
            for (var i = 0; i < stock_array.length; i++) {
                stock_array[i].stock = stock_array[i].stock
                    ? stock_array[i].stock.product_name
                    : "";
                stock_array[i].user = stock_array[i].user
                    ? stock_array[i].user.name
                    : "";
            }

            return stock_array;
        },

        async concurrentApiReq() {
            const res = await Promise.all([this.fetchMovt(1)]).then(function(
                results
            ) {
                return results;
            });
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/stock_movt/fetchMovtDeaprtmentLogs", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",

                    to: this.to,
                    from: this.from
                }
            });
            if (res.status === 200) {
                this.report_data = res.data;
            }
        },

        async fetchMovt(page) {
            this.showLoader();
            const res = await this.getApi("data/stock_movt/fetchMovtDeaprtmentLogs", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    to: this.to,
                    from: this.from
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.report_data = res.data;
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
