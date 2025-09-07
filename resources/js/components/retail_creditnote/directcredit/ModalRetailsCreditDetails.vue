<template>
    <v-app>
        <div class="row justify-content-center">
            <div class="col-md-12" v-if="active.dashboard">
                <div class="card">
                    <div class="card-header">
                        <h3>
                            Creditnote Details for
                            {{ details_data.customer.company_name }}
                        </h3>
                        <v-btn small color="primary" @click="reprintReceipt">Reprint</v-btn>
                    </div>

                    <div class="card-body">
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(value, i) in display_array_data"
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
                                            {{ cashFormatter(value.row_total) }}
                                        </td>
                                        <td>{{ value.entry_type }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <span>
                                    <b>
                                        Total Credit:
                                        {{ cashFormatter(total_amount) }}
                                         <br>
                                          Total Replacement {{cashFormatter(used_total)}}</b
                                    ><br>
                                     Balance {{cashFormatter(pending_balance)}} </b>
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
                                    v-if="is_balance_available"
                                    small
                                    color="primary"
                                    @click="addReplacement()"
                                    >Add Replacement</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <ReplacementRetailsCr
                    v-if="active.add_replacement"
                    :details_data="details_data"
                    :total_amount="total_amount"
                    :total_tax="total_tax"
                    :used_total="used_total"
                    :replacement_vat="replacement_vat"
                    v-on:dashboard-active="setActiveRefresh"
                />
            </div>
              <v-row>
                <v-col cols="4">
                    <ReprintDirectGenericReceipt
                        ref="ReceiptPrintComponent"
                        v-if="load_printer_component"
                        :form_data="form_data"
                        :replacement_array="replacement_array"
                        :credit_total="total_amount"
                        :replacement_total="used_total"
                        :pending_balance="pending_balance"
                        :array_data="array_data"
                       
                    />
                </v-col>
            </v-row>
        </div>
    </v-app>
</template>

<script>
import ReplacementRetailsCr from "./ReplacementRetailsCr.vue";
import ReprintDirectGenericReceipt from './ReprintDirectGenericReceipt.vue';
export default {
    components: { ReplacementRetailsCr, ReprintDirectGenericReceipt },
    props: ["details_data"],
    data() {
        return {
            active: {
                dashboard: true,
                add_replacement: false,
            },
            load_printer_component:false,
            form_data:null,
            display_array_data: [],
            array_data: [],
            replacement_array: [],
            isSending: false,
            isLoading: true,
        };
    },
    mounted() {
        console.log("Component mounted.");

        this.concurrentApiReq();
    },
    methods: {
        async reprintReceipt(){
            this.load_printer_component=true
             
                        setTimeout(() => {
                            this.$refs.ReceiptPrintComponent.printReceipt();
                            this.load_printer_component=false
                        }, 1000);
                  
// this.showLoader()
// const res=await this.getApi()
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([
                this.fetchDetails(),
                this.fetchCreditByCreditNo(),
                this.fetchReplacement(),
            ])
            this.isLoading
                ? this.hideLoader()
                : "";
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        addReplacement() {
            this.setActiveComponent("add_replacement");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        async completeDelivery() {
            this.isSending = true;
            const res = await this.callApi(
                "POST",
                "data/preorders/completeDelivery",
                {
                    order_no: this.details_data.order_no,
                }
            );
            this.isSending = false;
            if (res.status === 200) {
                this.successNotif("completed ");
                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },

        async fetchDetails() {
            const res = await this.getApi(
                "data/directretail_creditnote/getByCreditNo",
                {
                    params: {
                        ref_no: this.details_data.ref_no,
                        credit_no: this.details_data.credit_no,
                        customer_id:this.details_data.customer_id
                    },
                }
            );

            if (res.status === 200) {
                this.display_array_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchReplacement() {
            const res = await this.getApi(
                "data/directretail_creditnote/fetchReplacement",
                {
                    params: {
                        ref_no: this.details_data.ref_no,
                        credit_no: this.details_data.credit_no,
                        customer_id:this.details_data.customer_id
                    },
                }
            );

            if (res.status === 200) {
                this.replacement_array = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchCreditByCreditNo() {
            const res = await this.getApi(
                "data/directretail_creditnote/fetchCreditByCreditNo",
                {
                    params: {
                        ref_no: this.details_data.ref_no,
                        credit_no: this.details_data.credit_no,
                        customer_id:this.details_data.customer_id
                    },
                }
            );

            if (res.status === 200) {
                this.array_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
    computed: {
        pending_balance(){
          return  (this.total_amount-this.used_total)
        },
        is_balance_available() {
            var res=this.total_amount-this.used_total
            if(res>0){
                return true;
            }else{
                return false
            }
        },
        total_amount() {
            if (this.array_data.length > 0) {
                return this.array_data
                    .reduce(function (sum, val) {
                        return sum + val.row_total;
                    }, 0)
                    .toFixed(2);
            } else {
                return 0;
            }
        },
        used_total() {
            if (this.replacement_array.length > 0) {
                return this.replacement_array
                    .reduce(function (sum, val) {
                        return sum + val.row_total;
                    }, 0)
                    .toFixed(2);
            } else {
                return 0;
            }
        },
         replacement_vat() {
            if (this.replacement_array.length > 0) {
                return this.replacement_array
                    .reduce(function (sum, val) {
                        return sum + val.row_vat;
                    }, 0)
                    .toFixed(2);
            } else {
                return 0;
            }
        },
        total_tax() {
            if (this.array_data.length > 0) {
                return this.array_data.reduce(function (sum, val) {
                    return sum + val.row_vat;
                }, 0);
            } else {
                return 0;
            }
        },
    },
};
</script>
