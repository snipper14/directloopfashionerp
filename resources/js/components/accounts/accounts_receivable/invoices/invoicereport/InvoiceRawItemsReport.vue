<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><ReceivableNav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <b>Invoice Items Sales</b>
                    </div>

                    <div class="card-body">
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
                                        v-model="params.from"
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
                                        v-model="params.to"
                                        placeholder="Select a date"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>

                                <v-btn
                                    x-small
                                    color="secondary"
                                    @click="fetchAllData()"
                                    >Reset Date Filter</v-btn
                                >
                            </div>

                            <div class="form-group col-md-3">
                                <label for="">Search</label>
                                <input
                                    type="text"
                                    v-model="params.search"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group col-md-3 nopadding">
                                <label for=""> Select Customer</label>
                                <treeselect
                                    width="300"
                                    :load-options="fetchCustomer"
                                    :options="customer_select_data"
                                    :auto-load-root-options="false"
                                    v-model="params.customer_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select Customer "
                                />
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Created at  <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_date
                                                "
                                                sort-key="sort_date"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th>
                                            Product<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_stock_name
                                                "
                                                sort-key="sort_stock_name"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Customer</th>
                                        <th>
                                            Qty Sold<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_quantity
                                                "
                                                sort-key="sort_quantity"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Purchase Price
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_purchase_amount
                                                "
                                                sort-key="sort_purchase_amount"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Selling Price  
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_selling_price
                                                "
                                                sort-key="sort_selling_price"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Vat</th>
                                        <th>Amount  <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_amount
                                                "
                                                sort-key="sort_total_amount"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in items_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.stock.name }}</td>
                                        <td>
                                            {{ data.customer.company_name }}
                                        </td>

                                        <td>{{ data.qty }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.purchase_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.price) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.tax_amount) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="items_data !== null"
                            v-bind:results="items_data"
                            v-on:get-page="itemizedReport"
                        ></Pagination>
                        Items Count {{ items_data.total }}
                        <div class="row">
                            <div class="col-8 d-flex">
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

            <notifications group="foo" />
        </div>
    </div>
</template>

<script>
import Pagination from "../../../../utilities/Pagination.vue";
import SortButtons from "../../../../utilities/SortButtons.vue";
import ReceivableNav from "../../ReceivableNav.vue";

export default {
    data() {
        return {
            active: {
                dashboard_active: true,
                summary_details: false,
            },
            sort_params: {
                sort_stock_name: "",
                sort_quantity: "",
                sort_purchase_amount:"",
                sort_selling_price:"",
                sort_total_amount: "",
                sort_total: "",
                sort_date:"DESC"
            },
            params: {
                customer_id: null,
                search: "",
                to: "",
                from: "",
                is_grouped: "false",
            },
            isLoading: true,

            items_data: [],
        };
    },
    components: {
        ReceivableNav,
        SortButtons,
        Pagination,
    },
    watch: {
        sort_params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async fetchAllData() {
            this.isLoading = true;
            this.params.from = null;
            this.params.to = null;
            this.concurrentApiReq();
        },
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        roundUpToNearest10(number) {
            return Math.ceil(number / 10) * 10;
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard_active");
        },
        async printTotals() {
            setTimeout(() => {
                this.$refs.DineTotalPrintComponent.printBill();
            }, 1000);
        },

        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/invoices/itemizedReport", {
                params: {
                    ...this.params,

                    ...this.sort_params,
                },
            });
            this.hideLoader();
            return res;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        created: this.formatDateTime(data.created_at),
                        item: data.stock.name,
                        company_name: data.customer.company_name,
                        qty: data.qty,
                        purchase_price: data.purchase_price,
                        selling_price: data.price,
                        tax_amount: data.tax_amount,
                        row_total: data.row_total,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },

        async itemizedReport(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/invoices/itemizedReport",

                {
                    params: {
                        page,
                        ...this.params,
                        ...this.sort_params,
                    },
                }
            );
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.items_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },

        async concurrentApiReq() {
            await Promise.all([this.itemizedReport(1)]);
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>

<style scoped></style>
