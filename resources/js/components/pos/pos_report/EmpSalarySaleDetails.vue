<template>
    <div class="row">
        <div class="row">
            <div class="col-10 p-4">
              
               
                
                <br />

                <div
                    class="d-flex flex-column mt-2 mb-2"
                    v-for="(value, i) in order_data"
                    :key="i"
                  
                >
                    <div class="row">
                        <div class="col-5">
                            <b>{{
                                value.stock ? value.stock.product_name : "NA"
                            }}</b>
                        </div>
                        <div class="col-2">
                            <b> X </b> <b> {{ value.qty }}</b>
                        </div>
                        <div class="col-1">
                            <b> {{ cashFormatter(value.row_total) }}</b>
                        </div>
                    </div>
                    <div class="d-flex justify-content-start ">
                        <p class="mr-2">Unit Price /</p>

                        <p>{{ cashFormatter(value.price) }}</p>
                       
                        <p class="ml-2">
                            {{ value.notes }}
                        </p>
                    </div>
                    <hr />
                </div>

               
              
              
               
              
            </div>
        </div>

       
        
    
    </div>
</template>
<script>
import {
    mdiBackburger,
    mdiNotebookEdit,
    mdiArrowRightBold,
    mdiPrinter,
    mdiArrowSplitVertical,
    mdiBookmarkOutline,
    mdiTrashCanOutline,
    mdiPlusThick,
    mdiMinusThick,
    mdiHome,
    mdiCashPlus,
    mdiAccountPlus
} from "@mdi/js";
export default {
    components: {   },
    props: ["order_data_details", "total_tax"],
    data() {
        return {
            order_data: [],
            branchInfo: "",
            userInfo: "",
            sub_total:0,
            total_sale:0,
            service_charge_total:0,
            form_data: {
                order_no: "",
              
                total_vat:0,
                receipt_total:0,
            },

            icons: {
                mdiBackburger,
                mdiNotebookEdit,
                mdiArrowRightBold,
                mdiPrinter,
                mdiArrowSplitVertical,
                mdiBookmarkOutline,
                mdiTrashCanOutline,
                mdiPlusThick,
                mdiMinusThick,
                mdiHome,
                mdiCashPlus,
                mdiAccountPlus
            }
        };
    },

    mounted() {
      
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
      
   this.form_data.order_no=this.order_data_details.order_data
       this.total_sale=this.order_data_details.total_sale
        this.fetchData();
    },
    methods: {
        printReceipt() {
            printJS("printReceipt", "html");
        },
        printBill() {
            this.$refs.printBillComponent.printBill();
        },
        async fetchData() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/fetchEmployeeSaleDeductionDetails", {
                params: {
                    order_no: this.order_data_details.order_no
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
           
                   
              
              
            } else {
                this.servo();
            }
        }
    }
};
</script>
<style scoped>
.border {
    padding: 2rem !important;
}
.t-amount {
    color: #fff;
}
.receipt_header {
    display: flex;
    flex-direction: column;
}
.receipt-menu td {
    color: #000;
    font-weight: 700;
}
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
#printReceipt {
    visibility: hidden;
}
.printer-header {
    text-align: center;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
}
.t-amount {
    color: #000;
    font-weight: 600;
}
</style>
