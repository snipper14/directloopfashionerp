<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
            <div class="card">
                <div class="card-header">
                    <h3><b>ORDERS RETURNS (Accounting)</b></h3>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6>Return Code: {{ form_data.return_code }}</h6>
                        <h6>Order No: {{ order_details.order_no }}</h6>
                        <h6>
                            Vendor: {{ order_details.supplier.company_name }}
                        </h6>

                        <div class="form-group">
                            <div class="form-group d-flex flex-column">
                                <label>Return Date *</label>
                                <date-picker
                                    v-model="form_data.return_date"
                                    valueType="format"
                                ></date-picker>
                            </div>
                        </div>
                    </div>
                    <table
                        class="table table-sm table-striped table-bordered mt-3"
                    >
                        <thead>
                            <tr>
                                <th scope="col">Code</th>
                                <th scope="col">Item Name</th>
                               

                                <th scope="col">Qty Ordered</th>
                                <th scope="col">Qty Delivered</th>
                                <th>Notes</th>
                                <th scope="col">Qty Returned</th>
                                <th>Deduct Stock</th>
                                <td>Receive</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in modify_results"
                                :key="i"
                            >
                                <td>
                                    {{ data.code }}
                                </td>
                                <td>
                                    {{ data.product_name }}
                                </td>
                              

                                <td>
                                    {{ cashFormatter(data.qty_ordered) }}
                                </td>
                                <td>
                                    {{ cashFormatter(data.qty_delivered) }}
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="data.details"
                                    />
                                </td>
                                <td>
                                    <input
                                        class="form-control"
                                        type="number"
                                        v-model="data.qty"
                                    />
                                </td>
                                <td>
                                     <input
                                        type="checkbox"
                                        v-model="data.deduct_stock"
                                    />
                                </td>
                                <td>
                                    <button
                                        v-if="isWritePermitted"
                                        class="btn btn-secondary btn-sm"
                                        @click="returnPurchases(data)"
                                    >
                                        Return
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered mt-3"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item Name</th>
                                    <th scope="col">Unit Price</th>

                                    <th scope="col">Qty Returned</th>

                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in peding_returns"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock ? data.stock.code : "" }}
                                    </td>
                                    <td>
                                        {{ data.stock ? data.stock.name : "" }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.unit_price)
                                        }}
                                    </td>

                                    <td>
                                        {{ data.qty }}
                                    </td>

                                    <td>
                                        <button
                                        v-if="isDeletePermitted"
                                            class="btn btn-danger btn-sm"
                                            @click="delRecord(data, i)"
                                        >
                                            <v-icon dark left>
                                                mdi-trash </v-icon
                                            >Trash
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <button
                                            class="btn btn-primary btn-sm"
                                            v-if="isDownloadPermitted"
                                            @click="download()"
                                        >
                                            Complete 
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    props: ["order_details"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            total_order: 0,
            modify_results: [],
            taxes: 0,
            order_data: [],
            peding_returns: [],
            select_data: {
                supplier_options: null
            },
            form_data: {
                deduct_stock:false,
                supplier_id: null,
                stock_id: null,
                order_no: "",
                qty: 0,
                return_code: "",
                return_date: "",
                details: "",
                unit_price:0,
            }
        };
    },
    watch: {},

    mounted() {
        this.form_data.order_no = this.order_details.order_no;
        this.form_data.supplier_id = this.order_details.supplier_id;

        this.form_data.return_date = this.getCurrentDate();
        this.concurrentApiReq();
    },

    methods: {
        async delRecord(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "POST",
                    "data/purchase_returns/destroy/" + data.id,
                    ""
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.peding_returns.splice(i, 1);
                    this.fetchOrders();
                } else {
                    this.swr("Server error try later!!");
                }
            }
        },
        async download() {
            this.showLoader();
            const dataObj = {
                return_code: this.form_data.return_code,
                supplier_id: this.form_data.supplier_id,
             
            };
            axios({
                url: "data/purchase_returns/downloadReturns",
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
                    this.getTimeStamp() + "_RN" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
                this.$emit("dashboard-active");
            });
        },

        async returnPurchases(data) {
            this.form_data.stock_id = data.stock_id;
            this.form_data.qty = data.qty;
            this.form_data.stock_id = data.stock_id;
            this.form_data.details = data.details;
            this.form_data.unit_price=data.unit_price
            this.form_data.deduct_stock=data.deduct_stock
            this.form_data.return_date = this.formatDate(
                this.form_data.return_date
            );
            if (this.form_data.qty > data.qty_ordered) {
                this.e("Return Qty can't be greater than ordered Qty");
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/purchase_returns/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.s("Record added successfully");
                this.peding_returns = res.data;
            } else {
                this.form_error(res);
            }
        },
        modifyResData(data) {
            let modif = data.map(obj => {
                return {
                    id: obj.id,
                    details: "",
                    qty: 0,
                    qty_delivered: obj.qty_delivered,
                    qty_ordered: obj.qty,
                    code: obj.stock.code,
                    stock_id: obj.stock_id,
                    product_name: obj.stock.product_name,
                    unit_price: obj.unit_price,
                    sub_total: obj.sub_total,
                    deduct_stock:false,
                };
            });
            return modif;
        },

        async fetchOrders() {
            const res = await this.getApi("data/po/fetchByOrder", {
                params: {
                    order_no: this.form_data.order_no,
                    supplier_id: this.form_data.supplier_id
                }
            });

            if (res.status === 200) {
                this.order_data = res.data;
                this.modify_results = this.modifyResData(this.order_data);
            } else {
                this.swr("Server Error Reload");
            }
        },
        async fetchPendingReturns() {
            const res = await this.getApi(
                "data/purchase_returns/fetchPendingReturns",
                {
                    params: {
                        return_code: this.form_data.return_code,
                        supplier_id: this.form_data.supplier_id
                    }
                }
            );

            if (res.status === 200) {
                if (res.data.results == "data") {
                    this.peding_returns = res.data.data;
                    this.form_data.return_code = this.peding_returns[0].return_code;
                } else if (res.data.results == "return_code") {
                    this.form_data.return_code = res.data.data;
                } else {
                    this.fetchCode();
                }
            } else {
                this.swr("Server Error Reload");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchOrders(),
                this.fetchPendingReturns()
                //this.fetchCode()
            ]);
            this.hideLoader();
        },
        async fetchCode() {
            const res = await this.getApi("data/purchase_returns/fetchCode");
            if (res.status === 200) {
                this.form_data.return_code = res.data;
            } else {
                this.servo();
            }
        },
        modifyProductKey() {
            let modif = this.supplier_data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.company_name
                };
            });
            return modif;
        }
    }
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
