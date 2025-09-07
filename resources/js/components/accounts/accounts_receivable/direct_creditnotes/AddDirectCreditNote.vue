<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Direct Cr</div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="">Credit No</label>
                                    <input
                                        type="text"
                                        v-model="form_data.credit_no"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="">Credit Date</label>
                                    <date-picker
                                        v-model="form_data.credit_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="">Notes</label>
                                    <textarea
                                        name=""
                                        id=""
                                        class="form-control"
                                        v-model="form_data.notes"
                                    ></textarea>
                                </div>

                                <div class="col-md-12">
                                    <product-search-selling-price
                                        @stockSearchResult="submitResult"
                                    />
                                </div>
                                <div class="col-md-12">
                                    <h4>Pending Completion</h4>
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered"
                                        >
                                            <thead>
                                                <tr>
                                                    <th scope="col">Item</th>

                                                    <th scope="col">Price</th>
                                                    <th scope="col">qty</th>

                                                    <th scope="col">Vat</th>
                                                    <th scope="col">Total</th>
                                                    <th>Action</th>
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
                                                        {{ data.stock.name }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.price
                                                            )
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.qty
                                                            )
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.row_vat
                                                            )
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.line_total
                                                            )
                                                        }}
                                                    </td>
                                                    <td>
                                                        <v-btn
                                                            @click="
                                                                destroyRec(
                                                                    data.id,
                                                                    i
                                                                )
                                                            "
                                                            >DEL</v-btn
                                                        >
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">
                                                        <b> Total VAT</b>
                                                    </td>
                                                    <td>
                                                        <b>{{
                                                            cashFormatter(
                                                                total_vat_amount
                                                            )
                                                        }}</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">
                                                        <b>Total Amount</b>
                                                    </td>
                                                    <td>
                                                        <b>{{
                                                            cashFormatter(
                                                                total_amount
                                                            )
                                                        }}</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <v-btn
                                                            color="info"
                                                            large
                                                            @click="
                                                                completeCreditNote()
                                                            "
                                                            >Complete
                                                            Creditnote</v-btn
                                                        >
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div></v-app
        >
    </div>
</template>

<script>
import ProductSearchCostPrice from "../../../utilities/ProductSearchCostPrice.vue";
import ProductSearchSellingPrice from "../../../utilities/ProductSearchSellingPrice.vue";
export default {
    props: ["customer_details"],
    components: { ProductSearchCostPrice, ProductSearchSellingPrice },
    data() {
        return {
            credit_note_data: [],
            form_data: {
                customer_id: null,
                credit_date: null,
                credit_no: null,
                notes: "",
                qty: 0,
                row_vat: 0,
                price: 0,
                line_total: 0,
                stock_id: null,
            },
            total_vat_amount: 0,
            total_amount: 0,
        };
    },
    watch: {
        credit_note_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.total_amount = this.credit_note_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.line_total;
                },
                0);

                this.total_vat_amount = this.credit_note_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.row_vat;
                },
                0);
            }, 100),
        },
    },
    mounted() {
        this.form_data.customer_id = this.customer_details.id;
        this.form_data.credit_date = this.getCurrentDate();
        this.concurrentApiReq();
    },
    methods: {
        async completeCreditNote() {
            this.showLoader();

            Object.assign(this.form_data, {
                total_amount: this.total_amount,
                total_vat_amount: this.total_vat_amount,
            });
            const res = await this.callApi(
                "POST",
                "data/direct_credit_note/complete",

                Object.assign(this.form_data, {
                    total_amount: this.total_amount,
                    total_vat_amount: this.total_vat_amount,
                })
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("completed sucessfully");
                this.$emit("dismiss-diag");
            } else {
                this.form_error(res);
            }
        },
        async destroyRec(id, i) {
            const res = await this.callApi(
                "DELETE",
                "data/direct_credit_note/destroy/" + id,

                {}
            );
            if (res.status === 200) {
                this.s("deleted");
                this.credit_note_data.splice(i, 1);
            } else {
                this.form_error(res);
            }
        },
        async submitResult(data) {
            this.form_data.stock_id = data.id;
            this.form_data.qty = data.qty;
            this.form_data.price = data.selling_price;

            this.form_data.line_total =
                this.form_data.price * this.form_data.qty;
            this.form_data.row_vat = this.calculateTax(
                parseFloat(data.tax_rate),
                parseFloat(this.form_data.line_total)
            );

            const res = await this.callApi(
                "POST",
                "data/direct_credit_note/create",

                this.form_data
            );
            if (res.status === 200) {
                this.s("saved");
                this.credit_note_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.showLoader();

            await Promise.all([this.fetchPendingCr()]);

            this.hideLoader();
        },
        async fetchPendingCr() {
            const res = await this.getApi(
                "data/direct_credit_note/fetchPendingCr",
                {
                    params: {
                        ...this.form_data,
                    },
                }
            );

            if (res.status === 200) {
                if (res.data.res.length > 0) {
                    this.credit_note_data = res.data.res;
                    this.form_data.credit_no =
                        this.credit_note_data[0].credit_no;
                } else {
                    this.form_data.credit_no = res.data.credit_no;
                }
                console.log(JSON.stringify(res.data));
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
