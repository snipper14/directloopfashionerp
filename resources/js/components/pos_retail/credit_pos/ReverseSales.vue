<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><CreditNav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">
                        <h3><b style="color: red">Reverse Sales</b></h3>
                    </div>

                    <div class="card-body">
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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Search..</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th>Customer</th>
                                        <th scope="col">Amount</th>
                                        <th>Vat</th>

                                        <th>Receipt No</th>

                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>
                                            {{ data.customer.company_name }}
                                        </td>

                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.receipt_total
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.total_vat) }}
                                        </td>

                                        <td>
                                            {{ data.receipt_no }}
                                        </td>

                                        <td>
                                            <v-btn
                                                title="write"
                                                v-if="isWritePermitted"
                                                x-small
                                                color="primary"
                                                @click="
                                                    viewDetails(data)
                                                "
                                                >Open</v-btn
                                            >
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
                                class="col-md-8 d-flex"
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
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <Modal v-model="credit_note_modal" width="1200px">
            <create-credit-note
                v-if="credit_note_modal"
                :receipt_details="receipt_details"
                @dismiss-diag="dismissDiag"
            />
        </Modal>
  <Modal
            title="Order details"
            v-model="receipt_details_modal"
            width="700px"
            ><sale-details-report-modal
                v-if="receipt_details_modal"
                :sale_details="receipt_details"
                v-on:dismiss-details="dismissCredit"
        /></Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import CreditNav from "./CreditNav.vue";
import datetime from "vuejs-datetimepicker";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateCreditNote from "./CreateCreditNote.vue";
import SaleDetailsReportModal from '../../pos/pos_report/SaleDetailsReportModal.vue';
export default {
    components: {
        CreditNav,
        Unauthorized,
        Pagination,
        datetime,
        Treeselect,
        CreateCreditNote,
        SaleDetailsReportModal,
    },
    data() {
        return {
            receipt_details_modal:false,
            search_params: {
                user_id: null,
                cashier_id: null,
                order_type: null,
            },
            order_type_obj: [
                { value: "", label: "All" },
                { value: "Dine In", label: "Dine In" },
                { value: "takeaway", label: "takeaway" },
                { value: "online", label: "online" },
            ],
            total_refundable: 0,
            search: "",
            sale_data: [],
            waiter_select_data: null,
            from: null,
            to: null,
            receipt_details: null,
            isLoading: true,
            credit_note_modal: false,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        order_type_obj: {
            deep: true,
            handler: _.debounce(function (val) {
                this.isLoading = false;
                console.log(JSON.stringify(val));
                // this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
           viewDetails(data) {
            this.receipt_details = data;
            this.receipt_details_modal = true;
        },
          dismissCredit() {
            this.hideLoader();
          
            this.receipt_details_modal = false;
            this.concurrentApiReq();
        },
        dismissDiag() {
            this.credit_note_modal = false;
        },
        async generateCreditNote(data) {
            this.receipt_details = data;
            this.credit_note_modal = true;
        },
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

        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchAllData() {
            this.isLoading = true;
            this.from = null;
            this.to = null;
            this.concurrentApiReq();
        },

        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchAllSales",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
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
        async fetchTotals() {
            const res = await this.getApi(
                "data/pos_sale/fetchAllSaleTotal",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
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
                }
            } else {
                this.swr("Server error occurred");
            }
        },

        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/fetchAllSales", {
                params: {
                    ...this.search_params,
                    search: this.search,
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                },
            });
            this.hideLoader();
            return res;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        receipt_total: array_item.receipt_total,
                        cash_pay: array_item.cash_pay,
                        mpesa_pay: array_item.mpesa_pay,
                        card_pay: array_item.card_pay,
                        credit_pay: array_item.credit_pay,
                        receipt_total: array_item.receipt_total,
                        customer: array_item.customer
                            ? array_item.customer.company_name
                            : "",
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
    },
};
</script>
