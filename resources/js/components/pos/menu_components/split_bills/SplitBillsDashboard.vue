<template>
    <div class="container">
        <div class="row justify-content-center" v-if="isReadPermitted">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row d-flex justify-content-between">
                            <button
                                class="btn btn-primary"
                                @click="$router.push('dine_in')"
                            >
                                <b>
                                    <v-icon medium>{{
                                        icons.mdiBackburger
                                    }}</v-icon>
                                    New Order</b
                                >
                            </button>
                            <button
                                class="btn btn-warning"
                                @click="logoutPOS()"
                            >
                                Log out
                            </button>
                            <button
                                @click="$router.push('posmenu')"
                                class="btn btn-secondary"
                            >
                                <v-icon style="color: #fff" medium>{{
                                    icons.mdiHome
                                }}</v-icon
                                >Home
                            </button>
                            <h4><b>Split Bills</b></h4>
                        </div>
                        <div class="row jumbotron">
                            <div class="col-5">
                                <div class="card">
                                    <div class="card-body">
                                        <b v-if="bill_data[0]"
                                            >ORDER NO:
                                            {{ bill_data[0].order_no }}</b
                                        >
                                        <hr />
                                        <div>
                                            <div
                                                :class="[
                                                    value.id,
                                                    split_current === value.id
                                                        ? 'split-active'
                                                        : '',
                                                ]"
                                                class="d-flex flex-column mt-2 mb-2"
                                                v-for="(value, i) in bill_data"
                                                :key="i"
                                                @click="splitItemClicked(value)"
                                            >
                                                <div class="row">
                                                    <div class="col-6">
                                                        <b
                                                            >{{
                                                                value.stock
                                                                    ? value
                                                                          .stock
                                                                          .product_name
                                                                    : "NA"
                                                            }}
                                                            <span
                                                                v-if="
                                                                    value.accompaniment_id
                                                                "
                                                            >
                                                                SERVED WITH
                                                                {{
                                                                    value
                                                                        .accompaniment
                                                                        .product_name
                                                                }}</span
                                                            ></b
                                                        >
                                                    </div>
                                                    <div class="col-2">
                                                        <b> X </b>
                                                        <b> {{ value.qty }}</b>
                                                    </div>
                                                    <div class="col-3">
                                                        <b>
                                                            {{
                                                                cashFormatter(
                                                                    value.row_total
                                                                )
                                                            }}</b
                                                        >
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-start"
                                                >
                                                    <p class="mr-2">
                                                        Unit Price /
                                                    </p>

                                                    <p>
                                                        {{
                                                            cashFormatter(
                                                                value.price
                                                            )
                                                        }}
                                                    </p>
                                                    <p class="ml-2">
                                                        Guest
                                                        <span
                                                            class="badge badge-secondary"
                                                            >{{
                                                                value.no_guest
                                                            }}</span
                                                        >
                                                    </p>
                                                    <p class="ml-2">
                                                        {{ value.notes }}
                                                    </p>
                                                </div>
                                                <hr />
                                            </div>

                                            <div
                                                class="d-flex justify-content-end mt-2"
                                            >
                                                <span>
                                                    <b>
                                                        TOTAL:
                                                        {{
                                                            cashFormatter(
                                                                total_order
                                                            )
                                                        }}</b
                                                    >
                                                </span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-end mt-1"
                                            >
                                                <span>
                                                    <b>
                                                        TAX:
                                                        {{
                                                            cashFormatter(
                                                                total_tax
                                                            )
                                                        }}</b
                                                    >
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2">
                                <button
                                    @click="modalClicked()"
                                    class="btn btn-primary btn-lg"
                                >
                                    <v-icon medium>{{
                                        icons.mdiArrowSplitVertical
                                    }}</v-icon>
                                    Split Bill
                                </button>
                            </div>
                            <div class="col-5">
                                <div class="card">
                                    <div class="card-body">
                                        <b>{{ form_data.split_order_no }}</b>
                                        <div
                                            style="
                                                height: 300px;
                                                overflow-x: hidden;
                                                overflow-y: auto;
                                            "
                                        >
                                            <div
                                                :class="[
                                                    value.id,
                                                    split_current === value.id
                                                        ? 'split-active'
                                                        : '',
                                                ]"
                                                class="d-flex flex-column mt-2 mb-2"
                                                v-for="(
                                                    value, i
                                                ) in split_bill_data"
                                                :key="i"
                                                @click="splitItemClicked(value)"
                                            >
                                                <div class="row">
                                                    <div class="col-6">
                                                        <b
                                                            >{{
                                                                value.stock
                                                                    ? value
                                                                          .stock
                                                                          .product_name
                                                                    : "NA"
                                                            }}
                                                            <span
                                                                v-if="
                                                                    value.accompaniment_id
                                                                "
                                                            >
                                                                SERVED WITH
                                                                {{
                                                                    value
                                                                        .accompaniment
                                                                        .product_name
                                                                }}</span
                                                            ></b
                                                        >
                                                    </div>
                                                    <div class="col-2">
                                                        <b> X </b>
                                                        <b> {{ value.qty }}</b>
                                                    </div>
                                                    <div class="col-3">
                                                        <b>
                                                            {{
                                                                cashFormatter(
                                                                    value.row_total
                                                                )
                                                            }}</b
                                                        >
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-start"
                                                >
                                                    <p class="mr-2">
                                                        Unit Price /
                                                    </p>

                                                    <p>
                                                        {{
                                                            cashFormatter(
                                                                value.price
                                                            )
                                                        }}
                                                    </p>
                                                    <p class="ml-2">
                                                        Guest
                                                        <span
                                                            class="badge badge-secondary"
                                                            >{{
                                                                value.no_guest
                                                            }}</span
                                                        >
                                                    </p>
                                                    <p class="ml-2">
                                                        {{ value.notes }}
                                                    </p>
                                                </div>
                                                <hr />
                                            </div>

                                            <div
                                                class="d-flex justify-content-end mt-2"
                                            >
                                                <span>
                                                    <b>
                                                        TOTAL:
                                                        {{
                                                            cashFormatter(
                                                                split_total_order
                                                            )
                                                        }}</b
                                                    >
                                                </span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-end mt-1"
                                            >
                                                <span>
                                                    <b>
                                                        TAX:
                                                        {{
                                                            cashFormatter(
                                                                split_total_tax
                                                            )
                                                        }}</b
                                                    >
                                                </span>
                                            </div>
                                            <div>
                                                <button
                                                    class="btn btn-warning"
                                                    @click="completeSplit()"
                                                >
                                                    Complete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <notifications group="foo" />
            <Modal v-model="modal.split" title="Split Qty">
                <div class="row">
                    <div class="col-11">
                        <div class="form-group">
                            <label for="">Items Qty</label>
                            <input
                                type="number"
                                data-layout="normal"
                                v-model="form_data.qty"
                                class="form-control"
                            />
                        </div>
                    </div>
                    <custom-board
                        @pressed="form_data.qty = $event"
                        :selfValue="form_data.qty"
                    />
                    <div class="col-11">
                        <div class="form-group">
                            <label for=""> No Guest</label>
                            <input
                                type="number"
                                data-layout="normal"
                                v-model="form_data.no_guest"
                                class="form-control"
                            />
                        </div>
                    </div>

                    <div class="col-9 d-flex justify-content-center">
                        <button
                            class="btn btn-secondary"
                            type="secondary"
                            @click="updateSplit()"
                        >
                            Add Qty
                        </button>
                    </div>
                    <div class="col-10"></div>
                </div>
            </Modal>
        </div>
        <div v-else>
            <unauthorized />
        </div>
        <scroll-widget-component />
    </div>
