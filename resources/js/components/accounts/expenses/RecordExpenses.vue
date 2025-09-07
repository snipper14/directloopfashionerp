<template>
    <v-app class="">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <v-btn color="primary" small @click="$emit('dashboard-active')">Back</v-btn>
                <div class="card">
                    <div class="card-header"><h3>Record Expenses </h3></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    <label for="">Search Category</label>
                                    <treeselect
                                        :load-options="fetchCategory"
                                        :options="expense_category_select"
                                        :auto-load-root-options="false"
                                        v-model="form_data.expense_type_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Category"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <div class="form-group d-flex flex-column">
                                    <label> Date Recorded *</label
                                    ><date-picker
                                        style="width: 150px"
                                        v-model="form_data.date_recorded"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="form-group col-md-4 ">
                                <label> Amount *</label>
                                <input
                                    type="number"
                                    v-model="form_data.amount"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group col-md-4 ">
                                <label for="">Expense To*</label>
                                <treeselect
                                    :load-options="fetchSupplier"
                                    :options="supplier_select_data"
                                    :auto-load-root-options="false"
                                    v-model="form_data.supplier_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select  Supplier "
                                />
                            </div>
                            <div class="col-md-4 ">
                                <label> Ref. No </label>
                                <input
                                    placeholder="Reference."
                                    type="text"
                                    v-model="form_data.ref_no"
                                    class="form-control"
                                />
                            </div>
                                <div class="col-md-4 ">
                                <label>CU Invoice. No </label>
                                <input
                                    placeholder="Cu Invoice No."
                                    type="text"
                                    v-model="form_data.cu_invoice_no"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-4 nopadding">
                                <label> Details *</label>
                                <input
                                    placeholder="Details."
                                    type="text"
                                    v-model="form_data.details"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-2">
                                <v-btn
                                v-if="isWritePermitted"
                                title="WRITE"
                                    dark
                                    :loading="btn_loading"
                                    @click="saveExpenses()"
                                    color="secondary"
                                    >+ Save Records</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>

export default {
    data() {
        return {
           btn_loading:false,
            form_data: {
                expense_type_id: null,
                date_recorded: this.getCurrentDate(),
                amount: 0,
                details: "",
                supplier_id: null,
                ref_no: "",
                cu_invoice_no:""
            },
        };
    },
    components:{
    
    },
    mounted() {
         console.log("Component mounted.");
    },
    methods:{
         async saveExpenses() {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/expense/create",
                this.form_data
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.successNotif("Added");

                this.form_data.expense_type_id = null;
                this.form_data.supplier_id = null;
                this.form_data.amount = 0;
                this.form_data.ref_no = "";
                this.form_data.details = "";
                this.form_data.cu_invoice_no=""
                
            } else {
                this.form_error(res);
            }
        },
          
       
    }
};
</script>
