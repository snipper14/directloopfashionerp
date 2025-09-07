<template>
    <v-app class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Retails Sales Exchange</b></h3>
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
                                <label for="">Credit No*</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    v-model="form_data.credit_no"
                                />
                            </div>
                            <div class="col-md-3">
                                <div
                                    class="input-group date d-flex flex-row"
                                    id="datepicker"
                                >
                                    <label for="">Credit Date</label><br />
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="form_data.credit_date"
                                        placeholder="Select a date"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Details (Optional)</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    v-model="form_data.details"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <div class="row">
                                <search-product
                                    v-on:stockSearchResult="addCart"
                                />
                            </div>
                        </div>
                        <div class="row" v-if="data_array.length > 0">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table modern-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Sell Price</th>

                                            <th scope="col">Sub Total</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in data_array"
                                            :key="i"
                                        >
                                            <td>
                                                {{ value.stock.code }}
                                            </td>
                                            <td>
                                                {{ value.stock.name }}
                                            </td>

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
                                    <div class="form-group">
                                        <label for="">Image *</label>
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            class="form-control"
                                            v-on:change="selectFile"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <v-btn
                                        color="primary"
                                        @click="completeCreditNote()"
                                        >Complete Credit</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<v-row>
        <v-col cols="4">
          <PrintDirectCR
            ref="PrintDirectComponent"
            v-if="load_printer_component"
            :form_data="form_data"
            :data_array="data_array"
            :total_amount="total_amount"
           :total_tax="total_tax"
          
         
          />
        </v-col>
      </v-row>        </div>
    </v-app>
</template>

<script>
import SearchProduct from "../../preorders/SearchProduct.vue";
import PrintDirectCR from './PrintDirectCR.vue';
export default {
    props: ["customer_details"],
    components: { SearchProduct, PrintDirectCR },
    data() {
        return {
            load_printer_component:false,
            btn_loading: false,
            data_array: [],
               branch: this.$store.state.branch,
            form_data: {
                customer_name: this.customer_details.company_name,
                customer_phone: this.customer_details.company_phone,
                credit_no: null,
                customer_id: this.customer_details.id,
                row_vat: 0,
                row_total: 0,
                credit_date: this.getCurrentDate(),
                details: "",
                image: null,
            },
        };
    },
    mounted() {
        this.fetchProgress();
    },
    methods: {
        async completeCreditNote() {
            const data = new FormData();
            data.append("file", this.form_data.image);
            const json = JSON.stringify(this.form_data);
            data.append("data", json);
            this.btn_loading = true;
            const res = await this.callApi(
                "post",
                "data/directretail_creditnote/completeCreditNote",
                data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.successNotif(
                    " Records Details has been completed successfully!"
                );
                if (this.branch.print_type == "dialogue") {
                     this.data_array=res.data
                    this.load_printer_component=true
                   
                    setTimeout(() => {
                        //disable browser printing

                        this.$refs.PrintDirectComponent.printReceipt();
                        this.$emit("dismiss-modal");
                    }, 1000);
                } else {
                    this.$emit("dismiss-modal");
                }
              
            } else {
                this.form_error(res);
            }
        },
        selectFile(event) {
            this.form_data.image = event.target.files[0];
        },
        async addCart(value) {
            this.form_data.row_total = value.qty * value.selling_price;
            this.form_data.row_vat = this.calculateTax(
                value.tax_rate,
                this.form_data.row_total
            ).toFixed(2);
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/directretail_creditnote/create",
                {
                    ...this.form_data,
                    ...value,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Added");
                this.data_array = res.data;
            } else {
                this.form_error(res);
            }
        },
        async deleteRecord(value, i) {
            const confirm = await this.deleteDialogue();
            if (!confirm) {
                return;
            }
            this.showLoader();
            const res = this.callApi(
                "DELETE",
                "data/directretail_creditnote/destroy/" + value.id,
                ""
            );
            this.hideLoader();

            this.s("deleted");
            this.data_array.splice(i, 1);
        },
        async fetchProgress() {
            this.showLoader();
            const res = await this.getApi(
                "data/directretail_creditnote/fetchProgress",
                {
                    params: {
                        ...this.form_data,
                    },
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("loaded");
                this.data_array = res.data;
                if (this.data_array.length > 0) {
                    this.form_data.credit_no = this.data_array[0].credit_no;
                }
            } else {
                this.form_error(res);
            }
        },
    },
    computed: {
        total_amount() {
            if (this.data_array.length > 0) {
                return this.data_array
                    .reduce(function (sum, val) {
                        return sum + val.row_total;
                    }, 0)
                    .toFixed(2);
            }
        },

        total_tax() {
            if (this.data_array.length > 0) {
                return this.data_array.reduce(function (sum, val) {
                    return sum + val.row_vat;
                }, 0);
            }
        },
    },
};
</script>
