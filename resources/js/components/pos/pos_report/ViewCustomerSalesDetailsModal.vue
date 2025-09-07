<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <b
                                >Customer
                                {{ sale_details.customer.company_name }}
                                Details</b
                            >
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="d-flex justify-content around mt-3">
                                    <div class="d-flex flex-column">
                                        <label> From Datetime *</label>

                                        <date-picker
                                            type="datetime"
                                            width="300px"
                                            v-model="from"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <label> To Datetime *</label>
                                        <date-picker
                                            type="datetime"
                                            width="300px"
                                            v-model="to"
                                        ></date-picker>
                                    </div>
                                    <div>
                                        <button
                                            class="btn btn-primary btn-sm mt-4"
                                            @click="filterDate()"
                                        >
                                            Filter
                                        </button>
                                        <button
                                            class="btn btn-info btn-sm mt-4"
                                            @click="fetchAllData()"
                                        >
                                            Fetch All
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <input
                                        type="text"
                                        v-model="search_params.search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th>
                                                    Created at<sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_created_at
                                                        "
                                                        sort-key="sort_created_at"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                <th>Order No</th>

                                                <th scope="col">
                                                    Product Code
                                                </th>
                                                <th scope="col">
                                                    Product Name
                                                </th>

                                                <th>
                                                    qty<sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_qty
                                                        "
                                                        sort-key="sort_qty"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                <th>
                                                    Selling Price<sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_selling_price
                                                        "
                                                        sort-key="sort_selling_price"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>

                                                <th>
                                                    Total
                                                    <sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_row_total
                                                        "
                                                        sort-key="sort_row_total"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                <th>User</th>
                                                <th>Branch</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    data, i
                                                ) in order_data.data"
                                                :key="i"
                                            >
                                                <th>
                                                    {{
                                                        formatMonthDateTime(
                                                            data.created_at
                                                        )
                                                    }}
                                                </th>
                                                <td
                                                    :style="{
                                                        backgroundColor:
                                                            orderNoColorMap[
                                                                data.order_no
                                                            ] || '',
                                                    }"
                                                >
                                                    {{ data.order_no }}
                                                </td>
                                                <td>{{ data.stock.code }}</td>
                                                <td>
                                                    {{
                                                        data.stock.product_name
                                                    }}
                                                </td>
                                                <td>{{ data.qty }}</td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.price
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.row_total
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ data.user.name }}</td>
                                                <td>{{data.branch.branch}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <Pagination
                                        v-if="order_data !== null"
                                        v-bind:results="order_data"
                                        v-on:get-page="searchCustomerSalesItems"
                                    ></Pagination>
                                    Items Count {{ order_data.total }}
                                </div>
                                <div class="row" v-if="isDownloadPermitted">
                                    <div class="col-md-8 d-flex">
                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchExcel"
                                            worksheet="My Worksheet"
                                            name="filename.xls"
                                        >
                                            <button
                                                class="btn btn-primary btn-sm"
                                            >
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
                                            <button
                                                class="btn btn-success btn-sm"
                                            >
                                                Export CSV
                                            </button>
                                        </export-excel>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
import Swal from "sweetalert2/dist/sweetalert2.js";
import Pagination from "../../utilities/Pagination.vue";
import ReprintPosReceipt from "./ReprintPosReceipt.vue";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    components: {
        ReprintPosReceipt,
        Pagination,
        Swal,
        SortButtons,
    },
    props: ["sale_details"],
    data() {
        return {
            show_loader: true,
            order_data: [],
            form_data: {},
            etims_data: {},
            etims_response_data: null,
            load_printer_component: true,
            search_params: {
                search: "",
            },
            sort_params: {
                sort_selling_price: "",
                sort_qty: "",
                sort_row_total: "",
                sort_created_at: "",
                sort_stock_name: "",
            },
            from: null,
            to: null,
            orderNoColorMap: {}, // New: map order_no to color
            colorList: [
                "#E3F2FD",
                "#F1F8E9",
                "#FFF3E0",
                "#FCE4EC",
                "#E0F7FA",
                "#EDE7F6",
                "#FFFDE7",
                "#F3E5F5",
                "#FBE9E7",
                "#E8F5E9",
            ],
        };
    },
    mounted() {
        this.form_data = this.sale_details;
        this.etims_data = this.sale_details;

        this.searchCustomerSalesItems(1);
    },
    watch: {
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.show_loader = false;
                this.searchCustomerSalesItems(1);
            }, 500),
        },
        search_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.show_loader = false;
                this.searchCustomerSalesItems(1);
            }, 500),
        },
    },
    methods: {
        async fetchExcel() {
            const res = await this.getApi(
                "data/pos_sale/searchCustomerSalesItems",

                {
                    params: {
                        customer_id: this.sale_details.customer_id,

                        ...this.search_params,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                        ...this.sort_params,
                    },
                }
            );

            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        created_at: this.formatMonthDateTime(data.created_at),
                        order_no: data.order_no,
                        code: data.stock.code,

                        product_name: data.stock.product_name,
                        qty: data.qty,

                        s_price: data.price,
                        row_total: data.row_total,
                        user: data.user.name,
                        branch:data.branch.branch
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        async filterDate() {
            this.isLoading = true;
            this.searchCustomerSalesItems(1);
        },
        async fetchAllData() {
            this.isLoading = true;
            this.from = null;
            this.to = null;
            this.searchCustomerSalesItems(1);
        },
        async searchCustomerSalesItems(page) {
            this.show_loader ? this.showLoader() : "";
            const res = await this.getApi(
                "data/pos_sale/searchCustomerSalesItems",
                {
                    params: {
                        customer_id: this.sale_details.customer_id,
                        page,
                        ...this.search_params,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                        ...this.sort_params,
                    },
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.order_data = res.data;
                // Assign a color to each unique order_no
                const assignedColors = {};
                let colorIndex = 0;
                this.order_data.data.forEach((item) => {
                    const orderNo = item.order_no;
                    if (!assignedColors[orderNo]) {
                        assignedColors[orderNo] =
                            this.colorList[colorIndex % this.colorList.length];
                        colorIndex++;
                    }
                });
                this.orderNoColorMap = assignedColors;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
