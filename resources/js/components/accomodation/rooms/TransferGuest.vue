<template>
    <div class="container">
        <div class="row justify-content-center">
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
            <div class="col-md-10">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h3>Available Rooms.</h3>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div
                                class="col-2"
                                v-for="(data, i) in rooms_data"
                                :key="i"
                                v-if="data.occupation == 'empty'"
                                :class="[
                                    'room-badge',
                                    {
                                        'dirty-room':
                                            data.usage_status == 'not_ready',
                                    },
                                ]"
                                @click="transferRoom(data)"
                            >
                                <b> DOOR:{{ data.door_name }}</b
                                ><br />
                                <hr />
                                <b> FLOOR:{{ data.floor_no }}</b
                                ><br />
                                <div class="d-flex justify-content-between">
                                    <button
                                        class="btn btn-success btn-sm"
                                        @click="showPricingModal(data)"
                                    >
                                        Select Package
                                    </button>
                                </div>
                            </div>
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
                            <th></th>
                        </thead>
                        <tbody>
                            <tr v-for="(data, i) in rates_data" :key="i">
                                <td>{{ data.room_standard.name }}</td>
                                <td>{{ data.room_package.name }}</td>
                                <td>{{ data.rate }}</td>
                                <td>
                                    <button
                                        class="btn btn-primary btn-sm"
                                        @click="completeTransfer(data)"
                                    >
                                        Transfer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
export default {
    props: ["checkout_data"],
    data() {
        return {
            active: {
                dashboard: true,
                add_details: false,
            },
            rates_data: [],
            isLoading: true,
            price_modal: false,
            search: "",
            room_details: null,
            rooms_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
            form_data: {
                qty: 0,
                id: null,
                room_id: null,
                room_rate_id: null,
                room_package_id: null,
                price: 0,
                total: 0,
                tax_amount: 0,
                tax_rate: 0,
            },
        };
    },
    components: {},
    mounted() {
        
        this.fetchRooms();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.fetchRooms();
            }, 500),
        },
    },
    methods: {
        async completeTransfer(data) {
           
            this.form_data.id = this.checkout_data.id;
          
            this.form_data.room_rate_id = data.id;
            this.form_data.room_package_id = data.room_package_id;
            this.form_data.qty = this.checkout_data.qty;
            this.form_data.price = data.rate;
            this.form_data.total = this.form_data.qty * this.form_data.price;
            this.form_data.tax_rate = this.checkout_data.tax_rate;
            var row_vat = this.calculateTax(
                this.form_data.tax_rate,
                this.form_data.total
            );
            this.form_data.tax_amount = row_vat;
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/room_transfer/update",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Successfully transfered");
                this.$emit("dashboard-active")
            } else {
                this.form_error(res);
            }
        },
        async showPricingModal(data) {
           this.form_data.room_id=data.id
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
        async transferRoom(data) {},
        async fetchRooms() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/rooms/fetch", {
                params: {
                    search: this.search,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.rooms_data = res.data;
            } else {
                this.servo();
            }
        },
    },
};
</script>
<style scoped>
.dirty-room {
    background: #827717 !important;
    margin: 2px;
}
.room-badge {
    border: 1px solid #827717;
    border-radius: 2px;
    background: #e0e0e0;
}
.room-badge:hover {
    cursor: pointer;
}
</style>
