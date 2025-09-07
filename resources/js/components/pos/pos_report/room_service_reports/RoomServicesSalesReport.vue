<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Room Service Report</div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    v-model="from"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    v-model="to"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <button
                                    class="btn btn-secondary btn-sm"
                                    @click="filterDate()"
                                >
                                    Filter Date
                                </button>
                            </div>
                            <div class="form-group">
                                <label for="">Search</label>
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="total-display">
                            <span class="badge badge-secondary totals-badge"
                                >Total Sales:
                                {{ cashFormatter(total_sales) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total VAT: {{ cashFormatter(total_vat) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total Service Charge:
                                {{
                                    cashFormatter(sum_service_charge_total)
                                }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total Cash:
                                {{ cashFormatter(total_cash) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total MPESA:
                                {{ cashFormatter(total_mpesa) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total Card:
                                {{ cashFormatter(total_card) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total Credit:
                                {{ cashFormatter(total_credit) }}</span
                            >
                             <div class="ml-2">
                                <button class="btn btn-primary btn-sm mr-2"  v-if="isDownloadPermitted" @click="printTotals">
                                    Print Totals
                                </button>
                                </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th scope="col">Amount</th>
                                        <th>Vat</th>
                                        <th>Service Charge</th>
                                        <th scope="col">Cash Paid</th>
                                        <th scope="col">Mpesa Paid</th>
                                        <th scope="col">Card Paid</th>
                                        <th scope="col">Credit Paid</th>
                                        <th>Receipt No</th>
                                        <th scope="col">Guest</th>
                                        <th scope="col">Cashier</th>
                                        <th scope="col">Waiter</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.paid_date }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.receipt_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.total_vat) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.service_charge_total
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
                                            {{ cashFormatter(data.mpesa_pay) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.card_pay) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.credit) }}
                                        </td>
                                        <td>
                                            {{ data.receipt_no }}
                                        </td>
                                        <td>
                                            {{
                                                data.guest
                                                    ? data.guest.name
                                                    : ""
                                            }}
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
                                                data.user ? data.user.name : ""
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
                        </div>
                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
                        <div class="row">
                            <div
                                class="col-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
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
                                    v-if="isDownloadPermitted"
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
                                <div class="ml-2">
                                    <button
                                        @click="printDetailedData()"
                                        v-if="isDownloadPermitted"
                                        class="btn btn-primary btn-sm"
                                    >
                                        Print Detailed Records
                                    </button>
                                </div>
                                 <div class="ml-2">
                                <button class="btn btn-primary btn-sm mr-2"  v-if="isDownloadPermitted" @click="printTotals">
                                    Print Totals
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <RoomServicePrintComponent
          v-if="all_sale_data"
        :sale_data="all_sale_data"
        :from="from"
        :to="to"
        ref="RoomServicePrintComponent"/>
        <DineTotalPrintComponent
         ref="DineTotalPrintComponent"
         :report_title="report_title"
         :total_sales="total_sales"
         :total_vat="total_vat"
         :total_cash="total_cash"
         :total_mpesa="total_mpesa"
         :total_card="total_card"
         :from="from"
        :to="to"
         />
         
        <Modal v-model="modal.details" title="Sale Details">
            <RoomServiceSaleDetails
                v-if="order_data_details && modal.details"
                :order_data_details="order_data_details"
                :total_order="total_sales"
                :total_tax="total_vat"
                :cash_pay="total_cash"
                :mpesa_pay="total_mpesa"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import RoomServiceSaleDetails from "../RoomServiceSaleDetails.vue";
import POSReportNav from "../POSReportNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import RoomServicePrintComponent from '../prints_components/RoomServicePrintComponent.vue';
import DineTotalPrintComponent from '../prints_components/DineTotalPrintComponent.vue';

export default {
    data() {
        return {
            modal: {
                details: false,
            },
            report_title:"Room Service Totals",
            order_data_details: null,
            total_vat: 0,
            total_sales: 0,
            total_cash: 0,
            total_mpesa: 0,
            total_card: 0,
            total_credit: 0,
            sum_service_charge_total: 0,
            isLoading: true,
            search: "",
            to: "",
            from: "",
            sale_data: [],
            all_sale_data: null,
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        RoomServiceSaleDetails,
        RoomServicePrintComponent,
        DineTotalPrintComponent
    },
    watch: {
        search: {
     
               handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async printTotals(){
               setTimeout(() => {
                    this.$refs.DineTotalPrintComponent.printBill();
                }, 1000);
        },
        async printDetailedData() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                this.all_sale_data = res.data;

                setTimeout(() => {
              
                    this.$refs.RoomServicePrintComponent.printBill();
                }, 1000);
            } else {
                this.servo();
            }
        },
        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/room_sale/salesReport", {
                params: {
                    search: this.search,
                    from: this.from,
                    to: this.to,
                },
            });
            this.hideLoader();
            return res;
        },
        viewDetails(data) {
            this.order_data_details = data;
            this.modal.details = true;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();

            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        order_no: array_item.order_no,
                        receipt_total: array_item.receipt_total,
                        service_charge_total: array_item.service_charge_total,
                        total_vat: array_item.total_vat,
                        cash_pay: array_item.cash_pay,
                        mpesa_pay: array_item.mpesa_pay,
                        card_pay: array_item.card_pay,
                        credit_pay: array_item.credit_pay,
                        receipt_total: array_item.receipt_total,
                        guest: array_item.guest ? array_item.guest.name : "",
                        cashier: array_item.cashier
                            ? array_item.cashier.name
                            : "",
                        sales_person: array_item.user
                            ? array_item.user.name
                            : "",
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
        async fetchTotals() {
            const res = await this.getApi(
                "data/room_sale/salesReportTotals",

                {
                    params: {
                        search: this.search,
                        currentRoute: this.$route.name,
                        from: this.from,
                        to: this.to,
                    },
                }
            );

            if (res.status == 200) {
                const data = res.data;
                if (data) {
                    this.total_vat = data.sum_total_vat;
                    this.total_sales = data.sum_receipt_total;
                    this.total_cash = data.sum_cash_pay;
                    this.total_mpesa = data.sum_mpesa_pay;
                    this.total_card = data.sum_card_pay;
                    this.total_credit = data.sum_credit_pay;
                    this.sum_service_charge_total =
                        data.sum_service_charge_total;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/room_sale/salesReport",

                {
                    params: {
                        search: this.search,
                        page,
                        currentRoute: this.$route.name,
                        from: this.from,
                        to: this.to,
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
