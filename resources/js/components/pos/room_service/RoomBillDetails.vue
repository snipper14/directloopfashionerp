<template>
    <div>
        <div v-if="active.dashboard" class="col-12">
           <order-details-component
           :data="bill_data"
           />

            <div class="button-nav mt-3">
                <button
                    @click="recollBill(data)"
                    v-if="isUpdatePermitted &&  data.isBillPrinted == '0'"
                    type="button"
                    class="btn btn-warning btn-lg ml-2"
                >
                    Recall Bill
                </button>

                <button
                    @click="voidBill(data)"
                    class="btn btn-outline-primary btn-lg"
                    v-if="isAdmin && bill_data.isComplete == 1 && data.isBillPrinted == '0'"
                >
                    <v-icon medium>{{ icons.mdiArrowSplitVertical }}</v-icon>
                    Void Bill
                </button>
                 
                                    <button
                                       v-if="isUpdatePermitted && bill_data.isComplete == 1 && data.isBillPrinted == '0'"
                                        @click="mergeBills(data)"
                                        class="btn btn-outline-primary"
                                    >
                                        Merge Bills
                                    </button>
                             
                <button
                    @click="cancelOrder(data.order_no, i)"
                    v-if="isAdmin && data.isBillPrinted == '0'"
                    type="button"
                    class="btn btn-danger btn-lg"
                >
                    Cancel Order
                </button>
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
    mdiArrowUpCircleOutline,
    mdiArrowDownCircleOutline,
} from "@mdi/js";
import ItemParticularsComponent from '../menu_components/dinerscomponents/ItemParticularsComponent.vue';
import OrderDetailsComponent from '../menu_components/dinerscomponents/OrderDetailsComponent.vue';

export default {
  components: { ItemParticularsComponent, OrderDetailsComponent },
    props: ["bill_data"],
    data() {
        return {
            active: {
                dashboard: true,
                void_bills: false,
            },
            void_data: null,
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
                mdiArrowUpCircleOutline,
                mdiArrowDownCircleOutline,
            },
            data: null,
        };
    },
    mounted() {
        this.data = this.bill_data;
    },
    methods: {
          mergeBills(data) {
            this.$store.commit("setRSMBOrderNo", data.order_no);
            this.$router.push("merge_room_service_bills");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            // this.concurrentApiReq(1);
        },

        async cancelOrder(order_no, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/room_order/destroyOrder/" + order_no,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    console.log("deeeeelete");
                    this.$emit("dismiss-modal");
                } else {
                    this.servo();
                }
            }
        },

        async recollBill(data) {
            this.showLoader();
            const res = await this.getApi("data/room_order/recallBill", {
                params: {
                    order_no: data.order_no,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var value = data.results;

                var data_obj = {
                    guest_id: value[0].guest_id,
                    room_id: value[0].room_id,
                    room_package_id: value[0].room_package_id,
                    guest: { name: value[0].guest.name },
                    id: value[0].room_reservation_id,

                    room: {
                        door_name: value[0].room.door_name,
                        floor_no: value[0].room.floor_no,
                    },
                    room_package: {
                        name: value[0].room_package.name,
                    },
                };
                this.$store.commit("setOrderNo2", data.order_no);
                this.$store.commit("setRoomServiceData", data_obj);
                this.$store.commit("setOrderType", value[0].order_type);
                this.$router.push("room_service_select_dept");
            } else {
                this.servo();
            }
        },
        voidBill(data) {
            this.$emit("void_info", data);
        },
    },
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
.cart-items {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
</style>
