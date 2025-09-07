<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row" v-if="active.dashboard">
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
                                    details_data.opened_time
                                        ? details_data.opened_time
                                        : "beginning"
                                }}
                                <br />
                                To: {{ details_data.created_at }}</b
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

                                            <th scope="col">Mpesa Paid</th>

                                            <th>Receipt No</th>

                                            <th scope="col">Cashier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in sales_data"
                                            :key="i"
                                            v-if="data.mpesa_pay>0"
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
                                                        data.mpesa_pay
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{ data.receipt_no }}
                                            </td>

                                            <td>
                                                {{
                                                    data.cashier
                                                        ? data.cashier.name
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
                                        <v-btn
                                            color="secondary"
                                            small
                                            @click="printMpesa"
                                        >
                                            Print
                                        </v-btn>
                                    </div>
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
        <MpesaPaymentPrintBalance
            ref="MpesaPaymentPrintBalance"
            v-if="print_statement"
            :sales_data="sales_data"
        />
    </div>
</template>

<script>
import ConsolidatedDetailsReport from "./ConsolidatedDetailsReport.vue";
import MpesaPaymentPrintBalance from "./MpesaPaymentPrintBalance.vue";
export default {
    props: ["details_data"],
    components: {
        ConsolidatedDetailsReport,
        MpesaPaymentPrintBalance,
    },
    data() {
        return {
            print_statement: false,
            active: {
                dashboard: true,
                mpesa_pay: false,
            },
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
        async printMpesa() {
            this.print_statement = true;
            setTimeout(() => {
                this.$refs.MpesaPaymentPrintBalance.printReceipt();
                this.print_statement = false;
            }, 1000);
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        async fetchExcel() {
            const res = await this.callApi(
                "post",
                "data/register/fetchBalancesDetails",

                this.details_data
            );
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    created_at: this.formatDateTime(data.created_at),

                    mpesa_pay: data.mpesa_pay,

                    receipt_no: data.receipt_no,
                    cashier: data.cashier.name,
                    user: data.user.name,
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
                "data/register/fetchBalancesDetails",

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
