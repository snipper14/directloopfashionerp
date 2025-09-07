<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <branch-transfer-nav />
            </div>
            <div class="col-md-10" v-if="isWritePermitted">
                <div class="card">
                    <div class="card-header"><h3><b>Branch Stock Transfer Report</b></h3></div>

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
                        <div class="table-responsive custom-table">
                            <table
                                class="table table-sm table-striped table-bordered"
                                id="content"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Transfer Date</th>
                                        <th scope="col">Code</th>
                                        <th>Reference Code</th>
                                        <th scope="col">Source Location</th>
                                        <th>Dest. Branch</th>
                                        <th scope="col">User</th>
                                        <th>Receive Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in transfer_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewDetails(data)
                                                "
                                                >{{
                                                    data.transfer_date
                                                }}</router-link
                                            >
                                        </td>
                                        <td>{{ data.transfer_code }}</td>
                                        <td>{{ data.reference_code }}</td>
                                        <td>
                                            {{ data.department.department }}
                                        </td>
                                        <td>{{ data.branch_to.branch }}</td>
                                        <td>{{ data.user.name }}</td>
                                        <td>{{ data.receive_status }}</td>
                                        <td><v-btn color="danger" title="delete" @click="deleteBtn(data,i)" x-small v-if="data.receive_status=='pending' && isDeletePermitted" >delete</v-btn></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <pagination
                            v-if="transfer_data !== null"
                            v-bind:results="transfer_data"
                            v-on:get-page="fetch"
                        ></pagination>
                        Items Count {{ transfer_data.total }}
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
            <div v-else><unauthorized /></div>
        </div>
        <PrintBranchStockTransferTemplate
            v-if="show_print && print_data"
            ref="PrintBranchStockTransferTemplate"
            :print_data="print_data"
        />
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
    data() {
        return {
            show_print: false,
            transfer_data: [],
            print_data: null,
            params: {
                search: "",
                to:null,
                from:null
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
        async deleteBtn(value,i){
          const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/branch_stock_transfer/deleteTransfer" ,
                    value
                );

                if (res.status === 200) {
                    this.s("deleted ");
                   this.transfer_data.data.splice(i,1)
                } else {
                    this.swr("Server error try again later");
                }

            }
            },
        async viewDetails(data, i) {
            this.showLoader();
            const res = await this.getApi(
                "data/branch_stock_transfer/fetchDetails",
                {
                    params: {
                        transfer_code: data.transfer_code,
                    },
                }
            );
            this.hideLoader();
            this.print_data = res.data;
            this.show_print = true;

            setTimeout(() => {
                this.$refs.PrintBranchStockTransferTemplate.printBill();
                this.show_print = false;
            }, 1000);
        },
        async exporExcel() {
            const res = await this.getApi(
                "data/branch_stock_transfer/fetchAll",
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
                        branch_to: data.branch_to.branch,
                         source_branch: data.branch.branch,
                        
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetch(page) {
            const res = await this.getApi("data/branch_stock_transfer/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });

            if (res.status == 200) {
                this.transfer_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq(page) {
            this.showLoader();
            await Promise.all([this.fetch(page)]);
            this.hideLoader();
        },
    },
};
</script>
