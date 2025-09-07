<template>
    <div class="container-fluid">
        <div v-if="isReadPermitted">
            <div class="row">
                <div class="col-4 d-flex">
                    <button
                        class="btn btn-primary"
                        @click="$router.push('posmenu')"
                    >
                        <h3>
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </h3>
                    </button>
                    <center class="mt-3 ml-2">
                        <h4>
                            <b>{{ orderType }}</b>
                        </h4>
                    </center>
                    <button class="btn btn-warning" @click="logoutPOS()">
                        Log out
                    </button>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <div class="row">
                        <div class="col-3">
                            <h4><b> Rooms Reservations</b></h4>
                        </div>
                        <div class="col-md-4">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div
                            class="col-2"
                            v-for="(data, i) in room_data"
                            :key="i"
                            :class="[
                                'room-badge',
                                {
                                    'occupied-room':
                                        data.occupation == 'accupied',
                                },
                            ]"
                        >
                            <b> DOOR:{{ data.room.door_name }}</b
                            ><br />
                            <hr />
                            <b> FLOOR:{{ data.room.floor_no }}</b
                            ><br />
                            <hr />
                            <b> Package:{{ data.room_package.name }}</b
                            ><br />
                            <hr />
                            <!-- <b> Guest:{{ data.guest.name }}</b> -->
                            <hr />
                            <br />
                            <button
                                @click="addRoomService(data)"
                                class="btn btn-primary btn-sm"
                            >
                                Add Room Service
                            </button>
                            <button
                                @click="fetchPackageDetails(data)"
                                class="btn btn-secondary btn-sm mt-2"
                            >
                                View Perks
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else>
            <unauthorized />
        </div>
        <Modal v-model="modal_package_details" width="860">
            <room-package-perks
                v-if="package_data && modal_package_details"
                :package_data="package_data"
            />
        </Modal>
        <scroll-widget-component />
    </div>
</template>

<script>
import { mdiBackburger } from "@mdi/js";
import Unauthorized from "../../utilities/Unauthorized.vue";
import RoomPackagePerks from "./RoomPackagePerks.vue";
import ScrollWidgetComponent from "../../utilities/ScrollWidgetComponent.vue";
export default {
    data() {
        return {
            isLoading: false,
            current: "",
            modal_package_details: false,
            room_data: [],
            orderType: "",
            search: "",
            package_data: null,
            order_data: [],

            icons: {
                mdiBackburger,
            },
            no_guest: 0,
        };
    },
    mounted() {
        this.orderType = this.$store.state.order_type;
        this.concurrentApiReq();
    },
    components: {
        Unauthorized,
        RoomPackagePerks,
        ScrollWidgetComponent,
    },
    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.fetchRervations();
            }, 500),
        },
    },
    methods: {
        async logout() {
            this.$router.push("waiter_logout");
            window.location.reload();
        },
        async addRoomService(data) {
            this.$store.commit("setRoomServiceData", data);
            this.$router.push("room_service_select_dept");
        },
        async fetchPackageDetails(data) {
            this.modal_package_details = true;

            this.package_data = data;
        },
        newOrder(value) {
            this.$store.commit("setLocation", value);

            this.$router.push("create_dineorder");
        },

        async getOrders() {
            const res = await this.getApi(
                "data/pos_order/fetchTableOrders",
                ""
            );

            if (res.status == 200) {
                this.order_data = res.data;
                this.mergeTableOrder();
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchRervations() {
            this.isLoading ? this.showLoader() : "";

            const res = await this.getApi("data/reservation/fetch", {
                params: {
                    search: this.search,
                    status: "occupied",
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.room_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.fetchRervations(),

                // this.getOrders()
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
    },
};
</script>
<style scoped>
.menu-icon {
    height: 70px;
    width: 70px;
    border-radius: 50%;
}
.room-badge {
    border: 1px solid #827717;
    border-radius: 2px;
    background: #e0e0e0;
}
.room-badge:hover {
    cursor: pointer;
}
.occupied-room {
    background: #9fa8da !important;
}
</style>
