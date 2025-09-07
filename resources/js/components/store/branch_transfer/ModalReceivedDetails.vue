<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>
                                <b>Branch Stock Receivable Details Report</b>
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="d-flex flex-row justify-content-around">
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

                            <div class="table-responsive custom-table">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                    id="content"
                                >
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Item Code</th>
                                            <th scope="col">Transfer Date</th>
                                            <th scope="col">Transfer Code</th>
                                            <th>Reference Code</th>
                                            <th scope="col">Source Location</th>
                                            <th>Dest. Branch</th>
                                            <th>Location Received</th>
                                            <th scope="col">Issuer</th>
                                            <th>Received Date</th>
                                            <th>Transfered Qty</th>
                                            <th>Received Qty</th>
                                            <th>Receiver</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in receivable_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.stock.product_name }}
                                            </td>
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td>
                                                {{ data.transfer_date }}
                                            </td>
                                            <td>{{ data.transfer_code }}</td>
                                            <td>{{ data.reference_code }}</td>

                                            <td>{{ data.branch.branch }}</td>
                                            <td>{{ data.branch_to.branch }}</td>
                                            <td>
                                                {{
                                                    data.received_department
                                                        .department
                                                }}
                                            </td>
                                            <td>{{ data.user.name }}</td>
                                            <td>{{ data.received_date }}</td>
                                            <td>{{data.qty}}</td>
                                            <td>{{ data.received_qty }}</td>
                                            <td>{{ data.receiver.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-4 d-flex">
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="exporExcel"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                        <v-btn color="success" x-small>
                                            Export Excel</v-btn
                                        >
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="exporExcel"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="filename.xls"
                                    >
                                        <v-btn color="primary" x-small>
                                            Export CSV</v-btn
                                        >
                                    </export-excel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </v-app>
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import BranchTransferNav from "./BranchTransferNav.vue";
import PrintBranchStockTransferTemplate from "./PrintBranchStockTransferTemplate.vue";

export default {
    components: {
        BranchTransferNav,
        Unauthorized,
        Pagination,
        PrintBranchStockTransferTemplate,
    },
    props: ["details_data"],
    data() {
        return {
            show_print: false,
            receivable_data: [],
            print_data: null,
            params: {
                search: "",
                r_from: null,
                r_to: null,
                transfer_code: this.details_data.transfer_code,
            },
        };
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    mounted() {
        this.concurrentApiReq(1);
    },
    methods: {
        async exporExcel() {
            const res = await this.getApi(
                "data/branch_stock_transfer/viewDetails",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status == 200) {
                const data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        id: data.id,

                        transfer_code: data.transfer_code,
                        reference_code: data.reference_code,
                        transfer_date: data.transfer_date,
                        receive_status: data.receive_status,
                        stock: data.stock.name,
                        code: data.stock.code,
                        qty: data.qty,

                        note: data.note,
                        source_branch: data.branch.branch,
                        branch_to: data.branch_to.branch,
                        received_department:
                            data.received_department.department,

                        received_date: data.received_date,
                        received_qty: data.received_qty,
                        receiver: data.receiver.name,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async viewDetails() {
            const res = await this.getApi(
                "data/branch_stock_transfer/viewDetails",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status == 200) {
                this.receivable_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq(page) {
            this.showLoader();
            await Promise.all([this.viewDetails()]);
            this.hideLoader();
        },
    },
};
</script>
