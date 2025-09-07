<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row justify-content-left">
                    <v-btn
                        class="ma-2 v-btn-primary"
                        @click="$emit('dashboard-active')"
                        color="primary"
                        dark
                    >
                        <v-icon dark left> mdi-arrow-left </v-icon>
                        Back
                    </v-btn>
                </div>
                <div class="row">
                    <div class="card">
                        <div
                            class="card-header d-flex flex-row justify-content-between"
                        >
                            <h3>
                                <b>{{ details_data.user.name }}</b>
                            </h3>
                            <b
                                >From:
                                {{
                                    details_data.opening_time
                                        ? details_data.opening_time
                                        : "beginning"
                                }}
                                <br />
                                To: {{ details_data.closing_time }}</b
                            >
                            <h3>
                                <b
                                    >TOTAL BALANCE:
                                    {{ cashFormatter(balance_total) }}</b
                                >
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Sale Date</th>
                                            <th scope="col">Amount</th>
                                            <th>Vat</th>
                                            <th scope="col">Cash Paid</th>
                                            <th scope="col">Mpesa Paid</th>
                                            <th scope="col">Card Paid</th>

                                            <th>Receipt No</th>
                                            <th>Order No</th>
                                            <th scope="col">Cashier</th>
                                            <th scope="col">Waiter</th>
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
                                                    formatDateTime(
                                                        data.created_at
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.receipt_total
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.total_vat
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <b
                                                    :title="
                                                        'balance: ' +
                                                        cashFormatter(
                                                            data.receipt_balance
                                                        )
                                                    "
                                                >
                                                    {{
                                                        cashFormatter(
                                                            data.cash_pay +
                                                                data.receipt_balance
                                                        )
                                                    }}
                                                </b>
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.mpesa_pay
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(data.card_pay)
                                                }}
                                            </td>

                                            <td>
                                                {{ data.receipt_no }}
                                            </td>
                                            <td>
                                                {{ data.order_no }}
                                            </td>

                                            <td>
                                                {{
                                                    data.cashier
                                                        ? data.cashier.name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.waiter
                                                        ? data.waiter.name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        viewDetails(data)
                                                    "
                                                >
                                                    View Details
                                                </router-link>
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
        <!-- <Modal v-model="modal.details" title="Sale Details">
            <POSDetailReport
                v-if="order_data_details && modal.details"
                :order_data_details="order_data_details"
                :total_order="total_order"
                :total_tax="total_tax"
            />
        </Modal> -->
         <Modal
            title="Order details"
            v-model="modal.details"
            width="700px"
            ><sale-details-report-modal
                v-if="order_data_details && modal.details"
               :sale_details="order_data_details"
                v-on:dismiss-details="dismissCredit"
        /></Modal>
    </div>
</template>

<script>
import POSDetailReport from "../pos_report/dine_in_reports/POSDetailReport.vue";
import SaleDetailsReportModal from '../pos_report/SaleDetailsReportModal.vue';
export default {
    props: ["details_data"],
    components: {
        POSDetailReport,
        SaleDetailsReportModal,
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
        };
    },
    mounted() {
        this.fetchBalancesDetails();
    },
    methods: {
        async fetchExcel() {
            var data = this.sales_data;
            if (data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    data[i].name = data[i].user ? data[i].user.name : "";
                    data[i].cashier = data[i].cashier ? data[i].cashier.name : "";
                     data[i].waiter = data[i].waiter ? data[i].waiter.name : "";
                     data[i].created_at=  this.formatDateTime(data[i].created_at)
                }

                return data;
            } else {
            }
        },
        viewDetails(data) {
            this.total_order = data.receipt_total;
            this.total_tax = data.total_vat;
            this.order_data_details = data;
            this.modal.details = true;
        },
        async fetchBalancesDetails() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/cashier_balance/fetchBalancesDetails",

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
