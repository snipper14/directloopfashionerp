<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Add Balances for {{data.company_name}}</div>

                    <div class="card-body">
                          
                     

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group ">
                                    <label for="">Entry Date</label>
                                    <date-picker
                                        v-model="form_data.entry_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label for="">Dr Amount</label>
                                    <input
                                        v-model="form_data.dr_balance"
                                        type="number"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder=""
                                    />
                                </div>
                                  <div class="form-group">
                                    <label for="">Cr Amount</label>
                                    <input
                                        v-model="form_data.cr_balance"
                                        type="number"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder=""
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Description</label>
                                    <input
                                        v-model="form_data.description"
                                        type="text"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder=""
                                    />
                                </div>
                                <div class="flex">
                                    <button
                                        v-if="isWritePermitted"
                                        class="btn btn-secondary  "
                                        @click="addBalance()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props:['data'],
    data() {
        return {
              form_data: {
                supplier_id: null,
                ref: "Opening",
                type: "OP",
                entry_date: "",
                description: "",
                debit: 0,
                amount_due: 0,
                dr_balance: 0,
                cr_balance: 0
            }
        };
    },
    mounted() {
         this.form_data.supplier_id = this.data.id;
        this.form_data.entry_date = this.getCurrentDate();
        console.log("Component mounted."+JSON.stringify(this.data));
    },
    methods:{
        async addBalance() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/supp_ledger/addBalances",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("has been recorded ");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
    }
};
</script>
