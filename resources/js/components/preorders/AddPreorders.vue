<template>
<v-app>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <b
                                >New Pre-Order for
                                {{ customer_details.company_name }}</b
                            >
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="">Customer Name*</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    v-model="form_data.customer_name"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Customer Phone*</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    v-model="form_data.customer_phone"
                                />
                            </div>
                            <div class="col-md-3">
                                <div class="input-group date" id="datepicker">
                                    <label for="">Order Date</label><br />
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="form_data.order_date"
                                        placeholder="Select a date"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <search-product v-on:stockSearchResult="addCart" />
                        </div>
                        <div class="row" v-if="order_data.length > 0">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table modern-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>
                                            <th>Description</th>
                                            <th scope="col">Qty</th>
                                            <th scope="col">Sell Price</th>

                                            <th scope="col">Sub Total</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in order_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ value.stock.code }}
                                            </td>
                                            <td>
                                                {{ value.stock.name }}
                                            </td>
                                            <td>{{ value.description }}</td>
                                            <td>{{ value.qty }}</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.selling_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.row_total
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <v-btn
                                                    x-small
                                                    color="danger"
                                                    title="DELETE"
                                                    v-if="isDeletePermitted"
                                                    @click="
                                                        deleteRecord(value, i)
                                                    "
                                                    >Delete</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <span>
                                        <b>
                                            TOTAL:
                                            {{ cashFormatter(total_amount) }}</b
                                        >
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <span>
                                        <b>
                                            TAX:
                                            {{ cashFormatter(total_tax) }}</b
                                        >
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <v-btn
                                        color="primary"
                                        @click="completeSale()"
                                        >Complete Sale</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="cashout_modal" width="800px">
            <cashout-preorders
                :order_data="order_data"
                v-if="cashout_modal"
                :customer_details="customer_details"
                :total_amount="total_amount"
                :total_tax="total_tax"
                :form_data1="form_data"
                v-on:dismiss-modal="dismissModal"
        /></Modal>
    </div>
    
</v-app>
    
</template>

<script>
import CashoutPreorders from "./CashoutPreorders.vue";
import SearchProduct from "./SearchProduct.vue";
export default {
    components: { SearchProduct, CashoutPreorders },
    props: ["customer_details"],
    data() {
        return {
            cashout_modal: false,
            order_data: [],
            form_data: {
                customer_name: this.customer_details.company_name,
                customer_phone: this.customer_details.company_phone,
                order_no: null,
                customer_id: this.customer_details.id,
                row_vat: 0,
                row_total: 0,
                order_date: this.getCurrentDate(),
            },
        };
    },
    mounted() {
        this.fetchProgress();
    },
    methods: {
        dismissModal(){
            this.cashout_modal=false
            this.$emit('dismiss-modal')
        },
        completeSale(){
            this.cashout_modal=true
        },
        async deleteRecord(value, i) {
            const confirm = await this.deleteDialogue();
            if (!confirm) {
                return;
            }
            this.showLoader();
            const res = this.callApi(
                "DELETE",
                "data/preorders/destroy/" + value.id,
                ""
            );
            this.hideLoader();

            this.s("deleted");
            this.order_data.splice(i, 1);
        },
        async fetchProgress() {
            this.showLoader();
            const res = await this.getApi("data/preorders/fetchProgress", {
                params: {
                    ...this.form_data,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.s("loaded");
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async addCart(value) {
            
            this.form_data.row_total = value.qty * value.selling_price;
            this.form_data.row_vat = this.calculateTax(
                value.tax_rate,
                this.form_data.row_total
            ).toFixed(2);
            console.log(JSON.stringify(this.form_data));
            
            this.showLoader();
            const res = await this.callApi("POST", "data/preorders/create", {
                ...this.form_data,
                ...value,
            });
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Added");
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
    computed: {
        total_amount() {
            if (this.order_data.length > 0) {
                return this.order_data
                    .reduce(function (sum, val) {
                        return sum + val.row_total;
                    }, 0)
                    .toFixed(2);
            }
        },
        total_tax() {
            if (this.order_data.length > 0) {
                return this.order_data.reduce(function (sum, val) {
                    return sum + val.row_vat;
                }, 0);
            }
        },
    },
};
</script>
