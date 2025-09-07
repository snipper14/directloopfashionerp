<template>
    <div>
        <fieldset class="border">
            <legend class="text-center">
                Single Product Requisition
            </legend>

            <div class="row">
                <div class="col-3 ">
                    <label>REQ NO:</label>
                    <input
                        type="text"
                        class="form-control "
                        disabled
                        v-model="form_data.req_id"
                    />
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label for="">Priority</label>
                        <select
                            class="form-control"
                            v-model="form_data.priority"
                        >
                            <option value="normal">
                                Normal
                            </option>
                            <option value="high">
                                High
                            </option>
                            <option value="low">
                                Low
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-3">
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
                    <label>Details:</label>
                    <input
                        type="text"
                        class="form-control "
                        v-model="form_data.details"
                    />
                </div>
                <div class="col-3">
                    <div class="form-group d-flex flex-column">
                        <label for="">Department*</label>

                        <Select
                            v-model="form_data.department_id"
                            style="width:200px"
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
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Code</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Cost Price</th>

                                <th scope="col">Qty</th>
                                <th scope="col">Sub Total</th>
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
                                <td>{{ data.cost_price }}</td>

                                <td>
                                    <input type="text" v-model="data.qty" />
                                </td>
                                <td>
                                    {{ data.cost_price * data.qty }}
                                </td>
                                <td>
                                    <button
                                        
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
                                    <th scope="col">Unit Price</th>

                                    <th scope="col">Qty</th>
                                    <th scope="col">Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in req_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock ? data.stock.code : "" }}
                                    </td>
                                    <td>
                                        {{
                                            data.stock
                                                ? data.stock.product_name
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.unit_price) }}
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            v-model="data.qty"
                                        />
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.unit_price * data.qty
                                            )
                                        }}
                                    </td>
                                    <td>
                                        <router-link
                                            v-if="isUpdatePermitted"
                                            to="#"
                                            @click.native="updateData(data, i)"
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
                                    <td></td>
                                    <td></td>
                                    <td><b>Total Items</b></td>
                                    <td>
                                        <b>{{ cashFormatter(total_items) }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b>TOTAL Amount</b></td>
                                    <td>
                                        <b>{{ cashFormatter(total_amount) }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <button
                                            type="button"
                                            v-if="isWritePermitted"
                                            class="btn btn-primary btn-sm "
                                            @click="downloadReq()"
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
</template>
<script>
export default {
    props: ["req_id", "dept_data"],
    data() {
        return {
            search_query: {
                code: "",
                product_name: ""
            },
            req_data: null,
            total_items: 0,
            total_amount: 0,
            form_data: {
                department_id: null,
                req_id: "",
                date_due: "",
                product_type: "single",
                details: "",
                priority: "normal",
                stock_id: null,
                qty: 0,
                unit_price: 0
            },

            search_results: []
        };
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function(val, old) {
                this.searchProduct();
            }, 500)
        },

        req_data: {
            deep: true,
            handler() {
                this.total_amount = this.req_data.reduce(function(sum, val) {
                    return sum + val.unit_price * val.qty;
                }, 0);
                this.total_items = this.req_data.reduce(function(qty, val) {
                    return qty + val.qty;
                }, 0);
            }
        }
    },
    mounted() {
        this.fetchPendingReq();
    },
    methods: {
        async updateData(data) {
            this.showLoader();
            const res = await this.callApi(
                "put",
                "data/requisition/updateReqData",
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
        async downloadReq() {
            this.showLoader();
            const dataObj = {
                req_id: this.form_data.req_id
            };
            axios({
                url: "data/requisition/downloadReq",
                method: "POST",
                data: dataObj,
                responseType: "blob" // important
            }).then(response => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.form_data.req_id + "_RQ" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                this.$router.push("single_product_requisition_dashboard");
            });
        },

        async fetchPendingReq() {
            this.showLoader();
            const res = await this.getApi("data/requisition/fetchPendngReq");
            this.hideLoader();
            if (res.status === 200) {
                this.req_data = res.data;
                if (this.req_data.length > 0) {
                    this.form_data.req_id = this.req_data[0].req_id;
                    this.form_data.date_due = this.req_data[0].date_due;
                    this.form_data.product_type = this.req_data[0].product_type;
                    this.form_data.priority = this.req_data[0].priority;
                    this.form_data.details = this.req_data[0].details;
                    this.form_data.department_id = this.req_data[0].department_id;
                } else {
                    this.form_data.req_id = this.req_id;
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
                    "data/requisition/deleteItem/" + id,
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
            var tax_total = this.calculateTax(
                this.taxes,
                this.form_data.sub_total
            );

            this.form_data.tax_rate = this.taxes;
            this.form_data.taxes = tax_total;

            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/requisition/create",
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
                        ...this.search_query
                    }
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },
        modifyResData(data) {
            let modif = data.map(obj => {
                return {
                    id: obj.id,
                    qty: 1,
                    code: obj.code,
                    product_name: obj.product_name,
                    cost_price: obj.cost_price
                };
            });
            return modif;
        }
    }
};
</script>
