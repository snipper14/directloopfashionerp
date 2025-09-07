<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card" v-if="isReadPermitted">
                        <div class="card-header">
                            <h3>
                                <b style="color: red">CREDIT NOTES DETAILS </b>
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="col-md-3">
                                <v-btn
                                    title="update"
                                    v-if="isUpdatePermitted"
                                    small
                                    color="primary"
                                    @click="reprintCredit()"
                                    >Re-Print Cr.Note</v-btn
                                >
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Timestamp</th>
                                            <th scope="col">Credit Date</th>
                                            <th>Code</th>
                                            <th>Product</th>
                                            <th>Receipt No</th>
                                            <th>Credit No</th>
                                            <th scope="col">Cr.SP</th>
                                            <th scope="col">Cr.qty</th>
                                            <th>Cr.Vat</th>
                                            <th>Cr.Amount</th>
                                            <th>User</th>
                                            <th>Add Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in credit_note_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{
                                                    formatDateTime(
                                                        data.created_at
                                                    )
                                                }}
                                            </td>
                                            <td>{{ data.credit_date }}</td>
                                            <td>{{ data.stock.code }}</td>
                                            <td>{{ data.stock.name }}</td>
                                            <td>{{ data.receipt_no }}</td>
                                            <td>{{ data.credit_no }}</td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            data.credit_sp
                                                        )
                                                    }}</b
                                                >
                                            </td>
                                            <td>
                                                {{ data.qty }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.credit_vat
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <b>{{
                                                    cashFormatter(
                                                        data.credit_amount
                                                    )
                                                }}</b>
                                            </td>

                                            <td>{{ data.user.name }}</td>
                                            <td>{{ data.deduct_stock }}</td>
                                            <td>
                                                <v-btn
                                                    title="delete"
                                                    x-small
                                                    color="danger"
                                                    @click="
                                                        deleteData(data.id, i)
                                                    "
                                                    v-if="isDeletePermitted"
                                                    >Delete</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <unauthorized />
                    </div>
                </div>
            </div>

            <notifications group="foo" />
            <div class="row">
                <div class="col-4">
                    <PrintPOSCreditNote
                        ref="ReceiptPrintComponent"
                        v-if="load_printer_component"
                        :form_data="form_data"
                        :order_data="credit_note_data"
                    />
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import CreditNav from "./CreditNav.vue";
import datetime from "vuejs-datetimepicker";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import PrintPOSCreditNote from "./PrintPOSCreditNote.vue";
export default {
    props: ["credit_details"],
    components: {
        CreditNav,
        Unauthorized,
        Pagination,
        datetime,
        Treeselect,
        PrintPOSCreditNote,
    },
    data() {
        return {
            form_data: {
                cu_invoice_no: "",
                credit_amount: 0,
                receipt_no: "",
                credit_no: null,
                credit_date: null,
                credit_vat: 0,
                total_cr: 0,
                total_vat_cr: 0,
            },

            load_printer_component: false,
            credit_note_data: [],

            isLoading: true,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },

    methods: {
        reprintCredit() {
            this.form_data.cu_invoice_no = this.credit_details.cu_invoice_no;
            this.form_data.credit_no = this.credit_details.credit_no;
            this.form_data.total_vat_cr = this.credit_details.total_vat;
            this.form_data.total_cr = this.credit_details.total_amount;
            this.load_printer_component = true;
            setTimeout(() => {
                //disable browser printing
                this.$refs.ReceiptPrintComponent.printReceipt();
            }, 1000);
        },
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/pos_credit/deleteCCompletedCr/" + id,
                    {}
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.credit_note_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetch(1)]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },

        async apiCall(page) {
            const res = await this.getApi("data/pos_credit/fetch", {
                params: {
                    credit_no: this.credit_details.credit_no,
                },
            });
            return res;
        },
        async fetch(page) {
            const res = await this.apiCall(page);

            if (res.status === 200) {
                this.credit_note_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchExcel() {
            const res = await this.apiCall();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        timestamp: this.formatDateTime(data.created_at),
                        credit_date: data.credit_date,
                        receipt_no: data.receipt_no,
                        credit_no: data.credit_no,
                        total_amount: data.total_amount,
                        total_vat: data.total_vat,
                        user: data.user.name,
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
