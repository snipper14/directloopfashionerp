<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row justify-content-left"></div>

                <div class="card">
                    <div
                        class="card-header d-flex flex-row justify-content-between"
                    >
                       
                        <b
                            >From:
                            {{
                                details_data.opened_time
                                    ? details_data.opened_time
                                    : "beginning"
                            }}
                            <br />
                            To: {{ details_data.created_at }}</b
                        >
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th>Code</th>
                                        <th scope="col">Item</th>
                                        <th>Selling Price</th>
                                        <th>TT qty</th>
                                        <th scope="col">VAT </th>
                                        <th scope="col">TT Total</th>
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sales_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>

                                        <td>
                                            {{ data.stock.code }}
                                        </td>
                                        <td>
                                            {{ data.stock.name }}
                                        </td>
                                        <td>{{cashFormatter(data.stock.selling_price)}}</td>
                                        <td>
                                            {{ data.sum_qty }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sum_row_vat) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sum_row_total) }}
                                        </td>

                                        
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div
                                    class="col-4 d-flex"
                                    v-if="isDownloadPermitted"
                                >
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
            </div>
        </div>
        <Modal v-model="modal.details" title="Sale Details">
            <consolidated-details-report
                v-if="modal.details"
                :sale_details="sale_details"
            />
        </Modal>
    </div>
</template>

<script>
import ConsolidatedDetailsReport from "./ConsolidatedDetailsReport.vue";
export default {
    props: ["details_data"],
    components: {
        ConsolidatedDetailsReport,
    },
    data() {
        return {
            sales_data: [],
            order_data_details: null,
            modal: {
                details: false,
            },
            total_order: 0,
            total_tax: 0,
            balance_total: 0,
            sale_details: null,
        };
    },
    mounted() {
        this.fetchBalancesDetails();
    },
    methods: {
        async fetchExcel() {
            const res = await this.callApi(
                "post",
                "data/register/fetchBalancesDetailsItemized",

                this.details_data
            );
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    created_at: this.formatDateTime(data.created_at),

                    stock_code: data.stock.code,
                    stock_name: data.stock.name,
                    selling_price:data.stock.selling_price,
                    qty: data.sum_qty,
                      sum_row_vat: data.sum_row_vat,

                  
                    row_total: data.sum_row_total,
                 
                });
            });
            return data_array;
        },
        viewDetails(data) {
            this.sale_details = data;
            this.modal.details = true;
        },
        async fetchBalancesDetails() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/register/fetchBalancesDetailsItemized",

                this.details_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.sales_data = res.data;
                this.balance_total = this.sales_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.receipt_total;
                },
                0);
            } else {
                this.servo();
            }
        },
    },
};
</script>
