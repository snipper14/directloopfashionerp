<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <!-- <stock-nav /> -->
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h3><b>Stock Movement</b></h3>
                        </div>
                        <div class="col-3">
                            <button
                                class="btn btn-primary btn-sm"
                                @click="$router.push('stock_movement_logs')"
                            >
                                View Stock Movement Logs
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex flex-row justify-content-around">
                                <div class="form-group">
                                    <label> Select Date *</label
                                    ><date-picker
                                        v-model="report_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>

                                <div class="form-group mr-4">
                                    <button
                                        class="btn btn-secondary btn-sm"
                                        @click="concurrentApiReq()"
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
                                <div class="form-group ml-3">
                                    <label for=""> Filter Dept</label>

                                    <treeselect
                                        style="width: 200px"
                                        :load-options="fetchDept"
                                        :options="department_data"
                                        :auto-load-root-options="false"
                                        v-model="department_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select "
                                    />
                                </div>
                                <div class="ml-3">
                                    <button
                                        class="btn btn-info btn-sm"
                                        @click="stockTakeSheet()"
                                    >
                                        Print Stock Take Sheet
                                    </button>
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

                                        <th scope="col">Purchase P</th>
                                        <th scope="col">Opening Stock</th>
                                        <th scope="col">Added GRN</th>
                                        <th>T.T Stock</th>
                                        <th scope="col">T.Issue Qty</th>
                                        <th scope="col">T.Balance</th>
                                        <th scope="col">System Stock</th>
                                        <th scope="col">Physical Stock</th>
                                        <th scope="col">Deficit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in filteredRows"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.code }}
                                        </td>
                                        <td>
                                            {{ data.product_name }}
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
                                                    data.opening_stock
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.sum_grn_qty)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.total_stock)
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_issued_qty
                                                )
                                            }}
                                        </td>
                                        <!-- stock balance -->
                                        <td>
                                            {{ data.total_balance }}
                                        </td>

                                        <!-- system stock -->
                                        <td>{{ data.system_stock }}</td>
                                        <!-- physical stock -->
                                        <td>
                                            <input
                                                type="number"
                                                v-model="data.physical_qty"
                                            />
                                        </td>
                                        <td>
                                            {{
                                                data.physical_qty -
                                                data.system_stock
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- <Pagination
                                  v-if="report_data !== null"
                                  v-bind:results="report_data"
                                  v-on:get-page="fetchMovt"
                              ></Pagination>
                              Items Count {{ report_data.total }} -->

                        <div class="row">
                            <div
                                class="col-12 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
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
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    v-if="isDownloadPermitted"
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
                                    Print Document
                                </button>
                                <button
                                    class="btn btn-warning btn-sm mr-l"
                                    v-if="update_status"
                                     :disabled='isUpdateBtnDisabled'
                                    @click="updatePhysicalStock()"
                                >
                                    Update Physical Stock
                                </button>
                                <button
                                    v-else
                                    class="btn btn-warning btn-sm ml-2"
                                    disabled
                                >
                                    You have updated Stock
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
        <div class="row">
            <div class="col-12">
                <print-stock-movt
                    ref="PrintStockMovt"
                    :report_date="report_date"
                    :report_data="report_data"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <PrintStoreStockTakeSheet
                    v-if="display_printer"
                    ref="PrintStoreStockTakeSheet"
                    :category_data="category_data"
                    :report_date="report_date"
                />
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import PrintStockMovt from "./PrintStockMovt.vue";
import PrintStoreStockTakeSheet from "./PrintStoreStockTakeSheet.vue";

export default {
    data() {
        return {
             isUpdateBtnDisabled:false,
            display_printer: false,
            report_date: null,
            yesterday_date: null,
            department_data: null,
            update_status: true,
            isLoading: true,
            search: "",
            to: "",
            from: "",
            report_data: [],

            department_id: null,
            params: {
                name: "",
                description: "",
            },
            form_data: {
                physical_stock: 0,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
        Treeselect,
        Unauthorized,
        PrintStockMovt,
        PrintStoreStockTakeSheet,
    },
    mounted() {
        this.to = this.getCurrentDate();
        this.from = this.getCurrentDate();
        this.report_date = this.getCurrentDate();
        //  this.fetchMovtStatus();
         this.concurrentApiReq();
    },
    watch: {
        // search: {
        //     deep: true,
        //     handler: _.debounce(function (val, old) {
        //         this.isLoading = false;
        //         this.concurrentApiReq();
        //     }, 500),
        // },
        department_id: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async fetchMovtStatus() {
            const res = await this.getApi(
                "data/mx_stock_movt/fetchMovtStoreStatus",

                {
                    params: {
                        report_date: this.report_date,
                    },
                }
            );

            if (res.status == 200) {
                this.update_status = res.data.status;
            } else {
                this.swr("Server error occurred");
            }
        },
        async stockTakeSheet() {
            this.showLoader();
            const res = await this.getApi(
                "data/stockmvt/fetchBlankStockSheet",
                {
                    params: {
                        report_date: this.report_date,
                        search: this.search,
                        department_id: this.department_id,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.display_printer = true;
                this.category_data = res.data;
                this.department_id = null;
                setTimeout(() => {
                    this.$refs.PrintStoreStockTakeSheet.printDoc();
                }, 1000);
            } else {
                this.servo();
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.department_data = this.modifyDept(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyDept(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.department,
                };
            });
            return modif;
        },
        async updatePhysicalStock() {
            const proceed = await this.confirmDialogue(
                "Are you sure !!!?, this operation  updates qty of all the stock shown"
            );

            if (proceed) {
                if (this.report_data.length > 0) {
                    this.showLoader();
                     this.isUpdateBtnDisabled=true
                    const res = await this.callApi(
                        "post",
                        "data/mx_stock_movt/updatePhysicalStock",
                        {
                            update_data: this.report_data,

                            report_date: this.report_date,
                        }
                    );
                    this.hideLoader();
                    if (res.status == 200) {
                        this.successNotif("Updated successfully");
                        this.concurrentApiReq();
                    } else {
                         this.isUpdateBtnDisabled=false
                        this.servo();
                    }
                }
            }
        },
        async downLoadPdf() {
            setTimeout(() => {
                this.$refs.PrintStockMovt.printBill();
            }, 1000);
        },

        async fetchExcel() {
            var stock_array = this.report_data;

            return stock_array;
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            const res = await Promise.all([
                this.fetchMovt(1),
              //  this.fetchMovtStatus(),
            ]).then(function (results) {
                
                return results;
            });
console.log('>>>>>>>>');
            this.isLoading ? this.hideLoader() : "";
        },

        async fetchMovt(page) {
            this.yesterday_date = this.$moment();
            this.yesterday_date = this.$moment(this.report_date).subtract(
                1,
                "days"
            );
            this.yesterday_date = this.yesterday_date.format("YYYY-MM-DD");

            var is_future_date = this.$moment(this.report_date).diff(
                this.$moment(this.getCurrentDate()),
                "days"
            );

            if (is_future_date >= 1) {
                this.errorNotif("You can't select future date!!");
                return;
            }
            if (!this.report_date) {
                this.errorNotif("Select date?");
                return;
            }
           
            const res = await this.getApi("data/stockmvt/fetchDeptStockMovt", {
                params: {
                    report_date: this.report_date,
                    search: this.search,
                    yesterday_date: this.yesterday_date,

                    department_id: this.department_id,
                },
            });
            

            if (res.status === 200) {
                this.report_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
    computed: {
    filteredRows() {
      return this.report_data.filter(report_data => {
        const name = report_data.product_name.toString().toLowerCase();
        const code = report_data.code.toString().toLowerCase();
        const searchTerm = this.search.toLowerCase();

        return (
          name.includes(searchTerm) ||  code.includes(searchTerm) 
        );
      });
    }
  },
};
</script>
<style scoped>
.show-img {
    border-color: #ff6f00;
    background: #ff6f00;
}
</style>
