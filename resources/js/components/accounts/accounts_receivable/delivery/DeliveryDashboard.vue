<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
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
                <h4>Delivery Note</h4>
                <div class="card">
                    <div class="card-header-wrapper">
                        <div class="row d-flex">
                            <div>
                                <button
                                    @click="generateEntireDelivery()"
                                    class="btn btn-secondary"
                                    color="primary"
                                    dark
                                >
                                    Delivery Note Entire Invoice
                                </button>
                                <button
                                    @click="generateSelectedItems()"
                                    class="btn btn-secondary"
                                    color="primary"
                                    dark
                                >
                                    Delivery Note Selected
                                </button>
                            </div>

                            <div class="form-group">
                                <label for="email">Note Date *</label>
                                <date-picker
                                    v-model="form_data.delivery_date"
                                    valueType="format"
                                ></date-picker>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item</th>

                                    <th scope="col">Qty</th>

                                    <th scope="col">Price</th>
                                    <th scope="col">TOTAL</th>
                                    <th scope="col">Select Items</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in invoice_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock.code }}
                                    </td>
                                    <td>
                                        {{ data.stock.name }}
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            v-model="data.qty"
                                        />
                                    </td>

                                    <td>{{ cashFormatter(data.price) }}</td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                (data.row_total =
                                                    data.price * data.qty)
                                            )
                                        }}
                                    </td>
                                    <td>
                                        <input
                                            @change="
                                                checkDelivery($event, data, i)
                                            "
                                            type="checkbox"
                                            name=""
                                            class="form-control"
                                            id=""
                                        />
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
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";

export default {
    props: ["data"],
    data() {
        return {
            active: {
                dashboard: true,
            },
            delivery_no_data: [],
            invoice_data: [],
            search: "",
            form_data: {
                delivery_date: "",
                customer_id: "",
                invoice_id: "",
                invoice_no: "",
                data_array: [],
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {},
    mounted() {
        this.form_data.invoice_id = this.data.id;
        this.form_data.customer_id = this.data.customer_id;
        this.form_data.invoice_no = this.data.invoice_no;
        this.form_data.delivery_date = this.getCurrentDate();
        this.fetchInvoicesByInvoiceNo();
    },

    methods: {
        async generateEntireDelivery() {
            await this.insertDeliveryNote(this.invoice_data);
        },
        async generateSelectedItems() {
            if (this.delivery_no_data.length > 0) {
                await this.insertDeliveryNote(this.delivery_no_data);
            } else {
                this.e("Select delivery items");
            }
        },
        checkDelivery(event, data, i) {
            var isChecked = event.target.checked;
            if (isChecked) {
                this.delivery_no_data.push(data);
            } else {
                var index = -1;
                var val = data.id;
                var filteredObj = this.delivery_no_data.find(function (
                    item,
                    i
                ) {
                    if (item.id === val) {
                        index = i;
                        return i;
                    }
                });

                if (index > -1) {
                    this.delivery_no_data.splice(index, 1);
                }
            }
        },
        async insertDeliveryNote(data) {
            this.form_data.data_array = data;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/delivery/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Generated successfully");
                this.$emit('dashboard-active')
            } else {
                this.form_error(res);
            }
        },
        async fetchInvoicesByInvoiceNo() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/delivery/fetchByInvoice",
                {
                    invoice_no: this.data.invoice_no,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.invoice_data = res.data.results;
            } else {
                this.swr("error occured please contact admin");
            }
        },
    },
};
</script>
<style scoped></style>
