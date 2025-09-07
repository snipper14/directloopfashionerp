<template>
    <div>
        <div v-if="active.dashboard" class="col-12">
            <order-details-component :data="bill_data" />

            <div class="button-nav mt-3">
               
              
               
                <button
                    @click="voidBill(data)"
                    class="btn btn-outline-primary btn-lg"
                    v-if="
                        isAdmin
                    "
                    title="admin"
                >
                    <v-icon medium>{{ icons.mdiArrowSplitVertical }}</v-icon>
                    Void Bill
                </button>
              

                <button
                    @click="cancelOrder(data.order_no, i)"
                    v-if="isAdmin"
                    type="button"
                    title="admin"
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
import OrderDetailsComponent from '../pos/menu_components/dinerscomponents/OrderDetailsComponent.vue';

export default {
    components: {OrderDetailsComponent   },
    props: ["bill_data"],
    data() {
        return {
            active: {
                dashboard: true,
                void_bills: false,
            },
            loading4: false,
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
            data: {},
        };
    },
    mounted() {
        this.data = this.bill_data;
    },
    methods: {
        mergeBill() {
            this.$store.commit("setOrderNoMerge", this.data.order_no);
            this.$router.push("merge_bill");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            // this.concurrentApiReq(1);
        },
        splitBill(order_no) {
            this.$store.commit("setOrderNo", order_no);
            this.$router.push("split_bills");
        },
        async cancelOrder(order_no, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/online_orders/destroyOrder/" + order_no,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.$emit("dismiss-modal");
                } else {
                    this.servo();
                }
            }
        },
        async archiveOrder(data) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/archive_order/archiveOnline",
                    data
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.$emit("dismiss-modal");
                } else {
                    this.servo();
                }
            }
        },
        async recollBill(data) {
            this.showLoader();
            const res = await this.getApi("data/pos_order/recallBill", {
                params: {
                    order_no: data.order_no,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var value = data.results;

                var data_obj = {
                    department: value[0].department.department,
                    id: value[0].department_id,
                };
                this.$store.commit("setOrderNo2", data.order_no);
                this.$store.commit("setDineDepartment", data_obj);
                this.$store.commit("setOrderType", value[0].order_type);
                
                if ((value[0].order_type == "takeaway")) {
                    this.$router.push("takeaway_dept_chooser");
                } else {
                    this.$router.push("dine_in");
                }
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
