<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <branch-transfer-nav />
            </div>
            <div class="col-md-10" v-if="isDisplayPermitted">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Inter-Branch Stock Transfer</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="">Items Location*</label>
                                <Select v-model="form_data.department_id">
                                    <Option
                                        v-for="(value, i) in dept_data"
                                        :key="i"
                                        :value="value.id"
                                    >
                                        {{ value.department }}
                                    </Option>
                                </Select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Transfer To Branch*</label>
                                <Select v-model="form_data.branch_to_id">
                                    <Option
                                        v-for="(value, i) in branch_select_data"
                                        :key="i"
                                        :value="value.id"
                                    >
                                        {{ value.branch }}
                                    </Option>
                                </Select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Reference No (Optional)</label>

                                <input
                                    type="text"
                                    v-model="form_data.reference_code"
                                    class="form-control"
                                />
                            </div>

                            <div class="col-md-3 form-group d-flex flex-column">
                                <label> Transfer Date *</label
                                ><date-picker
                                    v-model="form_data.transfer_date"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div
                                class="col-md-4 form-group"
                                style="paddin-top: 0px"
                            >
                                <label for="">Note</label>

                                <textarea
                                    type="text"
                                    v-model="form_data.note"
                                    class="form-control"
                                ></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <label for="">Search Item</label>
                                <input
                                    type="text"
                                    v-model="search"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <v-btn
                                    class="mt-6"
                                    color="info"
                                    @click="search_results = []"
                                    >Clear</v-btn
                                >
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Item Name</th>

                                            <th>Tranfer Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(value, i) in search_results"
                                            :key="i"
                                        >
                                            <td>
                                                {{ value.product_name }}
                                            </td>

                                            <td>
                                                <input
                                                    class="form-control"
                                                    style="min-width: 4rem"
                                                    type="text"
                                                    v-model="value.qty"
                                                />
                                            </td>

                                            <td>
                                                <v-btn
                                                    color="primary"
                                                    @click="
                                                        addTransfer(value, i)
                                                    "
                                                >
                                                    Add
                                                </v-btn>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-dark">
                                <thead>
                                    <tr>
                                        <th scope="col">CODE</th>
                                        <th scope="col">Item</th>
                                        <th scope="col">qty</th>
                                        <th scope="col">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(value, i) in transfer_data"
                                        :key="i"
                                    >
                                        <th scope="row">
                                            {{ value.stock.code }}
                                        </th>
                                        <th scope="row">
                                            {{ value.stock.product_name }}
                                        </th>
                                        <td>{{ value.qty }}</td>
                                        <td>
                                            <v-btn color="danger" @click="deleteInfo(value,i)" x-small
                                                >Delete</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <v-btn
                                v-if="transfer_data.length > 0"
                                @click="completeTransfer()"
                                color="primary"
                                >Complete Transfer</v-btn
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
        <PrintBranchStockTransferTemplate
            v-if="show_print"
            ref="PrintBranchStockTransferTemplate"
            :print_data="print_data"
        />
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import BranchTransferNav from "./BranchTransferNav.vue";
import PrintBranchStockTransferTemplate from "./PrintBranchStockTransferTemplate.vue";
export default {
    components: {
        BranchTransferNav,
        PrintBranchStockTransferTemplate,
        Unauthorized,
    },
    data() {
        return {
            branch_select_data: null,
            dept_data: null,
            transfer_data: [],
            search: "",
            search_results: [],
            print_data: null,
            show_print: false,
            form_data: {
                stock_id: null,
                department_id: null,
                branch_to_id: null,
                note: "",
                qty: 1,
                transfer_date: null,
                reference_code: "",
            },
        };
    },
    mounted() {
        this.form_data.transfer_date = this.getCurrentDate();
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
        async deleteInfo(value,i){
              const res = await this.callApi(
                "POST",
                "data/branch_stock_transfer/destroy",
                value
            );
            if(res.status==200){
                this.transfer_data.splice(i,1)
            }
          
        },
        async completeTransfer() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/branch_stock_transfer/completeTransfer",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Saved");
                this.transfer_data = [];
                this.print_data = res.data;
                this.show_print = true;
                setTimeout(() => {
                    this.$refs.PrintBranchStockTransferTemplate.printBill();
                    this.show_print = false;
                    this.$router.push("branch_transfer_report");
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        async addTransfer(data) {
            this.form_data.stock_id = data.stock_id;
            this.form_data.qty = data.qty;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/branch_stock_transfer/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Saved");
                this.transfer_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async searchProduct() {
            const res = await this.getApi("data/inventory/searchItemChunk", {
                params: {
                    search: this.search,
                },
            });

            this.search_results = this.modifyResData(res.data);
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    stock_id: obj.stock_id,
                    product_name: obj.stock.name,
                    qty: 1,

                    code: obj.stock.code,

                    unit: obj.stock.unit.name,
                };
            });
            return modif;
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchbranch() {
            const res = await this.getApi("data/branch/fetch", "");

            if (res.status == 200) {
                this.branch_select_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchPendingTransfer() {
            const res = await this.getApi(
                "data/branch_stock_transfer/fetchPending",
                ""
            );

            if (res.status == 200) {
                this.transfer_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchbranch(),
                this.fetchDept(),
                this.fetchPendingTransfer(),
            ]);
            this.hideLoader();
        },
    },
};
</script>
