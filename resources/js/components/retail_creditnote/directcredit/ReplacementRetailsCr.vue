<template>
    <v-app class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <v-btn
                    class="ma-2 v-btn-primary"
                    @click="$emit('dashboard-active')"
                    color="primary"
                    dark
                >
                    <v-icon dark left> mdi-arrow-left </v-icon>
                    Back
                </v-btn>
                <div class="card">
                    <div class="card-header">
                        <h3><b>Search Replacement</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <search-product v-on:stockSearchResult="addCart" />
                        </div>
                        <div class="row" v-if="data_array.length > 0">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table modern-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Sell Price</th>

                                            <th scope="col">Sub Total</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in data_array"
                                            :key="i"
                                        >
                                            <td>
                                                {{ value.stock.code }}
                                            </td>
                                            <td>
                                                {{ value.stock.name }}
                                            </td>

                                            <td>{{ value.qty }}</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.selling_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.row_total
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <v-btn
                                                    x-small
                                                    color="danger"
                                                    title="DELETE"
                                                    v-if="isDeletePermitted"
                                                    @click="
                                                        deleteRecord(value, i)
                                                    "
                                                    >Delete</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <span>
                                        <b>
                                            Credit Total:
                                            {{ cashFormatter(total_amount) }}</b
                                        >
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <span>
                                        <b>
                                            Replacement Total:
                                            {{
                                                cashFormatter(receipt_total)
                                            }}</b
                                        >
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <span>
                                        <b v-if="top_amount > 0">
                                            Topup:
                                            {{ cashFormatter(top_amount) }}</b
                                        >
                                        <b v-else>
                                            Cr Balance:
                                            {{ cashFormatter(top_amount) }}</b
                                        >
                                    </span>
                                </div>

                                <div class="col-md-3">
                                    <v-btn
                                        v-if="
                                            data_array.length > 0 &&
                                            top_amount > 0
                                        "
                                        color="primary"
                                        @click="
                                            completeDirectRetailExchangeSales()
                                        "
                                        >Top-Up Cash</v-btn
                                    >
                                    <v-btn
                                        v-if="
                                            data_array.length > 0 &&
                                            top_amount <= 0
                                        "
                                        color="primary"
                                        @click="completeExchangeMatch()"
                                        >Complete Replacement</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <Modal v-model="cashout_modal" width="800px">
                <CashoutDirectCR
                    :total_order="top_amount"
                    :total_tax="extra_tax"
                    :order_data_offline="data_array"
                    @dismiss-diag="dismissDiag"
                    v-if="cashout_modal && data_array.length > 0"
                />
            </Modal>
        </div>
    </v-app>
</template>

<script>
import SearchProduct from "../../preorders/SearchProduct.vue";
import CashoutDirectCR from "./CashoutDirectCR.vue";
export default {
    components: { SearchProduct, CashoutDirectCR },
    props: ["total_amount", "details_data", "total_tax",'used_total','replacement_vat'],
    data() {
        return {
            cashout_modal: false,
            btn_loading: false,
            data_array: [],
            form_data: {
                entry_type: "replacement",
                credit_total: this.total_amount,
                credit_tax: this.total_tax,
                row_total: 0,
                row_vat: 0,
                customer_id: this.details_data.customer_id,
                credit_date: this.details_data.credit_date,
                credit_no: this.details_data.credit_no,
            },
        };
    },
    mounted() {
        this.fetchProgress();
        console.log("Component mounted.");
    },
    methods: {
        completeDirectRetailExchangeSales() {
            this.cashout_modal = true;
        },
        async completeExchangeMatch() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/directretail_creditnote/completeExchangeMatch",
                { ...this.form_data, data_array: this.data_array }
            );
            this.hideLoader();

            if (res.status === 200) {
                this.successNotif("Completed Exchange");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        dismissDiag() {
            console.log("dismisses");
            
            this.cashout_modal = false;
             this.$emit("dashboard-active");
        },
        async fetchProgress() {
            this.showLoader();
            const res = await this.getApi(
                "data/directretail_creditnote/fetchReplacementProgress",
                {
                    params: {
                        ...this.form_data,
                    },
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("loaded");
                this.data_array = res.data;
            } else {
                this.form_error(res);
            }
        },
        async deleteRecord(value, i) {
            const confirm = await this.deleteDialogue();
            if (!confirm) {
                return;
            }
            this.showLoader();
            const res = this.callApi(
                "DELETE",
                "data/directretail_creditnote/destroy/" + value.id,
                ""
            );
            this.hideLoader();

            this.s("deleted");
            this.data_array.splice(i, 1);
        },
        async addCart(value) {
            this.form_data.row_total = value.qty * value.selling_price;
            this.form_data.row_vat = this.calculateTax(
                value.tax_rate,
                this.form_data.row_total
            ).toFixed(2);
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/directretail_creditnote/createReplacement",
                {
                    ...this.form_data,
                    ...value,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Added");
                this.data_array = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
    computed: {
        receipt_total() {
            if (this.data_array.length > 0) {
              var total=   (this.data_array
                    .reduce(function (sum, val) {
                        return sum + val.row_total;
                    }, 0))
                   return (parseFloat(this.used_total)+total)
                    
            } else {
                return 0+this.used_total;
            }
        },
        receipt_tax() {
            if (this.data_array.length > 0) {
                var vat= this.data_array
                    .reduce(function (sum, val) {
                        return sum + val.row_vat;
                    }, 0)
                    
                return (parseFloat(vat)+parseFloat(this.replacement_vat))
            } else {
                return 0+this.replacement_vat;
            }
        },
        top_amount() {
            return this.receipt_total - this.form_data.credit_total;
        },

        extra_tax() {
            return this.receipt_tax - this.form_data.credit_tax;
        },
    },
};
</script>
