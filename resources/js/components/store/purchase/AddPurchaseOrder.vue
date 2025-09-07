<template>
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
                <div class="card-header"></div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">New Purchase Order</legend>

                        <div class="row">
                            <div
                                class="col-3 d-flex justify-content-center align-items-center"
                            >
                                <label>ORDER NO:</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    disabled
                                    v-model="form_data.order_no"
                                />
                            </div>
                            <div class="col-3">
                                <label>Supplier Name*</label>

                                <div class="form-group">
                                    <treeselect
                                        v-model="form_data.supplier_id"
                                        :multiple="false"
                                        :options="select_data.supplier_options"
                                        :show-count="true"
                                        placeholder="Select "
                                    />
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="">Vendor Ref. No</label>
                                    <input
                                        type="text"
                                        v-model="form_data.vendor_reference"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-2 d-flex align-items-center">
                                <button
                                    v-if="isDeletePermitted"
                                    @click="cancelOrders()"
                                    class="btn btn-danger btn-sm"
                                >
                                    Cancel Order
                                </button>
                            </div>
                            <div class="col-2 d-flex align-items-center">
                                <button
                                    v-if="isApprovePermitted"
                                    @click="approveOrder()"
                                    class="btn btn-secondary btn-sm"
                                >
                                    Approve Order
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label>Deadline Date *</label>
                                        <date-picker
                                            v-model="form_data.order_deadline"
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label>Receipt Date *</label>
                                        <date-picker
                                            v-model="form_data.receipt_date"
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label for=""
                                        >Shipping Address<span
                                            style="color: red"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        type="text"
                                        v-model="form_data.shipping_address"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <product-search-cost-price-main-store
                                @stockSearchResult="submitRecords"
                            />
                        </div>
                        <div class="row">
                            <div class="col-8 border border-secondary">
                                <center><b>PURCHASEORDERS</b></center>
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>
                                            <th scope="col">Unit Price</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in order_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{
                                                    data.stock
                                                        ? data.stock.code
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.stock
                                                        ? data.stock
                                                              .product_name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.unit_price
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    v-model="data.qty"
                                                />
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.sub_total
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <button
                                                    v-if="isWritePermitted"
                                                    class="btn btn-danger btn-sm"
                                                    @click="
                                                        deleteData(data.id, i)
                                                    "
                                                >
                                                    Delete Item
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>TOTAL ORDERS</b></td>
                                            <td>
                                                <b>{{
                                                    cashFormatter(total_order)
                                                }}</b>
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    v-if="isWritePermitted"
                                                    class="btn btn-primary btn-sm"
                                                    @click="completePO()"
                                                >
                                                    Complete Purchase Orders
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- </form> -->
                    </fieldset>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import _ from "lodash";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ProductSearchCostPrice from "../../utilities/ProductSearchCostPrice.vue";
import ProductSearchCostPriceMainStore from "../../utilities/ProductSearchCostPriceMainStore.vue";
export default {
    props: ["supplier_data", "tax_data", "pending_order"],
    components: {
        Treeselect,
        ProductSearchCostPrice,
        ProductSearchCostPriceMainStore,
    },
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            total_order: 0,
            search_results: [],
            taxes: 0,
            order_data: [],
            search_query: {
                code: "",
                product_name: "",
            },
            select_data: {
                supplier_options: null,
            },
            form_data: {
                supplier_id: null,
                order_no: "",
                vendor_reference: "",
                order_deadline: "",
                receipt_date: "",
                shipping_address: "",
                taxes: 0,
                tax_rate: 0,
                sub_total: 0,
                unit_price: 0,
                qty: 0,
                stock_id: null,
            },
        };
    },

    mounted() {
        this.form_data.shipping_address = this.$store.getters.getBranch.address;
        this.select_data.supplier_options = this.modifyProductKey();
        this.form_data.receipt_date = this.getCurrentDate();
        this.form_data.order_deadline = this.getCurrentDate();
        if (this.pending_order === null) {
            this.fetchOrderNo();
        } else {
            this.form_data.order_no = this.pending_order.order_no;
            this.form_data.supplier_id = this.pending_order.supplier_id;
            this.form_data.order_deadline = this.pending_order.order_deadline;
            this.form_data.receipt_date = this.pending_order.receipt_date;
            this.form_data.vendor_reference =
                this.pending_order.vendor_reference;
            this.form_data.shipping_address =
                this.pending_order.shipping_address;
            this.fetchOrders();
        }
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
        order_data: {
            deep: true,
            handler() {
                this.total_order = this.order_data.reduce(function (sum, val) {
                    return sum + val.sub_total;
                }, 0);
            },
        },
    },
    methods: {
        async cancelOrders() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/po/destroy", {
                    params: {
                        order_no: this.form_data.order_no,
                        supplier_id: this.form_data.supplier_id,
                    },
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.$emit("dashboard-active");
                }
            }
        },
        async approveOrder() {
            const approve = await this.confirmDialogue(
                "Do you want to approve orders"
            );
            if (approve) {
                this.showLoader();
                const res = await this.getApi("data/po/approveOrder", {
                    params: {
                        order_no: this.form_data.order_no,
                        supplier_id: this.form_data.supplier_id,
                        total_order: this.total_order,
                    },
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Approved ");
                    this.$emit("dashboard-active");
                }
            }
        },
        async fetchOrders() {
            this.showLoader();
            const res = await this.getApi("data/po/fetchByOrder", {
                params: {
                    order_no: this.form_data.order_no,
                    supplier_id: this.form_data.supplier_id,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.order_data = res.data;
            }
        },
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/po/deleteOrder/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.order_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async completePO() {
            this.showLoader();
            const res = await this.callApi("post", "data/po/completeOrder", {
                order_no: this.form_data.order_no,
               supplier_id:  this.form_data.supplier_id
            });
            this.hideLoader();
            if (res.status == "200") {
                this.s("Record added successfully");
                this.$emit("dashboard-active");
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async submitRecords(value) {
         
            this.form_data.stock_id = value.id;
            this.form_data.name = value.product_name;
            this.form_data.qty = value.qty;
            this.form_data.unit_price = value.purchase_price;
            this.form_data.sub_total =value.sub_total
                //this.form_data.unit_price * this.form_data.qty;
            this.form_data.tax_rate = value.tax_rate;
            var tax_total = this.calculateTax(
                this.form_data.tax_rate,
                this.form_data.sub_total
            );

            this.form_data.taxes = tax_total;

            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/po/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.s("Record added successfully");
                this.order_data = res.data;
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query,
                    },
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    qty: 1,
                    code: obj.code,
                    product_name: obj.product_name,
                    cost_price: obj.cost_price,
                };
            });
            return modif;
        },
        async fetchOrderNo() {
            const res = await this.getApi("data/po/fetchOrderNo", "");
            if (res.status === 200) {
                this.form_data.order_no = res.data;
            } else {
                this.swr("server error try later");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([]);
            this.hideLoader();
        },
        modifyProductKey() {
            let modif = this.supplier_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.company_name,
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
</style>
