<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <HouseKeepingNav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h3>House Cleaning.</h3>
                        </div>
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
                                            data.usage_status == 'not_ready'
                                    }
                                ]"
                                @click="addDetails(data)"
                            >
                                <b> DOOR:{{ data.door_name }}</b
                                ><br />
                                <hr />
                                <b> FLOOR:{{ data.floor_no }}</b
                                ><br />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <AddHouseKeepingConsumables
            v-if="active.add_details"
            :room_details="room_details"
            v-on:dashboard-active="setActiveRefresh"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import HouseKeepingNav from "./HouseKeepingNav.vue";
import AddHouseKeepingConsumables from "./AddHouseKeepingConsumables.vue";
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
                dashboard: true,
                add_details: false
            },
            isLoading: true,
            search: "",
            room_details: null,
            rooms_data: [],
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
        AddHouseKeepingConsumables
    },
    mounted() {
        this.fetchRooms();
    },
    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.fetchRooms();
            }, 500)
        }
    },
    methods: {
        addDetails(data) {
          
            this.room_details = data;
            this.setActiveComponent("add_details");
        },

        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },

        setActiveRefresh: function() {
            this.setActiveComponent("dashboard");
            this.fetchRooms();
        },
        async fetchRooms() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/rooms/fetch", {
                params: {
                    search: this.search
                }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.rooms_data = res.data;
            } else {
                this.servo();
            }
        }
    }
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
