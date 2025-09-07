<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h3><b>Department Stock Movement</b></h3>
                        </div>
                        <div class="col-3">
                            <button
                                class="btn btn-primary btn-sm"
                                @click="
                                    $router.push('department_stock_movt_logs')
                                "
                            >
                                View Dept Stock Movement Logs
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex flex-row justify-content-around">
                                <div class="form-group">
                                    <label> Date *</label
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

                                        <th scope="col">Selling P</th>
                                        <th scope="col">Opening Stock</th>
                                        <th scope="col">T.Issued</th>

                                        <th scope="col">T.Balance</th>
                                        <th>Sold Items</th>
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
                                                    data.selling_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.opening_dept_stock
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_dept_issued_qty
                                                )
                                            }}
                                        </td>

                                        <td>{{ data.total_stock }}</td>
                                        <!-- total sold -->
                                        <td>
                                            {{ data.total_sold }}
                                        </td>

                                        <!-- system stock -->
                                        <td>{{ data.dept_system_qty }}</td>

                                        <!-- physical stock -->
                                        <td>
                                            <input
                                                type="number"
                                                v-model="data.physical_dept_qty"
                                            />
                                        </td>

                                        <td>
                                            {{
                                                data.physical_dept_qty -
                                                data.dept_system_qty
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

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
                                    Print Details
                                </button>
                                <button
                                    class="btn btn-warning btn-sm ml-2"
                                    v-if="update_status"
                                     :disabled='isUpdateBtnDisabled'
                                    @click="updateDeptPhysicalStock()"
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
                <print-dept-stock-movt
                    ref="PrintDeptStockMovt"
                    :report_date="report_date"
                    :report_data="report_data"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <PrintDeptStockTakeSheet
                    v-if="display_printer"
                    ref="PrintDeptStockTakeSheet"
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
import PrintDeptStockMovt from "./PrintDeptStockMovt.vue";
import PrintDeptStockTakeSheet from "./PrintDeptStockTakeSheet.vue";

export default {
    data() {
        return {
            isUpdateBtnDisabled:false,
            search: "",
            to: "",
            from: "",
            yesterday_date: null,
            opening_stock_timestamp: null,
            closing_stock_timestamp: null,
            update_status: true,
            category_data: null,
            display_printer: false,
            report_date: null,
            report_data: [],
            index: -1,
            department_data: null,
            department_id: null,
            display: false,
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
        PrintDeptStockMovt,
        PrintDeptStockTakeSheet,
    },
    mounted() {
        this.report_date = this.getCurrentDate();
        // this.fetchMovtStatus();
        // this.concurrentApiReq();
    },
    watch: {
       
        // search: {
        //     deep: true,
        //     handler: _.debounce(function (val, old) {
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
    methods: {
        async stockTakeSheet() {
            this.showLoader();
            const res = await this.getApi(
                "data/stockmvt/fetchBlankStockSheet",
                {
                    params: {
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
                    this.$refs.PrintDeptStockTakeSheet.printDoc();
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
        async fetchMovtStatus() {
            const res = await this.getApi("data/stockmvt/fetchMovtStatus", {
                params: {
                    report_date: this.report_date,
                },
            });

            if (res.status == 200) {
                this.update_status = res.data.status;
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
        async updateDeptPhysicalStock() {
            const proceed = await this.confirmDialogue(
                "Are you sure !!!?, this operation  updates qty of all the stock shown"
            );

            if (proceed) {
                if (this.report_data.length > 0) {
                    this.showLoader();
                    this.isUpdateBtnDisabled=true
                    const res = await this.callApi(
                        "post",
                        "data/stock_movt/updateDeptPhysicalStock",
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
                this.$refs.PrintDeptStockMovt.printBill();
            }, 1000);
        },
        async fetchExcel() {
            return this.report_data;
        },

        async concurrentApiReq() {
            const res = await Promise.all([
                this.fetchMovt(1),
                this.fetchMovtStatus(),
            ]).then(function (results) {
                return results;
            });
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
                this.errorNotif("You can't select today or future date!!");
                return;
            }
            if (!this.report_date) {
                this.errorNotif("Select date?");
                return;
            }
            this.opening_stock_timestamp = this.computeOpeningStockTimeStamp(
                this.report_date
            );
           this.closing_stock_timestamp= this.computeClosingStockTimeStamp(   this.report_date)

            this.showLoader();
            const res = await this.getApi("data/stockmvt/fetchDeptStockMovt", {
                params: {
                    report_date: this.report_date,
                    opening_stock_timestamp: this.opening_stock_timestamp,
                    closing_stock_timestamp:this.closing_stock_timestamp,
                    search: this.search,
                    yesterday_date: this.yesterday_date,
                    department_id: this.department_id,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.report_data = res.data; //this.modifyRec(res.data);
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        modifyRec(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    stock_id: obj.stock_id,
                    code: obj.code,

                    product_name: obj.product_name,
                    selling_price: obj.selling_price,

                    opening_dept_stock: obj.opening_dept_stock,
                    sum_dept_issued_qty: obj.sum_dept_issued_qty,

                    total_stock: obj.total_stock,
                    total_sold: obj.total_sold,
                    dept_system_qty: obj.dept_system_qty,

                    physical_dept_qty: obj.physical_dept_qty,
                    deficit: 0,
                };
            });
            return modif;
        },
        computeOpeningStockTimeStamp(report_date) {
           
            const startDate =  this.$moment(report_date + " 00:00");
           
           var beginDate = this.formatDateTime(
                this.$moment(startDate).add(6, "hours")
            );
           
           return beginDate
        },
           computeClosingStockTimeStamp(report_date) {
          
            const closingDate =  this.$moment(report_date + " 00:00");
           
           var nextDate = this.formatDateTime(
                this.$moment(closingDate).add(1, "days")
            );
            console.log("next date "+nextDate );
              var endDate = this.formatDateTime(
                this.$moment(nextDate).add(6, "hours")
            );
            
          return endDate
        },
    },
};
</script>
<style scoped>
.show-img {
    border-color: #ff6f00;
    background: #ff6f00;
}
</style>
