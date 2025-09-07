<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><inventory-nav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Variation Report</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Stock Date</th>
                                        <th>Loc</th>
                                        <th scope="col">
                                            Total Items On Inventory
                                        </th>

                                        <th scope="col">Total Items Counted</th>
                                        <th><b>Variation on quantity</b></th>

                                        <th scope="col">
                                            Total Current On Purchase Price
                                        </th>
                                        <th scope="col">
                                            Total Counted On Purchase Price
                                        </th>
                                        <th scope="col">
                                            <b>
                                                Variation On Purchase
                                                Price(counted-current)</b
                                            >
                                        </th>
                                        <th scope="col">
                                            Total Current On Selling Price
                                        </th>
                                        <th scope="col">
                                            Total Counted On Selling Price
                                        </th>
                                        <th scope="col">
                                            <b
                                                >Variation On Selling Price
                                                (counted-current)</b
                                            >
                                        </th>

                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in report_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewDetails(data)
                                                "
                                            >
                                                {{
                                                    formatMonthDateTime(
                                                        data.stocktake_date
                                                    )
                                                }}</router-link
                                            >
                                        </td>
                                        <td>
                                            {{ data.department.department }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_current_qty
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_counted_qty
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.total_counted_qty -
                                                            data.total_current_qty
                                                    )
                                                }}</b
                                            >
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_variation_current_purchase_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_variation_counted_purchase_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.sum_variation_counted_purchase_price -
                                                            data.sum_variation_current_purchase_price
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_variation_current_selling_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_variation_counted_selling_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.sum_variation_counted_selling_price -
                                                            data.sum_variation_current_selling_price
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td>
                                            {{ data.user.name }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="report_data !== null"
                            v-bind:results="report_data"
                            v-on:get-page="fetchGrouped"
                        ></Pagination>
                        Items Count {{ report_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    type="xls"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" x-small>
                                        Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="erp_stock.csv"
                                >
                                    <v-btn x-small color="success"
                                        >Export CSV</v-btn
                                    >
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal width="1200px" v-model="details_modal"
            ><inventory-stock-take-details-modal
                :details_data="details_data"
                v-if="details_modal"
        /></Modal>
    </div>
</template>

<script>
import Pagination from "../utilities/Pagination.vue";
import InventoryNav from "./InventoryNav.vue";
import InventoryStockTakeDetailsModal from "./InventoryStockTakeDetailsModal.vue";
export default {
    components: { InventoryNav, Pagination, InventoryStockTakeDetailsModal },
    data() {
        return {
            details_modal: false,
            report_data: [],
            show_loader: true,
            details_data: null,
            params: {
                search: "",
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        viewDetails(data) {
            this.details_data = data;
            this.details_modal = true;
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock_stake/fetchGrouped", {
                params: {
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                const data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        stocktake_code: data.stocktake_code,
                        "STOCK TAKE DATE": data.stocktake_date,
                        "TOTAL ON INVENTORY": data.total_current_qty,
                        "TOTAL COUNTED": data.total_counted_qty,
                        "QTY VARIATION":
                            data.total_counted_qty - data.total_current_qty,

                        "SUM CURRENT ON PURCHASE PRICE":
                            data.sum_variation_current_purchase_price,
                        "SUM COUNTED  ON PURCHASE PRICE":
                            data.sum_variation_counted_purchase_price,
                        "VARIATION OF PURCHASES":
                            data.sum_variation_counted_purchase_price -
                            data.sum_variation_current_purchase_price,
                        "SUM CURRENT ON SELLING PRICE":
                            data.sum_variation_current_selling_price,
                        "SUM COUNTED  ON SELLING PRICE":
                            data.sum_variation_counted_selling_price,
                        "VARIATION ON SELLING PRICE":
                            data.sum_variation_counted_selling_price -
                            data.sum_variation_current_selling_price,
                    });
                });
                return data_array;
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.show_loader ? this.showLoader() : "";
            await Promise.all([this.fetchGrouped(1)]);
            this.show_loader ? this.hideLoader() : "";
        },
        async fetchGrouped(page) {
            const res = await this.getApi("data/stock_stake/fetchGrouped", {
                params: {
                    page,

                    ...this.params,
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
};
</script>
