<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <ReceivableNav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <h4>Payment Records</h4>
                        <form class="form-inline">
                            <input
                                class="form-control mr-sm-2"
                                type="search"
                                v-model="params.search"
                                placeholder="Search "
                                aria-label="Search"
                            />
                        </form>
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Company
                                       
                                    </th>

                                    <th scope="col">
                                        Amount Paid <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_amount_paid
                                            "
                                            sort-key="sort_amount_paid"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>
                                    <th scope="col">TT. Amount Due</th>
                                    <th scope="col">Reference</th>
                                    <th scope="col">Date Paid</th>
                                    <th scope="col">Pay method</th>
                                    <th scope="col">Details</th>
                                    <th scope="col">Receiver</th>
                                    <th>DEL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in payment_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.customer.company_name }}
                                    </td>

                                    <td>
                                        <b>{{
                                            cashFormatter(data.amount_paid)
                                        }}</b>
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.amount_due) }}
                                    </td>
                                    <td>{{ data.ref_no }}</td>
                                    <td>{{ data.date_paid }}</td>
                                    <td>{{ data.pay_method }}</td>
                                    <td>{{ data.payment_details }}</td>
                                    <td>{{ data.user.name }}</td>
                                    <td>
                                        <v-btn
                                            color="danger"
                                            v-if="isDeletePermitted"
                                            title="DELTE"
                                            @click="deleteRec(data.id, i)"
                                            >DEL</v-btn
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="payment_data !== null"
                            v-bind:results="payment_data"
                            v-on:get-page="fetchPayments"
                        ></Pagination>
                        Items Count {{ payment_data.total }}

                        <div
                            class="row"
                            title="download"
                            v-if="isDownloadPermitted"
                        >
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="allPayment"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" small>
                                        <v-icon medium>{{
                                            icons.mdiMicrosoftExcel
                                        }}</v-icon>
                                        Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="allPayment"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                    ><v-btn color="secondary" small>
                                        <v-icon class="v-icon" medium>{{
                                            icons.mdiFileExcel
                                        }}</v-icon>
                                        Export CSV</v-btn
                                    >
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
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import ReceivableNav from "../ReceivableNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import SortButtons from "../../../utilities/SortButtons.vue";

export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_payment: false,
                edit_supplier: false,
                customer_balances: false,
            },
            isLoading: true,
            value: "",
            payment_data: [],
            edit_data: null,
            pdf_data: [],

            sort_params: {
                sort_amount_paid: "",
            },
            params: {
                search: "",
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
        ReceivableNav,
        Pagination,
        Unauthorized,
        SortButtons,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        updateSortParameter(val){
            this.sortParameter(val.key,val.value)
           
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([this.fetchPayments(1)]);

            this.isLoading ? this.hideLoader() : "";
        },
        async deleteRec(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (!shouldDelete) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "delete",
                "data/payments/destroy/" + id,
                {}
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("DELETED");
                this.payment_data.data.splice(i, 1);
            } else {
                this.swr("Server error occurred contact system administrator");
            }
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchPayments();
        },

        onChildNavChange(newValue) {
            this.setActive(this.active, newValue);
        },

        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.allPayment();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async allPayment() {
            this.showLoader();
            const res = await this.getApi("data/payments/fetch", "");
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    client: data.customer.company_name,
                    invoice_no: data.invoice.invoice_no,
                    invoice_amount: data.invoice_amount,
                    amount_paid: data.amount_paid,
                    amount_due: data.amount_due,

                    ref_no: data.ref_no,
                    date_paid: data.date_paid,
                    pay_method: data.pay_method,
                    payment_details: data.payment_details,
                    user: data.user.name,
                });
            });
            return data_array;
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchPayments();
        },

        async fetchPayments(page) {
            const res = await this.getApi("data/payments/fetch", {
                params: {
                    page,
                    ...this.sort_params,
                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.payment_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
