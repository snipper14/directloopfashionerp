<template>
    <div class="container">
        <div class="row flex-column justify-content-center">
             <div class="row d-flex justify-content-between mr-2">
                <v-btn
                    class="ma-2 v-btn-primary ml-2"
                    @click="$emit('dashboard-active')"
                    color="primary"
                    dark
                >
                    <v-icon dark left> mdi-arrow-left </v-icon>
                    Back
                </v-btn>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Reservation Credit Note</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="">Total Cost</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="checkout_data.total"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=""> Price</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="checkout_data.price"
                                        class="form-control"
                                    />
                                </div>
                                 <div class="form-group">
                                    <label for=""> Day/s</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="checkout_data.qty"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=""> Amount Paid</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="checkout_data.amount_paid"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="">Credit Note No.</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="form_data.credit_no"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Details.</label>
                                    <input
                                        type="text"
                                        v-model="form_data.details"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Credit Amount.</label>
                                    <input
                                        type="number"
                                        v-model="form_data.total"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary btn-sm"
                                    @click="generateCredit()">
                                        Generate Credit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div id="printReceipt">
         <credit-note-print-component
         v-if="show_print"
         :checkout_data="checkout_data"
         :form_data="form_data"
         />
        </div>
    </div>
</template>

<script>
import ReceiptHeader from '../../../pos/menu_components/dinerscomponents/ReceiptHeader.vue';
import CreditNotePrintComponent from './CreditNotePrintComponent.vue';
export default {
  components: { ReceiptHeader, CreditNotePrintComponent },
    props: ["checkout_data"],
    data() {
        return {
            isLoading: true,
            show_print:false,
            form_data: {
                credit_no: null,
                room_reservation_id: null,
                details: null,
                qty: 0,
                price: 0,
                total: 0,
            },
            userInfo:null,
        };
    },
    mounted() {
       this.userInfo = this.$store.state.user;
        this.form_data.room_reservation_id=this.checkout_data.id
       
        this.form_data.total =
            this.checkout_data.amount_paid - this.checkout_data.total;
        this.fetchCreditNote();
    },
    methods: {
        async generateCredit(){
            this.showLoader()
            const res=await this.callApi("POST","data/reservation_credit_note/create",this.form_data)
            this.hideLoader()
            if(res.status==200){
                this.show_print=true
             this.s("saved successfully")
                setTimeout(() => {
                printJS("printReceipt", "html");
                this.$emit('dashboard-active')
            }, 1000);
           // 
             
            }else{this.form_error(res)}
        },
        async fetchCreditNote() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/reservation_credit_note/fetchCreditNoteNumber",
                {}
            );
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.form_data.credit_no = res.data.credit_no;
            } else {
                this.servo();
            }
        },
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([this.fetchCreditNote()]).then(
                function (results) {
                    return results;
                }
            );
            this.hideLoader();
        },
    },
};
</script>
<style scoped>
#printReceipt{
    visibility: hidden;
}
</style>