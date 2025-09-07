<template>
    <div class="row">
        <div class="col-md-2"><POSReportNav /></div>
        <div class="col-md-10" v-if="isReadPermitted">
            <div class="card">
                <div class="card-header">
                    <h3><b>All Sales Expandable Report</b></h3>
                </div>   <div class="card-body">
                <div class="row">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="datePicker" class="form-label"
                                >Filter From Date:</label
                            >
                            <div class="input-group date" id="datepicker">
                                <input
                                    type="datetime-local"
                                    class="form-control"
                                    id="datePicker"
                                    v-model="from"
                                    placeholder="Select a date"
                                />
                                <span class="input-group-text">
                                    <i class="bi bi-calendar"></i>
                                    <!-- Bootstrap Icon (optional) -->
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="datePicker" class="form-label"
                                >Filter To Date:</label
                            >
                            <div class="input-group date" id="datepicker">
                                <input
                                    type="datetime-local"
                                    class="form-control"
                                    id="datePicker"
                                    v-model="to"
                                    placeholder="Select a date"
                                />
                                <span class="input-group-text">
                                    <i class="bi bi-calendar"></i>
                                    <!-- Bootstrap Icon (optional) -->
                                </span>
                            </div>
                            <v-btn x-small color="info" @click="filterDate()"
                                >Filter Date</v-btn
                            >
                            <v-btn
                                x-small
                                color="secondary"
                                @click="fetchAllData()"
                                >Reset Date Filter</v-btn
                            >
                        </div>
                          <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter From Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        
                                        v-model="from_time"
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter To Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="to_time"
                                        
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                                <v-btn
                                    x-small
                                    color="info"
                                    @click="filterDate()"
                                    >Filter Date $ Time</v-btn
                                >
                                <v-btn
                                    x-small
                                    color="secondary"
                                    @click="fetchAllData()"
                                    >Reset Datetime Filter</v-btn
                                >
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Search As Seller</label>
                                <treeselect
                                    :load-options="fetchWaiter"
                                    :options="waiter_select_data"
                                    :auto-load-root-options="false"
                                    v-model="search_params.user_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select User"
                                />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Search As Cashier</label>
                                <treeselect
                                    :load-options="fetchWaiter"
                                    :options="waiter_select_data"
                                    :auto-load-root-options="false"
                                    v-model="search_params.cashier_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select Cashier"
                                />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Search..</label>
                                <input
                                    type="text"
                                    v-model="search"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="">Branch Filter</label>
                            <treeselect
                                :load-options="fetchBranch"
                                :options="branch_select_data"
                                :auto-load-root-options="false"
                                v-model="search_params.branch_id"
                                :multiple="false"
                                :show-count="true"
                                placeholder="Select   Branch "
                            />
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="">Report Type</label>
                            <select
                                v-model="search_params.report_type"
                                class="form-control"
                            >
                                <option value="">Select Report Type</option>
                                <option
                                    v-for="(type, index) in reportTypes"
                                    :key="index"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div
                        class="table-responsive"
                        style="overflow-x: auto; white-space: nowrap"
                    >
                        <b-table
                            :items="ordersWithDetails"
                            :fields="fields"
                            small
                            striped
                            responsive
                            class="custom-table"
                        >
                            <template #cell(details)="row">
                                <v-btn
                                    x-small
                                    color="success"
                                    @click.stop="toggleAllDetails"
                                    class="mr-2"
                                >
                                    {{ allDetailsVisible ? "Hide " : "Show " }}
                                </v-btn>
                            </template>

                            <template #row-details="row">
                                <b-table
                                    :items="row.item.details"
                                    :fields="detailFields"
                                    small
                                    striped
                                    responsive
                                    class="details-table"
                                >
                                    <template #cell(stock)="detailRow">
                                        {{ detailRow.item.stock.product_name }}
                                    </template>
                                </b-table>
                            </template>
                        </b-table>
                        <b-pagination
                            v-model="currentPage"
                            :total-rows="sale_data.total"
                            :per-page="sale_data.per_page"
                            @input="fetchData"
                            aria-controls="orders-table"
                            align="center"
                            class="mt-3"
                        ></b-pagination>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div v-else><unauthorized/></div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import POSReportNav from "./POSReportNav.vue";
