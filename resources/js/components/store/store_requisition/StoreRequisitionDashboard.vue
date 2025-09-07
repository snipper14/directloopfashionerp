<template>
    <div class="row">
        <div class="col-md-2">
            <StoreReqNav />
        </div>
        <div class="col-md-10" v-if="isReadPermitted">
            <fieldset class="border">
                <legend class="text-center">Store Product Requisition</legend>

                <div class="row">
                    <div class="col-md-3">
                        <label>REQ NO:</label>
                        <input
                            type="text"
                            class="form-control"
                            disabled
                            v-model="form_data.req_id"
                        />
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Due Date *</label>
                                <date-picker
                                    v-model="form_data.date_due"
                                    valueType="format"
                                    type="datetime"
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
                    <div class="col-3 border border-secondary">
                        <div class="form-group">
                            <label>Code. </label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="search_query.code"
                                autocomplete="new-user-street-address"
                                placeholder="Code"
                            />
                        </div>
                        <div class="form-group">
                            <label>Product Name </label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="search_query.product_name"
                                autocomplete="new-user-street-address"
                                placeholder="Product Name"
                            />
                        </div>
                        <button
                            class="btn btn-secondary btn-sm"
                            @click="search_results = []"
                        >
                            Clear Search
                        </button>
                    </div>
                    <div class="col-8 border border-secondary">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item Name</th>

                                    <th scope="col">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in search_results"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.code }}
                                    </td>
                                    <td>{{ data.product_name }}</td>

                                    <td>
                                        <input type="text" v-model="data.qty" />
                                    </td>

                                    <td>
                                        <button
                                            v-if="isWritePermitted"
                                            class="btn btn-secondary btn-sm"
                                            @click="submitRecords(data)"
                                        >
                                            Add Items
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="req_data">
                            <center><b>Pending Requisition List</b></center>
                            <table
                                class="table table-sm table-striped table-bordered mt-3"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>

                                        <th scope="col">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in req_data"
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
                                                    ? data.stock.product_name
                                                    : ""
                                            }}
                                        </td>

                                        <td>
                                            <input
                                                type="number"
                                                v-model="data.qty"
                                            />
                                        </td>

                                        <td>
                                            <router-link
                                                v-if="isUpdatePermitted"
                                                to="#"
                                                @click.native="
                                                    updateData(data, i)
                                                "
                                            >
                                                Update /
                                            </router-link>
                                            <router-link
                                                v-if="isDeletePermitted"
                                                class="danger"
                                                to="#"
                                                @click.native="
                                                    deleteData(data.id, i)
                                                "
                                            >
                                                Delete
                                            </router-link>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>

                                        <td><b>Total Items</b></td>
                                        <td>
                                            <b>{{
                                                cashFormatter(total_items)
                                            }}</b>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>

                                        <td>
                                            <button
                                                type="button"
                                                v-if="isWritePermitted"
                                                class="btn btn-primary btn-sm"
                                                @click="completeRequisition()"
                                            >
                                                Complete Requisition
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- </form> -->
            </fieldset>
        </div>
        <div v-else><unauthorized /></div>
    </div>
</template>
<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import StoreReqNav from "./StoreReqNav.vue";
export default {
    components: { Unauthorized, StoreReqNav },

    data() {
        return {
            search_query: {
                code: "",
                product_name: "",
            },
            dept_data: null,
            req_data: null,
            total_items: 0,
            total_amount: 0,
            form_data: {
                approved: 1,
                req_id: "",
                date_due: "",
                stock_id: null,
                qty: 0,
            },

            search_results: [],
        };
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },

        req_data: {
            deep: true,
            handler() {
                this.total_amount = this.req_data.reduce(function (sum, val) {
                    return sum + val.unit_price * val.qty;
                }, 0);
                this.total_items = this.req_data.reduce(function (qty, val) {
                    return qty + val.qty;
                }, 0);
            },
        },
    },
    mounted() {
        this.form_data.date_due = this.getDateTime();
        this.fetchPendingReq();
        this.fetchDept();
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
        async updateData(data) {
            this.showLoader();
            const res = await this.callApi(
                "put",
                "data/store_requisition/updateReqData",
                data
            );

            this.hideLoader();
            if (res.status === 200) {
                this.s("updated successfully");
                this.req_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async completeRequisition() {
            this.showLoader();
            const dataObj = {
                req_id: this.form_data.req_id,
            };
            const res = await this.callApi(
                "POST",
                "data/store_requisition/completeReq",
                dataObj
            );
            this.hideLoader()
            if (res.status == 200) {
                this.successNotif("Successfully Created");
                this.req_data=[]
                this.form_data.req_id=res.data.req_no
            } else {
                this.form_error(res);
            }
        },

        async fetchPendingReq() {
            this.showLoader();
            const res = await this.getApi(
                "data/store_requisition/fetchPendngReq"
            );
            this.hideLoader();
            if (res.status === 200) {
                this.req_data = res.data.pending_req;
                if (this.req_data.length > 0) {
                    this.form_data.req_id = this.req_data[0].req_id;
                    this.form_data.date_due = this.req_data[0].date_due;

                    this.form_data.details = this.req_data[0].details;
                    this.form_data.department_id =
                        this.req_data[0].department_id;
                } else {
                    this.form_data.req_id = res.data.req_no;
                }
            } else {
                this.swr("Server error try later");
            }
        },
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/store_requisition/deleteItem/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.req_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async submitRecords(data) {
            this.form_data.unit_price = data.cost_price;
            this.form_data.stock_id = data.id;
            this.form_data.qty = data.qty;
            var format_date = this.formatDateTime(this.form_data.date_due);
            this.form_data.date_due = format_date;

            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/store_requisition/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.s("Record added successfully");

                this.req_data = res.data;
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
                };
            });
            return modif;
        },
    },
};
</script>