</template>

<script>
import Unauthorized from "../../../utilities/Unauthorized.vue";
import {
    mdiBackburger,
    mdiNotebookEdit,
    mdiArrowRightBold,
    mdiPrinter,
    mdiArrowSplitVertical,
    mdiBookmarkOutline,
    mdiTrashCanOutline,
    mdiPlusThick,
    mdiMinusThick,
    mdiHome,
} from "@mdi/js";
import ScrollWidgetComponent from "../../../utilities/ScrollWidgetComponent.vue";
import CustomBoard from "../dinerscomponents/CustomBoard.vue";
export default {
    data() {
        return {
            modal: {
                split: false,
            },
            total_order: 0,
            total_tax: 0,
            split_total_order: 0,
            split_total_tax: 0,
            split_current: "",
            split_bill_data: [],
            table_data: [],
            bill_data: [],
            location_data: [],
            form_data: {
                accompaniment_id:null,
                id: null,
                order_no: "",
                split_order_no: "",
                qty: 0,
                order_qty: 0,
                stock_id: null,
                department_id: null,
                price: 0,
                row_total: 0,
                row_vat: 0,
                no_guest: 1,
                tax_rate: 18,
                
            },
            icons: {
                mdiBackburger,
                mdiNotebookEdit,
                mdiArrowRightBold,
                mdiPrinter,
                mdiArrowSplitVertical,
                mdiBookmarkOutline,
                mdiTrashCanOutline,
                mdiPlusThick,
                mdiMinusThick,
                mdiHome,
            },
        };
    },
    watch: {
        bill_data: {
            deep: true,
            handler(val, old) {
                this.total_order = this.bill_data.reduce(function (sum, val) {
                    return sum + val.row_total;
                }, 0);
                this.total_tax = this.bill_data.reduce(function (sum, val) {
                    return sum + val.row_vat;
                }, 0);
            },
        },
        split_bill_data: {
            deep: true,
            handler(val, old) {
                this.split_total_order = this.split_bill_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.row_total;
                },
                0);
                this.split_total_tax = this.split_bill_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.row_vat;
                },
                0);
            },
        },
    },
    mounted() {
        this.form_data.order_no = this.$store.state.order_no;
        this.concurrentApiReq();
    },
    components: {
        Unauthorized,
        ScrollWidgetComponent,
        CustomBoard,
    },
    methods: {
        async completeSplit() {
            const order_no = this.form_data.split_order_no;
            const res = await this.getApi("data/pos_order/completeSplitOrder", {
                params: {
                    order_no,
                },
            });
            if (res.status == 200) {
                this.form_data.split_order_no = res.data;
                this.split_bill_data = [];
            } else {
                this.servo();
            }
        },
        async updateSplit() {
            if (!this.form_data.stock_id) {
                this.swr("Select Order");
                return;
            }
            if (this.form_data.order_qty < this.form_data.qty) {
                this.swr("Qty  surpassed the order ");
                return;
            }
            this.form_data.row_total =
                this.form_data.price * this.form_data.qty;
            this.form_data.row_vat = this.calculateTax(
                this.form_data.tax_rate,
                this.form_data.row_total
            );
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/pos_order/splitOrder",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Successfully Saved");
                this.modal.split = false;
                (this.form_data.stock_id = null),
                    (this.split_bill_data = res.data.res_new);
                this.bill_data = res.data.res_prev;
                this.split_current = "";
            } else {
                this.form_error(res);
            }
        },
        splitItemClicked(value) {
            this.form_data.accompaniment_id=value.accompaniment_id
            this.form_data.id = value.id;
            this.form_data.order_qty = value.qty;
            this.form_data.stock_id = value.stock_id;
            this.form_data.department_id = value.department_id;
            
            (this.form_data.location_id = value.location_id),
                (this.form_data.price = value.price);
            this.form_data.tax_rate = value.stock.tax_dept.tax_rate;
            this.split_current = value.id;
        },
        modalClicked() {
            if (!this.form_data.stock_id) {
                this.swr("Select Order");
                return;
            }
            this.modal.split = true;
        },
        async getLocations() {
            const res = await this.getApi("data/location/fetch", {
                params: {
                    search: this.search,
                },
            });

            if (res.status == 200) {
                this.location_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async getTables() {
            const res = await this.getApi("data/table/fetch", {
                params: {
                    search: this.search,
                },
            });

            if (res.status == 200) {
                this.table_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async getByOrder() {
            const res = await this.getApi("data/pos_order/getByOrder", {
                params: {
                    order_no: this.form_data.order_no,
                },
            });
            if (res.status == 200) {
                this.bill_data = res.data;
            } else {
                this.servo();
            }
        },
        async getPendingOrders() {
            const res = await this.callApi(
                "POST",
                "data/pos_order/getSplitPendingOrders",
                {
                    order_no: this.form_data.order_no,
                }
            );

            if (res.status == 200) {
                if (res.data.status == "orders") {
                    this.split_bill_data = res.data.results;
                    this.form_data.split_order_no =
                        this.split_bill_data[0].order_no;
                }
                if (res.data.status == "order_no") {
                    this.split_bill_data = [];
                    this.form_data.split_order_no = res.data.results;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getByOrder(),
                this.getPendingOrders(),
                this.getLocations(),
                this.getTables(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
    },
};
</script>
<style scoped>
.split-active {
    background: #bec5b7 !important;
}
</style>
