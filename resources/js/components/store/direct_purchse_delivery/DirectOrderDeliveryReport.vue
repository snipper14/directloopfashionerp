<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2">
                <DPNav />
            </div>
            <div class="col-md-10">
                <div class="container" v-if="isReadPermitted">
                    <div class="card" v-if="active.dashboard">
                        <div class="card-header">
                            <h3><b>Direct Order Receivable Report</b></h3>
                        </div>

                        <div class="card-body">
                            <div class="d-flex flex-row justify-content-around">
                                <div class="form-group">
                                    <label> From *</label
                                    ><date-picker
                                        v-model="params.from"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label> To *</label
                                    ><date-picker
                                        v-model="params.to"
                                        valueType="format"
                                    ></date-picker>
                                </div>

                                <div class="form-group">
                                    <label for="">Search</label>
                                    <input
                                        type="text"
                                        v-model="params.search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Delivery No</th>
                                            <th scope="col">Order No</th>
                                            <th>Stock</th>
                                            <th>Delivery Date</th>

                                            <th>User</th>
                                            <th scope="col">Opening Stock</th>
                                            <th scope="col">Qty Ordered</th>
                                            <th scope="col">Qty Delivered</th>
                                            <th scope="col">Purchase Price</th>
                                            <th>Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data,
                                            i) in receivable_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.delivery_no }}
                                            </td>
                                            <td>
                                                {{ data.order_no }}
                                            </td>
                                            <td>
                                                {{ data.stock.product_name }}
                                            </td>
                                            <td>{{ data.received_date }}</td>

                                            <td>
                                                {{
                                                    data.user
                                                        ? data.user.name
                                                        : ""
                                                }}
                                            </td>
                                            <td>{{ data.opening_stock }}</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.qty_ordered
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.qty_delivered
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.unit_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.sub_total
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination
                                v-if="receivable_data !== null"
                                v-bind:results="receivable_data"
                                v-on:get-page="fetchOrders"
                            ></Pagination>
                            Items Count {{ receivable_data.total }}
                            <div class="row">
                                <div
                                    class="col-6 d-flex"
                                    v-if="isDownloadPermitted"
                                >
                                    <export-excel
                                        v-if="isDownloadPermitted"
                                        class="btn btn-default btn-export ml-2 "
                                        :fetch="fetchExcel"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                        <button class="btn btn-primary btn-sm">
                                            Export Excel
                                        </button>
                                    </export-excel>

                                    <export-excel
                                        v-if="isDownloadPermitted"
                                        class="btn btn-default btn-export ml-2 "
                                        :fetch="fetchExcel"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="filename.xls"
                                    >
                                        <button class="btn btn-success btn-sm">
                                            Export CSV
                                        </button>
                                    </export-excel>
                                    <button
                                        v-if="isDownloadPermitted"
                                        @click="downLoadPdf()"
                                        class="btn btn-primary btn-sm"
                                    >
                                        Download pdf
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <center v-else>
                    <unauthorized />
                </center>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

import Pagination from "../../utilities/Pagination.vue";
import DPNav from "../direct_purchase/DPNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";

export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_delivery: false
            },
            order_details: null,
            params: {
                order_no: "",
                to: "",
                from: ""
            },
            receivable_data: []
        };
    },
    components: {
        Treeselect,
        DPNav,
        Pagination,
        Unauthorized
    },
    watch: {
        params: {
            handler: _.debounce(function() {
                this.fetchOrders(1);
            }, 500),
            deep: true
        }
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async downLoadPdf() {
            this.showLoader();
            axios({
                url: "data/direct_po_receivable/downloadDetailsPdf",
                params: {
                    ...this.params
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
                link.setAttribute("download", this.getTimeStamp() + ".pdf");
                document.body.appendChild(link);
                link.click();
            });
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchOrders(1)]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchExcel() {
            const res = await this.getApi(
                "data/direct_po_receivable/fetchDPOrderReceivable",
                {
                    params: {
                        ...this.params
                    }
                }
            );
            if (res.status === 200) {
                var array_item = res.data;
                for (var i = 0; i < array_item.length; i++) {
                    array_item[i].stock = array_item[i].stock
                        ? array_item[i].stock.code
                        : "";
                    array_item[i].stock = array_item[i].stock.product_name;
                    array_item[i].user = array_item[i].user.name;
                    array_item[i].qty_delivered = array_item[i].qty_delivered;
                    array_item[i].order_no = array_item[i].order_no;
                    array_item[i].delivery_no = array_item[i].delivery_no;
                    array_item[i].sub_total = array_item[i].sub_total;
                    array_item[i].unit_price = array_item[i].unit_price;
                }

                return array_item;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchOrders(page) {
            const res = await this.getApi(
                "data/direct_po_receivable/fetchDPOrderReceivable",
                {
                    params: {
                        page,
                        ...this.params
                    }
                }
            );
            if (res.status === 200) {
                this.receivable_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped>
.supp_dropdown {
    width: 40% !important;
}
</style>
