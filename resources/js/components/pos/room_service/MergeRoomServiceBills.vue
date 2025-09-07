<template>
    <div>
        <div v-if="isReadPermitted">
            <div class="jumbotron">
                <div class="row d-flex flex-column">
                    <div>
                        <h4 class="mt-4"><b>CUSTOMER ORDERS</b></h4>
                        <br />
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-primary" @click="goBack()">
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </button>
                        <h3 class="ml-3">Merge Order >></h3>

                        <input
                            type="text"
                            style="width:200px"
                            placeholder="Search Order No"
                            class="form-control"
                            v-model="search"
                        />
                        <input
                            type="text"
                            style="width:200px"
                            placeholder="Search Room"
                            class="form-control"
                            v-model="door_name"
                        />
                    </div>
                </div>
                <hr class="my-4" />
                <div class="row">
                    <div class="col-4" v-for="(data, i) in bills_data" :key="i" v-if="data.order_no != $store.state.room_service_mb_order_no ">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <button
                                            @click="mergeBill(data.order_no)"
                                            class="btn btn-secondary"
                                        >
                                            Merge Bill
                                        </button>
                                    </div>
                                    <div class="col-7">
                                        <p>
                                            Order No: <b>{{ data.order_no }}</b>
                                        </p>
                                        <br />
                                        <p>
                                           Room:
                                            <b>{{
                                                data.door_name
                                            }}</b>
                                        </p>
                                        <br />
                                        <p>
                                            No Guest: <b>{{ data.no_guest }}</b>
                                        </p>
                                        <br />
                                    </div>
                                </div>
                                <div class="bill-wrapper">
                                    <div
                                        class="d-flex flex-column mt-2 mb-2"
                                        v-for="(value, i) in data.results"
                                        :key="i"
                                    >
                                        <div class="row">
                                            <div class="col-5">
                                                <b>{{
                                                    value.stock
                                                        ? value.stock
                                                              .product_name
                                                        : "NA"
                                                }}</b>
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
                                                <v-icon
                                                    v-if="isDeletePermitted"
                                                    @click="
                                                        deleteRecord(value, i)
                                                    "
                                                    style="color:red"
                                                    medium
                                                    >{{
                                                        icons.mdiTrashCanOutline
                                                    }}</v-icon
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
                                                        data.total_tax
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
                                                        data.order_total
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
        <scroll-widget-component/>
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
import Unauthorized from "../../utilities/Unauthorized.vue";
import _ from "lodash";
import ScrollWidgetComponent from '../../utilities/ScrollWidgetComponent.vue';
export default {
    data() {
        return {
            bills_data: [],
           door_name:null,
            search: "",
            order_data: [],
            bill_current: "",
           
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
        ScrollWidgetComponent
    },
    mounted() {
       
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.concurrentApiReq(1);
            }, 500)
        },
         door_name: {
            handler: _.debounce(function(val, old) {
                this.concurrentApiReq(1);
            }, 500)
        }
    },
    methods: {
        async mergeBill(order_no) {
            const isConfirmed = await this.confirmDialogue(
                "Confirm merge bill"
            );
            if (isConfirmed) {
                const res = await this.callApi(
                    "POST",
                    "data/room_order/mergeBills",
                    {
                        original_order_no: this.$store.state.room_service_mb_order_no,
                        merged_order_no: order_no
                    }
                );
                if (res.status == 200) {
                    this.s("successfully merged");
                    this.$router.push('room_bills')
               //     this.goBack();
                } else {
                    this.servo();
                }
            }
        },
      

        async getBills() {
            const res = await this.getApi("data/room_order/fetchBills", {
                params: {
                    search: this.search,
                    door_name:this.door_name,
                       bill_printed_status:false,
                }
            });

            if (res.status == 200) {
                this.bills_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getBills()]).then(function(
                results
            ) {
                return results;
            });
            this.hideLoader();
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
