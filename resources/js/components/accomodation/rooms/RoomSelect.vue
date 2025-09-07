<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>
                            <b class="green text-white">
                                Room Availability & Reservation</b
                            >
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="">Search</label>
                                <input
                                    type="text"
                                    placeholder="Search"
                                    class="form-control"
                                    style="width: 200px"
                                    v-model="search"
                                />
                            </div>

                            <div style="width: 200px" class="ml-2 form-group">
                                <label for="">Search By Occupancy</label>
                                <Select v-model="params.occupation">
                                    <Option value="empty">empty</Option>
                                    <Option value="accupied"> Occupied</Option>
                                </Select>
                            </div>
                            <div style="width: 300px" class="ml-2 form-group">
                                <label for=""
                                    >Search by HouseKeeper Status</label
                                >
                                <Select v-model="params.usage_status">
                                    <Option value="ready">ready</Option>
                                    <Option value="not_ready">
                                        not Ready</Option
                                    >
                                </Select>
                            </div>
                            <div class="center">
                                <button
                                    class="btn btn-info btn-sm ml-2"
                                    @click="showAll()"
                                >
                                    Show All
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <span class="badge badge-pill occupied-room"
                                >Occupied</span
                            >
                            <span class="badge badge-pill usage_status"
                                >Dirty Room</span
                            >
                            <span class="badge badge-pill under-maintenance"
                                >Out of Order</span
                            >
                            <span class="badge badge-pill cleaned-vacant"
                                >Cleaned Vacant</span
                            >
                        </div>
                        <div class="row">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">DOOR NAME</th>
                                        <th>STANDARD</th>
                                        <th scope="col">NO. BEDS</th>
                                        <th>FLOOR</th>

                                        <th scope="col">DETAILS</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in rooms_data"
                                        :key="i"
                                        :class="[
                                            {
                                                'under-maintenance':
                                                    data.is_under_maintenance ==
                                                    '1',
                                            },
                                            {
                                                'occupied-room':
                                                    data.occupation ==
                                                    'accupied',
                                            },
                                        ]"
                                    >
                                        <td
                                            :class="{
                                                usage_status:
                                                    data.usage_status ==
                                                    'not_ready',
                                            }"
                                        >
                                            {{ data.door_name }}
                                        </td>
                                        <td>
                                            {{ data.room_standard.name }}
                                        </td>

                                        <td>
                                            {{ data.qty_bed }}
                                        </td>
                                        <td>
                                            {{ data.floor_no }}
                                        </td>

                                        <td>
                                            {{ data.details }}
                                        </td>

                                        <td>
                                            <button
                                                v-if="isWritePermitted"
                                                class="btn btn-primary btn-sm"
                                                @click="bookRoom(data)"
                                            >
                                                Book Room
                                            </button>
                                            <button
                                                class="btn btn-secondary btn-sm"
                                                @click="showPricingModal(data)"
                                            >
                                                Pricing
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="price_modal" title="Room Rates ">
            <div class="row">
                <div class="col-11">
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <th>Room Type</th>
                            <th>Package</th>
                            <th>Price</th>
                        </thead>
                        <tbody>
                            <tr v-for="(data, i) in rates_data" :key="i">
                                <td>{{ data.room_standard.name }}</td>
                                <td>{{ data.room_package.name }}</td>
                                <td>{{ data.rate }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
export default {
    data() {
        return {
            search: "",
            price_modal: false,
            rooms_data: [],
            rates_data: [],
            params: { occupation: "", usage_status: "" },
            form_data: {
                from: "",
                to: "",
            },
        };
    },
    mounted() {
        this.getData();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.getData();
            }, 500),
        },
        params: {
            deep: true,
            handler() {
                this.getData();
            },
        },
    },
    methods: {
        async showAll() {
            this.showLoader();
            const res = await this.getApi("data/rooms/fetch", {
                params: {},
            });
            this.hideLoader();
            if (res.status == 200) {
                this.rooms_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async showPricingModal(data) {
            this.showLoader();
            const res = await this.callApi("post", "data/room_rate/checkRate", {
                room_standard_id: data.room_standard_id,
            });
            this.hideLoader();
            if (res.status === 200) {
                this.rates_data = res.data;
                this.price_modal = true;
            } else {
                this.servo();
            }
        },
        bookRoom(data) {
            if (data.is_under_maintenance == "1"){
                this.errorNotif("Room is under maintenance");
                    return; 
            }
                if (data.usage_status == "not_ready") {
                    this.errorNotif("You can't book a dirty room");
                    return;
                }
            this.$store.commit("setRoomData", data);
            this.$router.push("reservation");
        },
        async getData() {
            this.showLoader();
            const res = await this.getApi("data/rooms/fetch", {
                params: {
                    search: this.search,
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.rooms_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
<style scoped>
.occupied-room {
    background: #9fa8da !important;
}
.under-maintenance {
    background: #880e4f !important;
}
.cleaned-vacant {
    border-style: solid;
    border-color: #000;
    background: #fff !important;
    color: #000 !important;
}
.usage_status {
    background: #bf360c !important;
}
.badge-pill {
    text-align: center !important;
    height: 18px;
    color: #fff;
}
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
</style>
