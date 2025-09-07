<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-8">
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
                    <div class="card-header d-flex justify-content-between ">
                        <div>Order Items/ Order No {{this.form_data.order_no }}</div>
                        <div>
                            <v-btn
                                class="ma-2 v-btn-primary"
                                @click="generateInvoice()"
                                color="primary"
                                dark
                            >
                                Generate Invoice
                            </v-btn>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group date-picker">
                                    <label for="">Invoice Date</label>
                                    <date-picker
                                        v-model="form_data.invoice_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                     <th scope="col">Items</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Item Price</th>
                                    <th scope="col">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in form_data.order_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock.code }}
                                    </td>
                                      <td>
                                        {{ data.stock.name }}
                                    </td>
                                   
                                    <td>{{ data.qty }}</td>
                                    <td>
                                        {{ cashFormatter(data.item_price) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.row_total) }}
                                    </td>

                                    
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <strong>Total Invoice</strong>
                                    </td>
                                    <td>
                                        <strong>{{
                                            cashFormatter(total_invoice)
                                        }}</strong>
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
    props: ["data"],
    data() {
        return {
            total_invoice: 0,
            form_data: {
                stock_id: "",
                customer_id: "",
               
                invoice_date: "",
                invoice_no: "",
                order_no: "",
                qty: 0,
                price: 0,
                row_total: 0,
                order_data: [],
                department_id:null,
                sum_invoice:0
            }
        };
    },
    mounted() {
        this.setFormParams();
        this.concurrentApiReq();
    },
    methods: {
        async generateInvoice(){
        this.showLoader();
            const res = await this.callApi("post", "data/invoices/create", this.form_data);
            this.hideLoader();
            if (res.status === 200) {
                  this.$emit("dashboard-active");
            }else{
                this.swr("Server error occurred contact system administrator")
            }
            },
        orderTotal() {
            if (this.form_data.order_data.length > 0) {
                var res = this.form_data.order_data
                    .map(ordeer_total => ordeer_total.row_total)
                    .reduce((acc, ordeer_total) => ordeer_total + acc);
                this.total_invoice = res;
                this.form_data.sum_invoice=res
            }
        },
        setFormParams() {
        this.form_data.department_id=this.data.department_id
            this.form_data.invoice_date = this.getCurrentDate();
            this.form_data.customer_id = this.data.customer_id;
           
            this.form_data.order_no = this.data.order_no;
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getOrderDetails()]).then(
                function(results) {
                    return results;
                }
            );
            this.hideLoader();

            this.form_data.order_data = res[0];
          
            this.orderTotal();
        },
        async getOrderDetails() {
            const res = await this.getApi(
                "data/invoices/orders/" + this.data.order_no,
                ""
            );

            return res.data.results;
        }
    }
};
</script>
