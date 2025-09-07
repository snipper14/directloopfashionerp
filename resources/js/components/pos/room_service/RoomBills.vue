<template>
    <div>
        <div v-if="active.dashboard">
            <div v-if="isReadPermitted">
                <div class="jumbotron">
                    <div class="row d-flex flex-column">
                        <div>
                            <h4 class="mt-4"><b>ROOM ORDERS</b></h4>
                            <br />
                        </div>
                        <div class="d-flex justify-content-between">
                            <button
                                class="btn btn-primary"
                                @click="$router.push('posmenu')"
                            >
                                <b>
                                    <v-icon medium>{{
                                        icons.mdiBackburger
                                    }}</v-icon>
                                    BACK</b
                                >
                            </button>

                            <input
                                type="text"
                                style="width: 200px"
                                placeholder="Search Room"
                                class="form-control"
                                v-model="door_name"
                            />
                            <input
                                type="text"
                                style="width: 200px"
                                placeholder="Search "
                                class="form-control"
                                v-model="search"
                            />
                            <div class="ml-4">
                                <span class="badge badge-pill printed-bills"
                                    >Bill Printed</span
                                >
                            </div>
                        </div>
                    </div>
                    <hr class="my-4" />
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Order No</th>
                                    <th>Order Time</th>
                                    <th>Room</th>
                                    <th>Guest</th>
                                    <th>Cashier</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="large-tr"
                                    :class="[
                                        data.order_no,
                                        bill_current === data.order_no
                                            ? 'bill-active'
                                            : '',
                                    ]"
                                    v-for="(data, i) in bills_data"
                                    :key="i"
                                >
                                    <td
                                        :class="[
                                            {
                                                'printed-bills':
                                                    data.isBillPrinted == '1',
                                            },
                                        ]"
                                    >
                                        {{ data.order_no }}
                                    </td>

                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>
                                    <td>
                                        {{ data.door_name }}
                                    </td>
                                    <td>{{ data.guest_name }}</td>
                                    <td>{{ data.user.name }}</td>
                                    <td>
                                        {{ cashFormatter(data.order_total) }}
                                    </td>
                                    <td>
                                        <button
                                            @click="updateBillPrintStatus(data)"
                                            v-if="
                                                isDownloadPermitted &&
                                                data.isComplete == 1 &&
                                                data.isBillPrinted == '0'
                                            "
                                            type="button"
                                            class="btn btn-secondary btn-lg"
                                        >
                                            <v-icon medium>{{
                                                icons.mdiPrinter
                                            }}</v-icon
                                            >Print Bill
                                        </button>
                                        <button
                                            class="btn btn-primary btn-lg"
                                            @click="viewDetails(data)"
                                        >
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <RoomServiceBillPrint
                            v-if="order_data.length > 0"
                            :order_data="order_data"
                            :bill_total="total_order"
                            :tax_total="total_tax"
                            :sub_total="sub_total"
                            :service_charge_total="service_charge_total"
                            ref="printBillComponent"
                        />
                    </div>
                </div>
            </div>
            <div v-else>
                <unauthorized />
            </div>
        </div>
        <Modal
            v-model="details_modal"
            title="Bill Details"
            :styles="{ top: '20px' }"
        >
            <room-bill-details
                v-if="details_modal"
                :bill_data="bill_data"
                v-on:void_info="voidBillsFromChild"
                v-on:dismiss-modal="dismissModal"
            />
        </Modal>
        <void-room-service
            :void_data="void_data"
            v-if="active.void_bills"
            v-on:dashboard-active="setActiveRefresh"
        />
        <scroll-widget-component />
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
} from "@mdi/js";
import RoomServiceBillPrint from "./RoomServiceBillPrint.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import _ from "lodash";
import VoidRoomService from "./VoidRoomService.vue";
import ScrollWidgetComponent from "../../utilities/ScrollWidgetComponent.vue";
import RoomBillDetails from "./RoomBillDetails.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                void_bills: false,
            },
            details_modal: false,
            bills_data: [],
            door_name: "",
            search: "",
            order_data: [],
            bill_current: "",
            form_data: {
                room_id: "",
            },
            total_order: 0,
            isLoading: true,
            sub_total: 0,
            total_tax: 0,
            service_charge_total: 0,
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
            },
        };
    },
    components: {
        RoomServiceBillPrint,
        Unauthorized,
        VoidRoomService,
        ScrollWidgetComponent,
        RoomBillDetails,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq(1);
            }, 500),
        },
        door_name: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    methods: {
        async updateBillPrintStatus(data) {
            const shouldPrint = await this.confirmDialogue(
                "You want to print this bill"
            );
            if (!shouldPrint) {
                return;
            }
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_order/updateBillPrintStatus",
                {
                    order_no: data.order_no,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                let index = this.bills_data.findIndex(function (item, i) {
                    return item.order_no === data.order_no;
                });

                this.bills_data[index].isBillPrinted = "1";
                this.print(data);
            } else {
                this.servo();
            }
        },
        dismissModal() {
            this.details_modal = false;
            this.concurrentApiReq(1);
        },
        voidBillsFromChild(data) {
            this.details_modal = false;
            this.void_data = data;
            this.setActiveComponent("void_bills");
        },
        viewDetails(data) {
            this.details_modal = true;
            this.bill_data = data;
        },
        voidBill(data) {
            this.void_data = data;

            this.setActiveComponent("void_bills");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq(1);
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
        async deleteRecord(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/room_order/destroy",
                    {
                        id: data.id,
                    }
                );

                if (res.status === 200) {
                    this.successNotif("deleted ");
                    this.concurrentApiReq();
                } else {
                    this.form_error(res);
                }
            }
        },

        billClck(data) {
            this.order_data = data.results;
            this.bill_current = data.order_no;
        },
        print(data) {
            this.order_data = data.results;
            this.total_order = data.order_total;
            this.sub_total = data.sum_sub_total;
            this.service_charge_total = data.sum_service_charge_amount;
            this.total_tax = data.total_tax;
            var ref = this.$refs;
            var ctx = this;
            ctx.showLoader();
              setTimeout(() => {
                this.hideLoader();
                ref.printBillComponent.printBill();
                this.order_data = [];
            }, 1000);
        },
        async getBills() {
            const res = await this.getApi("data/room_order/fetchBills", {
                params: {
                    room_id: this.form_data.room_id,
                    search: this.search,
                    door_name: this.door_name,
                },
            });

            if (res.status == 200) {
                this.bills_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            const res = await Promise.all([this.getBills()]).then(function (
                results
            ) {
                return results;
            });
            this.isLoading ? this.hideLoader() : "";
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
.printed-bills {
    background: #ffb74d !important;
}
</style>
