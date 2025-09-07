<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <HouseKeepingNav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <h3><b> House Keeping Reservation Info.</b></h3>
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5"></div>
                        <div class="col-md-4 float-md-right ">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Room No</th>
                                    <th scope="col">Guest Name</th>

                                    <th scope="col">Guest Phone</th>
                                    <th scope="col">No Of Items</th>
                                    <th scope="col">Total Clearing Cost</th>
                                    <th>HouseKeeper</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in house_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.room.door_name }}
                                    </td>
                                    <td>
                                        {{
                                            data.room_reservation
                                                ? data.room_reservation.name
                                                : ""
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            data.room_reservation
                                                ? data.room_reservation.phone
                                                : ""
                                        }}
                                    </td>
                                    <td>{{ data.total_qty }}</td>

                                    <td>
                                        {{
                                            cashFormatter(data.total_consumable)
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            data.house_keeper.first_name +
                                                " " +
                                                data.house_keeper.last_name
                                        }}
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="viewDetails(data)"
                                            >View Details</router-link
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="house_data !== null"
                            v-bind:results="house_data"
                            v-on:get-page="fetchHseKeepingReport"
                        ></Pagination>
                        Items Count {{ house_data.total }}
                    </div>
                    <div class="row">
                        <div class="col-4 d-flex" v-if="isDownloadPermitted">
                            <export-excel
                                class="btn btn-default btn-export ml-2 "
                                :fetch="exportExcel"
                                worksheet="My Worksheet"
                                name="filename.xls"
                            >
                                <v-icon medium>{{
                                    icons.mdiMicrosoftExcel
                                }}</v-icon>
                                Export Excel
                            </export-excel>

                            <export-excel
                                class="btn btn-default btn-export ml-2 "
                                :fetch="exportExcel"
                                worksheet="My Worksheet"
                                type="csv"
                                name="filename.xls"
                            >
                                <v-icon class="v-icon" medium>{{
                                    icons.mdiFileExcel
                                }}</v-icon>
                                Export CSV
                            </export-excel>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <Modal v-model="modal_details" width="860">
            <HouseConsumableDetails v-if="data && modal_details" :data="data" />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import HouseKeepingNav from "./HouseKeepingNav.vue";
import HouseConsumableDetails from "./HouseConsumableDetails.vue";
import Pagination from "../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
export default {
    data() {
        return {
            active: {
                dashboard: true
            },
            isLoading: true,
            modal_details: false,
            data: null,
            search: "",
            house_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        HouseKeepingNav,
        Unauthorized,
        HouseConsumableDetails,
        Pagination
    },
    watch: {
        search: {
            handler: _.debounce(function() {
                this.isLoading = false;
                this.fetchHseKeepingReport(1);
            })
        }
    },
    mounted() {
        this.fetchHseKeepingReport(1);
    },

    methods: {
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi("data/house_keeping/fetch", {
                params: {
                    ...this.search_params,
                    search: this.search
                }
            });
            this.hideLoader();
            var data = res.data;
            var data_array = [];
            data.map(array_item => {
                data_array.push({
                    room: array_item.room.door_name,
                    house_keeper:
                        array_item.house_keeper.first_name +
                        " " +
                        array_item.house_keeper.last_name,
                    total_consumable: array_item.total_consumable,
                    total_qty: array_item.total_qty,
                    created_at: this.formatDateTime(array_item.created_at)
                });
            });
            return data_array;
        },
        async viewDetails(data) {
            this.data = data;
            this.modal_details = true;
        },
        async fetchHseKeepingReport(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/house_keeping/fetch", {
                params: {
                    page: page,
                    ...this.search_params,
                    search: this.search
                }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.house_data = res.data;
            } else {
                this.servo();
            }
        }
    }
};
</script>
