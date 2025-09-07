<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <b>Return: {{ details_data.stock.product_name }}</b>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="">Return Qty</label>
                                <input
                                    type="number"
                                    v-model="formData.qty"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="">Item Conditions</label>
                                <textarea
                                    class="form-control"
                                    v-model="formData.returns_conditions"
                                ></textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="">Purpose for Return</label>
                                <textarea
                                    class="form-control"
                                    v-model="formData.return_reasons"
                                ></textarea>
                            </div>
                            <div class="col-md-4 d-flex flex-column">
                                <label> Return Date*</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="formData.return_date"
                                ></date-picker>
                            </div>
                        </div>
                        <button
                            class="btn btn-secondary btn-small"
                            @click="addReturn"
                        >
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["details_data"],
    data() {
        return {
            formData: {
                stock_id: null,
                department_id: null,
                purchase_order_receivable_id: null,
                supplier_id: null,
                unit_price: 0,
                qty: this.details_data.delivered_qty,
                tax_rate: 0,
                tax_amount: 0,
                discount_amount: this.details_data.discount_amount,
                sub_total: 0,
                return_date: this.getCurrentDate(),
                returns_conditions: "",
                return_reasons: "",
                return_code: "",
                batch_no:"",
            
                
            },
        };
    },
    mounted() {
        this.formData = this.details_data;
          this.formData.qty= this.details_data.qty_delivered,
        this.formData.return_date = this.getCurrentDate();
        this.formData.returns_conditions=""
         this.formData.return_reasons=""
       
       
    },
    methods: {
        addReturn() {
           
            this.formData.sub_total =
                parseFloat(this.formData.unit_price) *
                parseFloat(this.formData.qty) ;
            this.formData.tax_amount = this.calculateTax(
                this.formData.tax_rate,
                this.formData.sub_total
            );
            this.$emit("addCart", this.formData);
        },
    },
};
</script>
