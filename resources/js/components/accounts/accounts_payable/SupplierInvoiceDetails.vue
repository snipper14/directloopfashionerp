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
                    <v-icon dark left>
                        mdi-arrow-left
                    </v-icon>
                    Back
                </v-btn>
                <div class="card">
                    <div class="card-header">
                        <b>Supplier: </b>{{ data.supplier.company_name }}
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group date-picker-wrapper">
                                    <label for="">Invoice Date</label>
                                    <date-picker
                                        v-model="data.invoice_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Invoice Description</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="data.description"
                                        disabled
                                        placeholder="Invoice Description"
                                    />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Invoice No.</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        disabled
                                        autocomplete="new-user-street-address"
                                        v-model="data.invoice_no"
                                        placeholder="Invoice No"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Invoice Amount *.</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        autocomplete="new-user-street-address"
                                        v-model="data.invoice_total"
                                        placeholder="Invoice Amount"
                                    />
                                </div>
                            </div>
                            <div class="col-4">
                                <button
                                  v-if="isDeletePermitted"
                                    class="btn btn-primary mt-3"
                                    @click="cancelInvoice()"
                                >
                                    Cancel Invoice
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <table
                                v-if="particulars_data.length > 0"
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">item</th>
                                        <th scope="col">Qty</th>
                                        <th scope="col">Price</th>

                                        <th scope="col">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in particulars_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.stock.code }}
                                        </td>
                                        <td>
                                            {{ data.stock.name }}
                                        </td>
                                        <td>{{ data.qty }}</td>
                                        <td>{{ cashFormatter(data.price) }}</td>
                                        <td>
                                            {{ cashFormatter(data.line_total) }}
                                        </td>
                                        <td>
                                            <button
                                              v-if="isDeletePermitted"
                                                @click="
                                                    deleteParticular(data.id, i)
                                                "
                                                class="btn btn-danger custom-button"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

export default {
    props: ["data"],
    data() {
        return {
            particulars_data: []
        };
    },
    components: {
        Treeselect
    },

    mounted() {
        this.getParticulars();
    },

    methods: {
        async deleteParticular(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/supplier_invoice/destroy_particular/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.w("deleted ");
                    this.particulars_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async cancelInvoice() {
         
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "post",
                    "data/supplier_invoice/destroy",
                    {
                        invoice_no: this.data.invoice_no,
                        supplier_invoice_id: this.data.id,
                        supplier_id:this.data.supplier_id,
                    }
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.w("successfully deleted");
                    this.$emit("dashboard-active");
                } else {
                    this.swr("Server error");
                }
            }
        },
        async getParticulars() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/supplier_invoice/invoiceParticulars",
                {
                    invoice_no: this.data.invoice_no,
                    supplier_invoice_id: this.data.id
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.particulars_data = res.data.results;
            } else {
                this.swr("Server error");
            }
        }
    }
};
</script>
