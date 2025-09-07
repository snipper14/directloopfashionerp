<template>
    <div class="container">
        <v-app>  <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Credit Note for {{ data.supplier.company_name }}
                    </div>

                    <div class="card-body">
                        <div class="col-md-10 form-group">
                            <label for="">Credit Note No.</label>
                            <input
                                type="text "
                                class="form-control"
                                v-model="form_data.credit_no"
                            />
                        </div>
                        <div class="col-md-10 form-group">
                            <label for="">Credit Amount</label>
                            <input
                                type="text "
                                class="form-control"
                                v-model="form_data.credit_amount"
                            />
                        </div>
                        <div class="col-md-10 form-group">
                            <label for="">Invoice Amount</label>
                            <input
                                type="text "
                                class="form-control"
                                disabled
                                v-model="form_data.invoice_total"
                            />
                        </div>
                        <div class="col-md-10 form-group">
                            <label for="">Details</label>
                            <input
                                type="text "
                                class="form-control"
                                v-model="form_data.details"
                            />
                        </div>
                        <div class="col-md-10 form-group date-picker-wrapper">
                            <label for="">CreditNote Date</label>
                            <date-picker
                                v-model="data.credit_date"
                                valueType="format"
                            ></date-picker>
                        </div>
                        <div class="col-md-10">
                            <v-btn color="secondary" @click="saveCreditNotes()" small :loading="btnLoading">Save</v-btn>
                        </div>
                    </div>
                </div>
            </div>
        </div></v-app>
      
    </div>
</template>

<script>
export default {
    props: ["data"],
    data() {
        return {
            btnLoading:false,
            form_data: {
                credit_no: "",
                supplier_id:null,
                supplier_invoice_id: null,
                credit_amount: 0,
                invoice_total: 0,
                details: "",
                credit_date: null,
                invoice_no:null,
            },
        };
    },
    mounted() {
        this.form_data.credit_date = this.getCurrentDate();
        this.form_data.invoice_total = this.data.invoice_total;
        this.form_data.supplier_invoice_id = this.data.id;
        this.form_data.supplier_id = this.data.supplier_id;
        this.form_data.invoice_no=this.data.invoice_no
    },
    methods:{
         async saveCreditNotes() {
            this.btnLoading=true
            const res = await this.callApi(
                "post",
                "data/supplier_creditnote/create",
                {
                    ...this.form_data,
                   
                }
            );
            this.btnLoading=false
            this.hideLoader();
            if (res.status == 200) {
                this.s("Successfully saved");
                this.$emit("dashboard-active");
            } else {
               this.form_error(res)
            }
        },
    },
};
</script>
