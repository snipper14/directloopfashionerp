<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            Add PO {{ details_data.stock.product_name }}
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="form-group d-flex flex-column">
                                    <label for=""> Search Supplier</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchSupplier"
                                        :options="supplier_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.supplier_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Search Supplier "
                                    />
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for="">Order Deadline</label>

                                    <date-picker
                                        v-model="form_data.order_deadline"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for="">Receipt Date</label>

                                    <date-picker
                                        v-model="form_data.receipt_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for=""
                                        >Qty<span style="color: red"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        type="number"
                                        v-model="form_data.qty"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for=""
                                        >Purchase Price<span style="color: red"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        type="number"
                                        v-model="form_data.unit_price"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for=""
                                        >Total Vat<span style="color: red"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        disabled
                                        type="number"
                                        v-model="form_data.taxes"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-10 form-group">
                                    <label for=""
                                        >Total Amount<span style="color: red"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        disabled
                                        type="number"
                                        v-model="form_data.sub_total"
                                        class="form-control"
                                    />
                                </div>
                                <div
                                    class="col-md-10 form-group"
                                    style="padding-top: 0"
                                >
                                    <label for="">Shipping Address</label>
                                    <input
                                        type="text"
                                        v-model="form_data.shipping_address"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-10 form-group">
                                    <v-btn
                                        color="secondary"
                                        :loading="btn_loader"
                                        @click="submitRecords()"
                                        >SAVE</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </v-app>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    components: { Treeselect },
    props: ["details_data"],
    data() {
        return {
            btn_loader: false,
            form_data: {
                supplier_id: null,
                vendor_reference: "",
                order_deadline: null,
                receipt_date: null,
                stock_id: null,
                qty: 0,
                unit_price: 0,
                taxes: 0,
                tax_rate: 0,
                sub_total: 0,
                shipping_address: "",
            },
        };
    },
    watch: {
        form_data: {
            handler() {
                this.form_data.sub_total = (
                    parseFloat(this.form_data.unit_price) *
                    parseFloat(this.form_data.qty)
                ).toFixed(2);
                this.form_data.taxes = this.calculateTax(
                    this.form_data.tax_rate,
                    this.form_data.sub_total
                ).toFixed(2);
            },
            deep: true,
        },
    },
    mounted() {
        this.fetchSupplier();
        this.form_data.order_deadline = this.getCurrentDate();
        this.form_data.receipt_date = this.getCurrentDate();
        this.form_data.stock_id = this.details_data.stock_id;
        this.form_data.unit_price = this.details_data.stock.purchase_price;
        this.form_data.supplier_id = this.details_data.stock.supplier_id;
        this.form_data.tax_rate = this.details_data.stock.tax_dept.tax_rate;
        this.form_data.shipping_address = this.$store.getters.getBranch.address;
        console.log(JSON.stringify(this.details_data));
    },
    methods: {
        async submitRecords() {
            this.btn_loader = true;
            const res = await this.callApi(
                "post",
                "data/temp_po/create",
                this.form_data
            );
            this.btn_loader = false;
            if (res.status === 200) {
                this.s("saved");
                this.$emit("dismiss-diag");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
