<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-10">
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
                    <div class="card-header">Add Credit Note</div>

                    <div class="card-body">
                        <div class="d-flex justify-content-center">
                             <div class="form-group date-picker">
                                    <label for="">Credit Date</label>
                                    <date-picker
                                        v-model="form_data.credit_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            <form class="form-inline">
                                <input
                                    class="form-control mr-sm-2"
                                    type="search "
                                    v-model="invoice_no"
                                    placeholder="Search Invoice No."
                                />
                            </form>
                            <button
                                v-if="isWritePermitted"
                                @click="concurrentApiReq()"
                                class="btn btn-primary button-custom"
                            >
                                Get Invoice
                            </button>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Item</th>

                                    <th scope="col">Item Price</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">total</th>

                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in invoice_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock.name }}
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            class="form-control"
                                            v-model="data.price"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            class="form-control"
                                            v-model="data.qty"
                                        />
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.row_total) }}
                                    </td>
                                    <td>
                                        <input
                                            type="checkbox"
                                            v-model="form_data.reduce_stock"
                                        />
                                    </td>

                                    <td>
                                        <router-link
                                            v-if="isWritePermitted"
                                            to="#"
                                            @click.native="addCreditNotes(data)"
                                        >
                                            Add To CreditNote
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="row" v-if="credit_data.length > 0">
                            <h6>Credit Notes Items</h6>
                            Credit Note No: {{ credit_data[0].credit_no }}
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Item</th>

                                        <th scope="col">Item Price</th>
                                        <th scope="col">Qty</th>
                                        <th scope="col">total</th>

                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in credit_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.invoice.stock.name }}
                                        </td>

                                        <td>
                                            {{ cashFormatter(data.price) }}
                                        </td>
                                        <td>
                                            {{ data.qty }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.line_total) }}
                                        </td>

                                        <td>
                                            <router-link
                                                v-if="data.approved == 0"
                                                to="#"
                                                @click.native="
                                                    removeCreditNoteItem(
                                                        data.id,
                                                        i
                                                    )
                                                "
                                            >
                                                Remove CreditNote
                                            </router-link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            invoice_data: [],
            invoice_no: null,
            credit_data: [],
            form_data: {
                reduce_stock: true,
                credit_date: null,
                customer_id: null,
            },
        };
    },
    mounted() {
        this.form_data.credit_date = this.getCurrentDate();
    },
    methods: {
        async removeCreditNoteItem(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/credit/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.credit_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.fetchInvoice(),
                this.fetchCredits(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();

            this.invoice_data = res[0];
            this.credit_data = res[1];
           
            this.form_data.customer_id = this.invoice_data[0].customer_id;
            if (this.invoice_data.length < 1) {
                this.w("Sorry Invoice Record No found");
            }
        },
        async addCreditNotes(data) {
            console.log(JSON.stringify(data));
            if (data.qty < 1) {
                this.e("Quantity should be greater than 0");
                return;
            }
            this.showLoader();
            const line_total = (data.price*data.qty);
           
          const vat=(line_total*data.tax_amount)/data.row_total
            const res = await this.callApi("post", "data/credit/create", {
                invoice_no: data.invoice_no,
                invoice_id: data.id,
                qty: data.qty,
                price: data.price,
                row_vat:vat,
                line_total: line_total,
                reduce_stock: this.form_data.reduce_stock,
                credit_date: this.form_data.credit_date,
                customer_id: this.form_data.customer_id,
                department_id: data.department_id,
            });
            this.hideLoader();
            if (res.status == 200) {
                this.s("Added successfully");
                this.credit_data = await this.fetchCredits();
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
        async fetchCredits() {
            const res = await this.getApi(
                "data/credit/creditItems/" + this.invoice_no,
                ""
            );

            return res.data.results;
        },
        async fetchInvoice() {
            if (this.invoice_no) {
                const res = await this.getApi(
                    "data/credit/getByInvoiceNo/" + this.invoice_no
                );

                if (res.status == 200) {
                    return res.data.results;
                } else {
                    this.swr("Server error occured");
                }
            }
        },
    },
};
</script>
