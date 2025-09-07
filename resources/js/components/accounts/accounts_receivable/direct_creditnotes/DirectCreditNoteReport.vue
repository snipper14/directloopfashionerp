<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><receivable-nav /></div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Direct Credit Note</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="col-md-10">
                            <div class="card">
                                <div
                                    class="card-header d-flex justify-content-between align-items-center"
                                >
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input
                                                type="text"
                                                placeholder="Search Customer.."
                                                class="form-control small-input"
                                                v-model="params.search"
                                            />
                                        </div>
                                        <div
                                            class="col-md-4 form-group d-flex flex-column"
                                        >
                                            <label for="">From Date</label>
                                            <date-picker
                                                v-model="params.from"
                                                valueType="format"
                                            ></date-picker>
                                        </div>
                                        <div
                                            class="col-md-4 form-group d-flex flex-column"
                                        >
                                            <label for="">To Date</label>
                                            <date-picker
                                                v-model="params.to"
                                                valueType="format"
                                            ></date-picker>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th scope="col">Company</th>

                                                    <th scope="col">
                                                        Credit No.
                                                    </th>
                                                    <th scope="col">
                                                        Total Vat
                                                    </th>

                                                    <th scope="col">
                                                        Total Creditnote
                                                    </th>

                                                    <th scope="col">
                                                        Download
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    class="small-tr"
                                                    v-for="(
                                                        data, i
                                                    ) in creditnote_data.data"
                                                    :key="i"
                                                >
                                                    <td>
                                                        {{ data.credit_date }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.customer
                                                                .company_name
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{ data.credit_no }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.sum_row_vat
                                                            )
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.sum_line_total
                                                            )
                                                        }}
                                                    </td>

                                                    <td>
                                                        <v-btn
                                                            @click="
                                                                printCreditnote(
                                                                    data
                                                                )
                                                            "
                                                            color="primary"
                                                            x-small
                                                            >Print
                                                            Creditnote</v-btn
                                                        >
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <pagination
                                        v-if="creditnote_data !== null"
                                        v-bind:results="creditnote_data"
                                        v-on:get-page="fetch"
                                    ></pagination>
                                    Items Count {{ creditnote_data.total }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <print-direct-credit-note
            :invoice_details="invoice_details"
            :total_credit_amount="total_credit_amount"
            :total_vat_amount="total_vat_amount"
            ref="PrintCreditNote"
            :logo="logo"
            v-if="show_print"
        />
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import ReceivableNav from "../ReceivableNav.vue";
import AddDirectCreditNote from "./AddDirectCreditNote.vue";
import PrintDirectCreditNote from "./PrintDirectCreditNote.vue";
export default {
    components: {
        Unauthorized,
        ReceivableNav,
        AddDirectCreditNote,
        Pagination,
        PrintDirectCreditNote,
    },
    data() {
        return {
            params: {
                search: "",
                to: null,
                from: null,
            },
            logo: null,
            invoice_details: null,
            showLoading: true,
            creditnote_data: [],
            show_print: false,
            total_credit_amount: 0,
            total_vat_amount:0,
        };
    },
    mounted() {
        this.logo = this.$store.state.branch.img_url;
        this.concurrentApiReq(1);
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.showLoading = false;
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    methods: {
        async printCreditnote(data) {
            const res = await this.getApi(
                "data/direct_credit_note/fetchDetails",
                {
                    params: {
                        credit_no: data.credit_no,
                    },
                }
            );

            if (res.status === 200) {
                console.log(JSON.stringify(res.data));
                this.invoice_details = res.data;
                this.show_print = true;
                 this.total_credit_amount = this.invoice_details.reduce(function ( sum, val) {
                    return sum + val.line_total;
                }, 0);
                 this.total_vat_amount = this.invoice_details.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.row_vat;
                },
                0);
                setTimeout(() => {
                    this.$refs.PrintCreditNote.printBill();
                }, 1000);
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.showLoading ? this.showLoader() : null;

            await Promise.all([this.fetch(1)]);
            this.showLoading ? this.hideLoader() : null;
        },
        async fetch(page) {
            const res = await this.getApi("data/direct_credit_note/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.creditnote_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
