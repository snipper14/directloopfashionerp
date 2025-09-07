<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-3">
                        <v-btn
                            class="ma-2 v-btn-primary"
                            @click="$emit('dashboard-active')"
                            color="primary"
                            dark
                        >
                            <v-icon dark left> mdi-arrow-left </v-icon>
                            Back
                        </v-btn>
                    </div>
                    <div class="form-group col-md-3 nopadding">
                        <label>Show or Hide Disc.</label>
                        <Select v-model="discount_status">
                            <Option
                                v-bind:value="data.name"
                                v-for="(data, i) of show_disc"
                                :key="i"
                            >
                                {{ data.name }}
                            </Option>
                        </Select>
                    </div>
                    <div class="col-md-3">
                        <v-btn
                            v-if="isWritePermitted"
                            @click="generateInvoice()"
                            x-small
                            class="v-btn-primary"
                            color="primary"
                            dark
                        >
                            Download Invoice
                        </v-btn>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            Invoice Items/ Invoice No
                            {{ this.form_data.invoice_no }}
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <v-btn
                                    v-if="isDeletePermitted"
                                    @click="cancelInvoice()"
                                    x-small
                                    color="error"
                                    dark
                                >
                                    Cancel Invoice
                                </v-btn>
                            </div>
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
                                    <th scope="col">BatchNo</th>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item</th>
                                    <th scope="col">Qty</th>

                                    <th scope="col">Item Price</th>
                                    <th scope="col">Disc</th>
                                    <th scope="col">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in form_data.order_data"
                                    :key="i"
                                >
                                    <td>{{ data.batch_no }}</td>
                                    <td>
                                        {{ data.stock.code }}
                                    </td>
                                    <td>
                                        {{ data.stock.name }}
                                    </td>
                                    <td>{{ data.qty }}</td>

                                    <td>
                                        {{ cashFormatter(data.price) }}
                                    </td>
                                    <td>-{{ data.discount }}</td>
                                    <td>
                                        {{ cashFormatter(data.row_total) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5">
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
            show_disc: [{ name: "show" }, { name: "hide" }],
            discount_status: "hide",
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
            },
        };
    },
    mounted() {
        this.setFormParams();
        this.concurrentApiReq();
    },
    components: {},
    methods: {
        async cancelInvoice() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "post",
                    "data/invoices/cancelInvoice",
                    {
                        invoice_no: this.data.invoice_no,
                        customer_id: this.data.customer_id,
                    }
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.$emit("dashboard-active");
                } else {
                    this.swr(
                        "Server error occurred contact system administrator"
                    );
                }
            }
        },
        async generateInvoice() {
            this.showLoader();
            axios({
                url: "data/invoices/generateInvoice",
                params: {
                    invoice_no: this.data.invoice_no,
                    discount_status: this.discount_status,
                },
                method: "GET",
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", this.data.invoice_no + ".pdf");
                document.body.appendChild(link);
                link.click();
            });
            //  this.$refs.html2Pdf.generatePdf();
        },
        orderTotal() {
            if (this.form_data.order_data.length > 0) {
                var res = this.form_data.order_data
                    .map((ordeer_total) => ordeer_total.row_total)
                    .reduce((acc, ordeer_total) => ordeer_total + acc);
                this.total_invoice = res;
            }
        },
        setFormParams() {
            this.form_data.invoice_date = this.data.invoice_date;
            this.form_data.customer_id = this.data.customer_id;

            this.form_data.order_no = this.data.order_no;
            this.form_data.invoice_no = this.data.invoice_no;
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getInvoice()]).then(function (
                results
            ) {
                return results;
            });
            this.hideLoader();

            this.form_data.order_data = res[0];

            this.orderTotal();
        },
        async getInvoice() {
            const res = await this.getApi(
                "data/invoices/invoiceItems/" + this.data.invoice_no,
                ""
            );

            return res.data.results;
        },
    },
};
</script>
