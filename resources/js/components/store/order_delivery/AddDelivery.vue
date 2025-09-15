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
                <div class="card-header">
                    <h3><b>RECEIVE ORDERS</b></h3>
                </div>

                <div class="card-body">
                    <div class="row d-flex justify-content-between">
                        <h6>Order No: {{ order_details.order_no }}</h6>
                        <h6>
                            Vendor: {{ order_details.supplier.company_name }}
                        </h6>
                        <div class="form-group">
                            <div class="form-group">
                                <label>Delivery No. *</label>
                                <input
                                    v-model="form_data.delivery_no"
                                    class="form-control"
                                    type="text"
                                />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-group d-flex flex-column">
                                <label>Deadline Date *</label>
                                <date-picker
                                    v-model="form_data.received_date"
                                    valueType="format"
                                ></date-picker>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group d-flex flex-column mr-2">
                                <label for=""> Location*</label>

                                <Select v-model="form_data.department_id">
                                    <Option
                                        v-for="item in department_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.department }}</Option
                                    >
                                </Select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="form-group d-flex flex-column">
                                    <label>CU INVOICE NO</label>
                                    <input
                                        type="text"
                                        v-model="form_data.cu_invoice_no"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <v-btn
                                class="mt-3"
                                :loading="btn_loading"
                                x-small
                                color="secondary"
                                @click="receiveAllDeliveries()"
                                v-if="isWritePermitted && isAdmin"
                                title="write /Admin"
                            >
                                Receive All
                            </v-btn>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered mt-3"
                        >
                            <thead>
                                <tr>
                                    <th>Received</th>
                                    <th>Batchno</th>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item Name</th>
                                    <th scope="col">Unit Price</th>

                                    <th scope="col">Qty Ordered</th>
                                    <th scope="col">Qty Delivered</th>
                                    <th scope="col">Qty Received</th>
                                    <th>Discount Amount</th>

                                    <td>Receive</td>
                                    <th scope="col">Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in modify_results"
                                    :key="i"
                                     :class="{ 'bg-received': receivedMap[data.id] }"
                                >
                                    <td>
                                        <date-picker
                                            v-model="data.expiry_date"
                                            valueType="format"
                                        ></date-picker>
                                    </td>

                                    <td>
                                        <input
                                            class="form-control"
                                            type="text"
                                            v-model="data.batch_no"
                                        />
                                    </td>
                                    <td>
                                        {{ data.code }}
                                    </td>
                                    <td>
                                        {{ data.product_name }}
                                    </td>
                                    <td>
                                        <input
                                            style="width: 100px"
                                            class="form-control"
                                            type="number"
                                            v-model="data.unit_price"
                                        />
                                    </td>

                                    <td>
                                        <input
                                            class="form-control"
                                            style="width: 100px"
                                            type="text"
                                            v-model="data.qty"
                                        />
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.qty_delivered) }}
                                    </td>
                                    <td>
                                        <input
                                            style="width: 100px"
                                            class="form-control"
                                            type="number"
                                            v-model="data.qty_received"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            style="width: 100px"
                                            class="form-control"
                                            type="text"
                                            v-model="data.discount_amount"
                                        />
                                    </td>
                                    <td>
                                        <button
                                            v-if="isWritePermitted"
                                            title="write"
                                            class="btn btn-secondary btn-sm"
                                            @click="receiveOrder(data)"
                                        >
                                            Receive
                                        </button>
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.unit_price *
                                                    data.qty_received -
                                                    data.discount_amount
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered mt-3"
                        >
                            <thead>
                                <tr>
                                    <th>Batchno</th>
                                    <th>Expiry</th>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item Name</th>
                                    <th scope="col">Unit Price</th>

                                    <th scope="col">Qty Delivered</th>

                                    <th scope="col">Sub Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in delivery_data"
                                    :key="i"
                                >
                                    <td>{{ data.batch_no }}</td>
                                    <td>{{ data.expiry_date }}</td>
                                    <td>
                                        {{ data.stock ? data.stock.code : "" }}
                                    </td>
                                    <td>
                                        {{ data.stock ? data.stock.name : "" }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.unit_price) }}
                                    </td>

                                    <td>
                                        {{ data.qty_delivered }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.sub_total) }}
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-danger btn-sm"
                                            @click="delRecord(data, i)"
                                        >
                                            <v-icon dark left>
                                                mdi-trash </v-icon
                                            >Trash
                                        </button>
                                    </td>
                                </tr>
                                <tr class="fw-bold bg-light">
                                    <td colspan="6" class="text-right">
                                      <b>Total VAT:</b>  
                                    </td>
                                    <td>
                                      
                                        {{ cashFormatter(totalDeliveryVat) }}
                                    
                                    </td>
                                  
                                </tr>
                                  <tr class="fw-bold bg-light">
                                    <td colspan="6" class="text-right">
                                      <b>Total Amount:</b>  
                                    </td>
                                    <td>
                                     <b>{{
                                            cashFormatter(totalDeliverySubtotal)
                                        }}</b>   
                                    </td>
                                  
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <v-btn
                                            class="primary"
                                            small
                                            :loading="btn_loading"
                                            v-if="
                                                isDownloadPermitted &&
                                                delivery_data.length > 0
                                            "
                                            @click="completeDelivery()"
                                        >
                                            Complete Delivery
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12" id="printMe">
                <download-grn
                    v-if="grn_download_data && active.print_grn"
                    :grn_download_data="grn_download_data"
                    ref="grnDownloadComponent"
                />
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import DownloadGrn from "../delivery_notes/DownloadGrn.vue";
export default {
    components: { DownloadGrn },
    props: ["order_details"],
    data() {
        return {
            btn_loading: false,
            active: { print_grn: false },
            grn_download_data: null,
            value: null,
            window: 0,
            showContent: true,
            total_order: 0,
            modify_results: [],
            taxes: 0,
            order_data: [],
            delivery_data: [],
            select_data: {
                supplier_options: null,
            },
              receivedMap: {},
            department_data: [],
            form_data: {
                department_id: null,
                supplier_id: null,
                stock_id: null,
                order_no: "",
                purchase_order_id: null,
                qty_delivered: 0,
                qty_ordered: 0,
                sub_total: 0,
                unit_price: 0,
                received_date: "",
                delivery_no: "",
                cu_invoice_no: "",
                tax_rate: 0,
                tax_amount: 0,
                expiry_date: null,
                batch_no: "",
                discount_amount: 0,
            },
        };
    },
    watch: {
        delivery_data(newVal) {
            if (newVal.length > 0) {
                this.form_data.delivery_no = newVal[0].delivery_no;
            }
        }
    },
    computed: {
        totalDeliverySubtotal() {
            return this.delivery_data.reduce(
                (acc, item) => acc + (item.sub_total || 0),
                0
            );
        },
        totalDeliveryVat() {
            return this.delivery_data.reduce(
                (acc, item) => acc + (item.tax_amount || 0),
                0
            );
        },
    },
    mounted() {
        this.form_data.order_no = this.order_details.order_no;
        this.form_data.supplier_id = this.order_details.supplier_id;

        this.form_data.received_date = this.getCurrentDate();
        this.concurrentApiReq();
    },

    methods: {
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },
        async delRecord(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/po_receivable/destroy", {
                    params: {
                        qty_delivered: data.qty_delivered,
                        supplier_id: data.supplier_id,
                        id: data.id,
                    },
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.delivery_data.splice(i, 1);
                    this.fetchOrders();
                } else {
                    this.swr("Server error try later!!");
                }
            }
        },
        async completeDelivery() {
            this.btn_loading = true;
            const dataObj = {
                order_no: this.form_data.order_no,
                supplier_id: this.form_data.supplier_id,
                delivery_no: this.form_data.delivery_no,
                department_id: this.form_data.department_id,
                cu_invoice_no: this.form_data.cu_invoice_no,
            };
            const res = await this.callApi(
                "POST",
                "data/po_receivable/completeDelivery",
                dataObj
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.s("successfully completed");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        async receiveAllDeliveries() {
            this.btn_loading = true;

            const res = await this.callApi(
                "POST",
                "data/po_receivable/receiveAllDeliveries",
                this.form_data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.s("successfully completed");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        async downloadDN() {
            this.showLoader();
            const dataObj = {
                order_no: this.form_data.order_no,
                supplier_id: this.form_data.supplier_id,
                delivery_no: this.form_data.delivery_no,
                department_id: this.form_data.department_id,
                cu_invoice_no: this.form_data.cu_invoice_no,
            };

            axios({
                url: "data/po_receivable/downloadDN",
                method: "POST",
                data: dataObj,
                responseType: "blob", // important
            })
                .then((response) => {
                    this.hideLoader();
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute(
                        "download",
                        this.getTimeStamp() + "_DN" + ".pdf"
                    );
                    document.body.appendChild(link);
                    link.click();
                    this.$emit("dashboard-active");
                })
                .catch((error) => {
                    this.hideLoader();
                    this.errorGetter(dataObj);
                });
        },
        async errorGetter(dataObj) {
            axios({
                url: "data/po_receivable/downloadDN",
                method: "POST",
                data: dataObj,
            })
                .then((response) => {})
                .catch((error) => {
                    this.form_error(error.response);
                });
        },
       
        async receiveOrder(data) {
            this.form_data.expiry_date = data.expiry_date;
            this.form_data.discount_amount = data.discount_amount;
            this.form_data.batch_no = data.batch_no;
            this.form_data.sub_total =
                data.unit_price * data.qty_received - data.discount_amount;
            this.form_data.purchase_order_id = data.id;
            this.form_data.unit_price = data.unit_price;
            this.form_data.stock_id = data.stock_id;
            this.form_data.qty_delivered = data.qty_received;
            this.form_data.qty_ordered = data.qty;
            this.form_data.received_date = this.formatDate(
                this.form_data.received_date
            );
            this.form_data.tax_rate = data.tax_rate;
            this.form_data.tax_amount = this.calculateTax(
                this.form_data.tax_rate,
                this.form_data.sub_total
            );

            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/po_receivable/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.s("Record added successfully");
                this.delivery_data = res.data;
                  this.$set(this.receivedMap, data.id, true);
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
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    qty_received: obj.qty - obj.qty_delivered,
                    qty: obj.qty,
                    qty_delivered: obj.qty_delivered,
                    code: obj.stock.code,
                    stock_id: obj.stock_id,
                    product_name: obj.stock.product_name,
                    unit_price: obj.unit_price,
                    sub_total: obj.sub_total,
                    taxes: obj.taxes,
                    tax_rate: obj.tax_rate,
                    batch_no: "",
                    expiry_date: this.getCurrentDate(),
                    discount_amount: 0,
                };
            });
            return modif;
        },

        async fetchOrders() {
            const res = await this.getApi("data/po/fetchPendingOrder", {
                params: {
                    order_no: this.form_data.order_no,
                    supplier_id: this.form_data.supplier_id,
                },
            });

            if (res.status === 200) {
                this.order_data = res.data;
                this.modify_results = this.modifyResData(this.order_data);
            } else {
                this.swr("Server Error Reload");
            }
        },
        async fetchPendingDelivery() {
            const res = await this.getApi(
                "data/po_receivable/fetchPendingDelivery",
                {
                    params: {
                        order_no: this.form_data.order_no,
                        delivery_no: this.form_data.delivery_no,
                        supplier_id: this.form_data.supplier_id,
                    },
                }
            );

            if (res.status === 200) {
                this.delivery_data = res.data;
            } else {
                this.swr("Server Error Reload");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchOrders(),
                this.fetchPendingDelivery(),
                this.fetchDeliveryNo(),
                this.fetchDepartment(),
            ]);
            this.hideLoader();
        },
        async fetchDeliveryNo() {
            const res = await this.getApi("data/po_receivable/fetchDeliveryNo");
            if (res.status === 200) {
                this.form_data.delivery_no = res.data;
              
            } else {
                this.swr("could not fetch delivery no");
            }
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
#printMe {
    visibility: hidden;
}
.bg-received {
  background-color: #e6ffed !important; /* soft green */
}
</style>
