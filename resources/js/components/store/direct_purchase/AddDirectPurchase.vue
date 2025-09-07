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
                                            v-model="form_data.order_date"
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group d-flex flex-column">
                                    <label for="">Department*</label>

                                    <Select
                                        v-model="form_data.department_id"
                                        style="width: 200px"
                                    >
                                        <Option
                                            v-for="item in dept_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="row">
                                <product-search-cost-price-main-store
                                    @stockSearchResult="addCart"
                                />
                            </div>

                            <div class="col-12">
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
    props: ["pending_order"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            total_order: 0,
            search_results: [],
            taxes: 0,
            dept_data: 0,
            order_data: [],
            search_query: {
                code: "",
                product_name: "",
            },
            select_data: {
                supplier_options: null,
            },
            form_data: {
                order_no: "",
                order_deadline: "",
                order_date: "",
                sub_total: 0,
                unit_price: 0,
                qty: 0,
                stock_id: null,
                department_id: null,
                progress: "progress",
                tax_rate: 0,
            },
        };
    },
    components: {
        Treeselect,
        ProductSearchCostPriceMainStore,
        ProductSearchCostPrice,
    },
    mounted() {
        this.fetchDept();

        this.form_data.order_deadline = this.getCurrentDate();
        this.form_data.order_date = this.getCurrentDate();
        if (this.pending_order === null) {
            this.fetchOrderNo();
        } else {
            this.form_data.order_no = this.pending_order.order_no;
            this.form_data.order_deadline = this.pending_order.order_deadline;
            this.form_data.order_date = this.pending_order.order_date;
            this.form_data.department_id = this.pending_order.department_id;
            this.form_data.progress = this.pending_order.progress;
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
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchOrderNo() {
            this.showLoader();
            const res = await this.getApi("data/direct_po/fetchOrderNo", "");
            this.hideLoader();
            if (res.status === 200) {
                this.form_data.order_no = res.data;
            } else {
                this.swr("server error try later");
            }
        },
        async addCart(value) {
            this.form_data.stock_id = value.id;
            this.form_data.name = value.product_name;
            this.form_data.qty = value.qty;
            this.form_data.unit_price = value.purchase_price;
            this.form_data.sub_total =
                this.form_data.unit_price * this.form_data.qty;

            var tax_total = this.calculateTax(
                value.tax_rate,
                this.form_data.sub_total
            );

            this.form_data.tax_rate = value.tax_rate;
            this.form_data.taxes = tax_total;
            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/direct_po/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async getDept() {
            const res = await this.getApi("data/");
        },
        async cancelOrders() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/direct_po/destroy", {
                    params: {
                        order_no: this.form_data.order_no,
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
            if (this.form_data.progress !== "completed") {
                this.errorNotif("Complete order first");
                return;
            }
            const approve = await this.confirmDialogue(
                "Do you want to approve orders"
            );
            if (approve) {
                this.showLoader();
                const res = await this.callApi(
                    "post",
                    "data/direct_po/approveOrder",
                    {
                        order_no: this.form_data.order_no,
                    }
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.successNotif("Approved ");
                    this.$emit("dashboard-active");
                }
            }
        },
        async fetchOrders() {
            this.showLoader();
            const res = await this.getApi("data/direct_po/fetchByOrder", {
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
                    "data/direct_po/deleteOrder/" + id,
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
            const res = await this.callApi(
                "post",
                "data/direct_po/completeOrder",
                {
                    order_no: this.form_data.order_no,
                }
            );
            this.hideLoader();
            if (res.status == "200") {
                this.successNotif("Record added successfully");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
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
                    tax_rate: obj.tax_dept.tax_rate,
                };
            });
            return modif;
        },
        async fetchOrderNo() {
            const res = await this.getApi("data/direct_po/fetchOrderNo", "");
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
