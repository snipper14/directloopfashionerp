<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <branch-transfer-nav />
            </div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Branch Stock Receivable Report</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    v-model="params.r_from"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    v-model="params.r_to"
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

                        <div class="table-responsive custom-table">
                            <table
                                class="table table-sm table-striped table-bordered"
                                id="content"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Transfer Date</th>
                                        <th scope="col">Transfer Code</th>
                                        <th>Reference Code</th>
                                        <th scope="col">Source Location</th>
                                        <th>Dest. Branch</th>

                                        <th scope="col">Issuer</th>
                                        <th>Received Date</th>

                                        <th>Receiver</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in receivable_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.transfer_date }}
                                        </td>
                                        <td>{{ data.transfer_code }}</td>
                                        <td>{{ data.reference_code }}</td>

                                        <td>{{ data.branch.branch }}</td>
                                        <td>{{ data.branch_to.branch }}</td>

                                        <td>{{ data.user.name }}</td>
                                        <td>{{ data.received_date }}</td>

                                        <td>{{ data.receiver.name }}</td>
                                        <td>
                                            <v-btn
                                                x-small
                                                color="primary"
                                                @click="viewDetails(data)"
                                                >view Details</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <pagination
                            v-if="receivable_data !== null"
                            v-bind:results="receivable_data"
                            v-on:get-page="fetch"
                        ></pagination>
                        Items Count {{ receivable_data.total }}
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
        <PrintBranchStockTransferTemplate
            v-if="show_print && print_data"
            ref="PrintBranchStockTransferTemplate"
            :print_data="print_data"
        />
        <Modal v-model="details_modal" width="1200px">
            <modal-received-details
                v-if="details_modal"
                :details_data="details_data"
            />
        </Modal>
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import BranchTransferNav from "./BranchTransferNav.vue";
import ModalReceivedDetails from "./ModalReceivedDetails.vue";
import PrintBranchStockTransferTemplate from "./PrintBranchStockTransferTemplate.vue";
export default {
    components: {
        BranchTransferNav,
        Unauthorized,
        Pagination,
        PrintBranchStockTransferTemplate,
        ModalReceivedDetails,
    },
    data() {
        return {
            show_print: false,
            receivable_data: [],
            print_data: null,
            details_modal: false,
            details_data: null,
            params: {
                search: "",
                r_from: null,
                r_to: null,
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
        viewDetails(data) {
            this.details_data = data;
            this.details_modal = true;
        },
        async exporExcel() {
            const res = await this.getApi(
                "data/branch_stock_transfer/fetchReceived",
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
        async fetchReceived(page) {
            const res = await this.getApi(
                "data/branch_stock_transfer/fetchReceived",
                {
                    params: {
                        page,
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
            await Promise.all([this.fetchReceived(page)]);
            this.hideLoader();
        },
    },
};
</script>
