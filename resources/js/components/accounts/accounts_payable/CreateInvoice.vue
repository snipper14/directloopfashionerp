<template>
    <div class="container">
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
                    <div class="card-header">
                        <b>Supplier: </b>{{ data.company_name }}
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group date-picker-wrapper">
                                    <label for="">Invoice Date</label>
                                    <date-picker
                                        v-model="form_data.invoice_date"
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
                                        v-model="form_data.description"
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
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.invoice_no"
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
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.invoice_total"
                                        placeholder="Invoice Amount"
                                    />
                                </div>
                            </div>
                            <div class="col-4">
                                <button
                                    class="btn btn-primary mt-3"
                                    @click="saveInvoice()"
                                >
                                    Save Info
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <center><h4>Add Invoice Particulars</h4></center>
                            <br />
                        </div>
                        <div class="row">
                            <product-search-cost-price
                                @stockSearchResult="saveParticulars"
                            />
                        </div>

                        <div class="row">
                            <table
                                v-if="particulars_data.length > 0"
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item</th>
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
                                                @click="
                                                    deleteParticulars(
                                                        data.id,
                                                        i
                                                    )
                                                "
                                                class="btn btn-danger btn-sm"
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
import _ from "lodash";
import { debounce } from "debounce";
import ProductSearch from "../../utilities/ProductSearch.vue";
import ProductSearchCostPrice from '../../utilities/ProductSearchCostPrice.vue';
const simulateAsyncOperation = (fn) => {
    setTimeout(fn, 2000);
};

export default {
    props: ["data"],
    data() {
        return {
            products_data: [],
            particulars_data: [],
            keyword: "",
            item_name: "",
            unit_options: [],
            form_data: {
                invoice_date: "",
                description: "",
                invoice_no: "",
                invoice_total: "",
                supplier_id: null,
            },
            particulars_form: {
                stock_id: null,

                supplier_invoice_id: "",
                invoice_no: "",
                line_total: 0,
                price: 0,
                qty: 1,
            },
            isOpen: false,
            results: [],
            search_item: "",
            isLoading: false,
            arrowCounter: -1,
            select_item: null,
        };
    },
    components: {
        Treeselect,
        ProductSearch,
        ProductSearchCostPrice,
    },

    mounted() {
        this.form_data.supplier_id = this.data.id;

        this.form_data.invoice_date = this.getCurrentDate();
    },
    watch: {
        particulars_form: {
            handler(val) {
                var qty = parseFloat(val.qty);
                var item_p = parseFloat(val.price);

                this.particulars_form.line_total = item_p * qty;
            },
            deep: true,
        },
    },
    methods: {
        async deleteParticulars(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "delete",
                    "data/supplier_invoice/destroy_particular/" + id,
                    ""
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.w("successfully deleted");
                    this.particulars_data.splice(i, 1);
                } else {
                    this.swr("Server error");
                }
            }
        },

        async saveParticulars(value) {
            this.particulars_form.stock_id = value.id;
            this.particulars_form.line_total = value.qty * value.purchase_price;
            this.particulars_form.price = value.purchase_price;
            this.particulars_form.qty = value.qty;

            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/supplier_invoice/save_particulars",
                this.particulars_form
            );
            this.hideLoader();
            if (res.status == 200) {
                this.particulars_form.stock_id = null;

                this.particulars_form.line_total = 0;
                this.particulars_form.price = 0;
                this.particulars_form.qty = 0;
                this.item_name = "";
                this.particulars_data = res.data.results;
                this.s("Successfully saved");
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
        async saveInvoice() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/supplier_invoice/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                const data = res.data.results;
                this.particulars_form.supplier_invoice_id = data.id;

                this.particulars_form.invoice_no = data.invoice_no;

                this.s("Successfully saved");
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
    },
};
</script>
<style scoped>
.dropdown-search-ul {
    padding: 1rem;
    cursor: pointer;
    border-radius: 5px;
    background-color: #ddd;
    list-style: none;
    position: absolute;
    z-index: 1000;
}
.dropdown-search-li {
    padding: 1rem;
    cursor: pointer;
    border-bottom: 1px solid #fff;
}

.autocomplete {
    position: relative;
}

.autocomplete-results {
    padding: 0;
    margin: 0;
    border: 1px solid #eeeeee;
    height: 120px;
    overflow: auto;
}

.autocomplete-result {
    list-style: none;
    text-align: left;
    padding: 4px 2px;
    cursor: pointer;
}

.autocomplete-result.is-active,
.autocomplete-result:hover {
    background-color: #4aae9b;
    color: white;
}
</style>