import Unauthorized from '../../utilities/Unauthorized.vue';
export default {
    components: { POSReportNav, Treeselect, Unauthorized },
    data() {
        return {
            fields: [
                { key: "details", label: "Details" },

                {
                    key: "created_at",
                    label: "Created",
                    formatter: "formatMonthDateTime",
                },
                { key: "branch.branch", label: "Branch" },
                { key: "receipt_no", label: "Receipt No" },
                { key: "customer_name", label: "Customer Name" },
                {
                    key: "receipt_total",
                    label: "Total",
                    formatter: "formatNumber",
                },
                {
                    key: "cash_pay_total",
                    label: "Cash Pay",
                    formatter: "formatNumber",
                },
                {
                    key: "mpesa_pay",
                    label: "Mpesa Pay",
                    formatter: "formatNumber",
                },

                {
                    key: "card_pay",
                    label: "Card Pay",
                    formatter: "formatNumber",
                },
                {
                    key: "credit_pay",
                    label: "Credit Pay",
                    formatter: "formatNumber",
                },
                {
                    key: "discount_pay",
                    label: "Discount ",
                    formatter: "formatNumber",
                },
                { key: "details", label: "Details" },
            ],
            detailFields: [
                { key: "id", label: "Detail ID" },
                { key: "stock", label: "Product Name" },
                { key: "qty", label: "Quantity" },
                { key: "price", label: "Price", formatter: "formatNumber" },
                { key: "row_total", label: "Total", formatter: "formatNumber" },
            ],
            orders: [], // Initialize as empty array
            allDetailsVisible: false, // Track whether all details are visible
            search_params: {
                report_type: "",
                user_id: null,
                cashier_id: null,
                order_type: null,
                branch_id: null,
            },
            reportTypes: [
                { value: "daily", label: "Daily" },
                { value: "bi-weekly", label: "Bi-Weekly" },
                { value: "monthly", label: "Monthly" },
                { value: "quarterly", label: "Quarterly" },
                { value: "yearly", label: "Yearly" },
            ],
            waiter_select_data: null,
            branch_select_data: null,
            isLoading: true,
            from: null,
            to: null,
             from_time:null,
            to_time:null,
            search: "",
            sale_data: [], // Initialize as empty array
            currentPage: 1, // Current page for pagination
            perPage: 10, // Number of items per page
        };
    },
    mounted() {
        this.concurrentApiReq(); // Fetch data when the component is mounted
    },
    watch: {
          from(newVal) {
            if (newVal) {
                this.from = this.setTime(newVal, 0, 0); // Set to 00:00
            }
        },
        to(newVal) {
            if (newVal) {
                this.to = this.setTime(newVal, 23, 59); // Set to 23:59
            }
        },
        currentPage(newPage) {
            this.fetchData(newPage);
        },

        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;

                this.concurrentApiReq();
            }, 500),
        },

        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    computed: {
        ordersWithDetails() {
            return this.sale_data.data
                ? this.sale_data.data.map((order) => ({
                      ...order,
                      _showDetails: this.allDetailsVisible, // Set visibility based on global state
                      cash_pay_total:
                          (order.cash_pay || 0) + (order.receipt_balance || 0), // Sum cash_pay and receipt_balance
                  }))
                : [];
        },
    },
    methods: {
        async fetchWaiter() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.waiter_select_data = this.modifyWaiterSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyWaiterSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },

        async fetchBranch() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/branch/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.branch_select_data = this.modifyBranchSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyBranchSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.branch,
                };
            });
            return modif;
        },

        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchAllData() {
            this.isLoading = true;
            this.from = null;
            this.to = null;
               this.from_time=null
            this.to_time=null
            this.concurrentApiReq();
        },
        formatNumber(value) {
            if (!value) return "0"; // Handle null, undefined, or 0 values
            return new Intl.NumberFormat().format(value); // Add thousands separator
        },
        toggleAllDetails() {
            this.allDetailsVisible = !this.allDetailsVisible; // Toggle visibility for all rows
        },
        async fetchData(page) {
            try {
                const res = await this.getApi("data/pos_sale/fetchAllSales", {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                           from_time:this.from_time,
                        to_time:this.to_time
                    },
                });

                if (res.status === 200) {
                    this.sale_data = res.data; // Update sale_data with API response
                } else {
                    this.swr("Server error occurred");
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                this.swr("An error occurred while fetching data");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            try {
                const res = await Promise.all([this.fetchData(1)]).then(
                    function (results) {
                        return results;
                    }
                );
            } catch (error) {
                console.error("Error in concurrent API request:", error);
            } finally {
                this.isLoading ? this.hideLoader() : "";
            }
        },
    },
};
</script>

<style scoped>
.table-container {
    width: 100%;
    overflow-x: auto;
}
.custom-table {
    min-width: 1200px; /* Adjust this value based on the number of columns */
    display: block; /* Forces it to behave as a block element */
}
.custom-table th,
.custom-table td {
    white-space: nowrap;
}
.details-table {
    background-color: #f2ef8b; /* Light gray background */
    border: 1px solid #ddd; /* Optional border */
}

.details-table tr {
    background-color: #f2ef8b !important; /* Slightly darker gray for rows */
}

.details-table th {
    background-color: #f2ef8b !important; /* Distinct header color */
}
</style>
