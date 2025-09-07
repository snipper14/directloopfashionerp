<template>
    <div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Etims Sales Report</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3"></div>

                        <div class="table-responsive custom-table">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th scope="col">Time</th>

                                        <th>ItaxID</th>
                                        <th>Trader InvoiceNO</th>
                                        <th>Receipt No</th>
                                        <th>Serial No</th>
                                        <th>Url</th>
                                        <th>Customer Name</th>
                                        <th>PIN</th>
                                        <th>Queue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.date }}
                                        </td>
                                        <td>{{ data.time }}</td>
                                        <td>{{ data.digitax_id }}</td>
                                        <td>
                                            {{ data.trader_invoice_number }}
                                        </td>
                                        <td>{{ data.receipt_number }}</td>
                                        <td>{{ data.serial_number }}</td>
                                        <td>{{ data.sale_detail_url }}</td>
                                        <td>{{ data.customer_name }}</td>
                                        <td>{{ data.customer_pin }}</td>
                                        <td>{{ data.queue_status }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <PaginationEtims
                            :pagination="sale_data"
                            @page-change="fetchData"
                            v-if="sale_data !== null"
                        ></PaginationEtims>
<br><br>
                        <div class="row">
                            <div class="col-md-8 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export CSV
                                    </button>
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import PaginationEtims from "../../utilities/PaginationEtims.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";

import "@riophae/vue-treeselect/dist/vue-treeselect.css";

export default {
    components: {
        Unauthorized,
        PaginationEtims,
    },
    data() {
        return {
            sale_details: null,
            receipt_details_modal: false,

            sale_data: null,

            isLoading: true,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },

    methods: {
        async fetchData(page) {
            this.showLoader();
            const res = await this.getApi(
                "data/etims_report/fetchDirectFromEtims",

                {
                    params: {
                        page,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            await Promise.all([this.fetchData(1)]);
        },

        async fetchExcel() {
            const res = await this.getApi(
                "data/etims_report/fetchDirectFromEtims",

                {
                    params: {
                        page: 1,
                    },
                }
            );
            if (res.status == 200) {
                var data_array = [];
                res.data.data.map((array_item) => {
                    data_array.push({
                        date: array_item.date,
                        time: array_item.time,
                        taxid: array_item.digitax_id,
                        trader_invoice_number: array_item.trader_invoice_number,
                        receipt_number: array_item.receipt_number,
                        serial_number: array_item.serial_number,
                        sale_detail_url: array_item.sale_detail_url,
                        customer_name: array_item.customer_name,
                        customer_pin: array_item.customer_pin,
                        queue_status: array_item.queue_status,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
