<template>
    <div>
        <div v-if="isReadPermitted">
            <div class="jumbotron">
                <div class="row d-flex flex-column">
                    <div>
                        <h4 class="mt-4"><b>VOID GUEST BILL</b></h4>
                        <br />
                    </div>
                    <div class="d-flex justify-content-between">
                        <button
                            class="btn btn-primary"
                            @click="$emit('dashboard-active')"
                        >
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </button>
                    </div>
                </div>
                <hr class="my-4" />
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <button
                                            @click="showModal()"
                                            class="btn btn-secondary"
                                        >
                                            Void All
                                        </button>
                                    </div>
                                    <div class="col-7">
                                        <p>
                                            Order No:
                                            <b>{{ bills_data.order_no }}</b>
                                        </p>
                                        <br />
                                        <p>
                                            Room No:
                                            <b>{{
                                                bills_data.results[0].room.door_name
                                            }}</b>
                                        </p>
                                        <br />
                                        <p>
                                           Package:
                                            <b>{{ bills_data.results[0].room_package.name }}</b>
                                        </p>
                                        <br />
                                    </div>
                                </div>
                                <div class="bill-wrapper">
                                    <div
                                        class="d-flex flex-column mt-2 mb-2"
                                        v-for="(value, i) in bills_data.results"
                                        :key="i"
                                    >
                                        <div class="row">
                                            <div class="col-5">
                                              <item-particulars-component
                                              :data="value"
                                              />
                                            </div>
                                            <div class="col-2">
                                                <b> X </b>
                                                <b> {{ value.qty }}</b>
                                            </div>
                                            <div class="col-3">
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            value.row_total
                                                        )
                                                    }}</b
                                                >
                                            </div>
                                            <div class="col-2">
                                                <button class="btn btn-warning btn-sm"
                                                    v-if="isDeletePermitted"
                                                    @click="
                                                        voidSingleModal(value)
                                                    "
                                                  
                                                    >Void</button

                                                >
                                            </div>
                                        </div>
                                        <div
                                            class="d-flex justify-content-start "
                                        >
                                            <p class="mr-2">Unit Price /</p>

                                            <p>
                                                {{ cashFormatter(value.price) }}
                                            </p>
                                            <p class="ml-2">
                                                Guest
                                                <span
                                                    class="badge badge-secondary"
                                                    >{{ value.no_guest }}</span
                                                >
                                            </p>
                                            <p class="ml-2">
                                                {{ value.notes }}
                                            </p>
                                        </div>
                                        <hr />
                                    </div>
                                    <div
                                        class="d-flex justify-content-end mt-1 mr-4"
                                    >
                                        <span>
                                            <b>
                                                TAX:
                                                {{
                                                    cashFormatter(
                                                        bills_data.total_tax
                                                    )
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                    <div
                                        class="d-flex justify-content-end mt-2 mr-4"
                                    >
                                        <span>
                                            <b>
                                                TOTAL:
                                                {{
                                                    cashFormatter(
                                                        bills_data.order_total
                                                    )
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else>
            <unauthorized />
        </div>
        <Modal v-model="notes_modal" title="Add Void Notes">
            <div class="row">
                <div class="col-11">
                     <void-list-multi-component
                    v-if="voided_data"
                      :voided_data="voided_data"
                        v-on:onVoidAllSelect="onVoidAllSelect" 
                    />
                </div>
              
            </div>
        </Modal>
        <Modal v-model="notes_modal_item" title="Add Void Notes">
            <div class="row">
                <div class="col-11">
                    <div class="form-group">
                        <label for="">Qty*</label>
                        <input
                            type="number"
                            v-model="form_data.qty"
                            class="form-control"
                        />
                    </div>
                      <custom-board
                        style="margin-top: 4px"
                        @pressed="form_data.qty = $event"
                        :selfValue="form_data.qty"
                    />
                </div>
                <div class="col-11">
                   <void-list-component
                     v-if="voided_data"
                     :voided_data="voided_data"
                      v-on:onVoidSelect="onVoidSelect"/>
                </div>

            </div>
        </Modal>
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
    mdiHome
} from "@mdi/js";
import _ from "lodash";
import Unauthorized from '../../utilities/Unauthorized.vue';
import ItemParticularsComponent from '../menu_components/dinerscomponents/ItemParticularsComponent.vue';
import VoidListComponent from '../menu_components/dinerscomponents/VoidListComponent.vue';
import CustomBoard from '../menu_components/dinerscomponents/CustomBoard.vue';
import VoidListMultiComponent from '../menu_components/dinerscomponents/VoidListMultiComponent.vue';
export default {
    props: ["void_data"],
    data() {
        return {
            notes_modal_item: false,
            notes_modal: false,
            bills_data: [],
            notes: "",
            total_amount: 0,
             voided_data:null,
            form_data: {
                accompaniment_id:null,
                qty: 1,
                id: null,
                notes: "",
                price:0,
                row_total:0,
                cost_price:0,
                cost_total:0,
                tax_rate:0,
                row_vat:0
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
                mdiHome
            }
        };
    },
    components: {
        Unauthorized,
        ItemParticularsComponent,
        VoidListComponent,
        CustomBoard,
        VoidListMultiComponent
        ,
    },
    mounted() {
        this.bills_data = this.void_data;
      this.getVoidData()
    },

    methods: {
         onVoidAllSelect(val){
             this.form_data.notes=val.details
            this.voidAll()
        },
           async getVoidData() {
            this.showLoader();
            const res = await this.getApi("data/void_items/fetch",  {});
            this.hideLoader();
            if (res.status == 200) {
                this.voided_data = res.data;
               
            } else {
                this.swr("Server error occurred");
            }
        }
    ,
        onVoidSelect(val) {
            
            this.form_data.notes=val.details
            this.submitSingle()
        },
        voidSingleModal(value) {
            this.notes_modal_item = true;
            this.form_data.id = value.id;
            this.form_data.price=value.price
            this.form_data.cost_price=value.cost_price,
            this.form_data.tax_rate=value.stock.tax_dept.tax_rate
           },
        async submitSingle() {
            this.form_data.row_total=this.form_data.qty*this.form_data.price
            this.form_data.cost_total=this.form_data.qty*this.form_data.cost_price
            this.form_data.row_vat=this.calculateTax(this.form_data.tax_rate, this.form_data.row_total)
            this.showLoader()
            const res=await  this.callApi("POST","data/void_sale/voidRoomServiceSingle",this.form_data)
            this.hideLoader()
        if(res.status==200){
            this.successNotif("Voided successfully")
            this.notes_modal_item=false
            this.$emit('dashboard-active')
        }else{
            this.form_error(res)
        }
        },
        showModal() {
            this.notes_modal = true;
        },
        async voidAll() {
            this.showLoader();
            const res = await this.callApi("POST", "data/void_sale/voidRoomServiceSale", {
                notes: this.form_data.notes,
                 order_no:this.bills_data.order_no,
                receipt_total: this.bills_data.order_total
            });
            this.hideLoader();
            if (res.status == 200) {
                this.notes_modal = false;
                this.successNotif("Voided successfully");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        }
    }
};
</script>
<style scoped>
.bill-active {
    background: #bec5b7 !important;
}
.bill-wrapper {
    height: 300px;
    overflow-x: hidden;
    overflow-y: auto;
}
</style>
