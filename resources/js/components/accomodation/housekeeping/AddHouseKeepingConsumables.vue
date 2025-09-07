<template>
    <div class="container">
        <div class="col-10">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
            <div class="card">
                <div class="card-header">
                    <b>House Consumables: </b>ROOM: {{ room_details.door_name }}
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="border p-2">
                                <b>Select House Keeper</b>
                                <div class="form-group mt-2">
                                    <label for="">Dept</label>
                                    <Select
                                        v-model="form_data.department_id"
                                        @on-change="changeDepartment"
                                    >
                                        <Option
                                            v-for="(value,
                                            i) in department_data"
                                            :key="i"
                                            :value="value.id"
                                            >{{ value.department }}</Option
                                        >
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for=""> House Keeper</label>

                                    <treeselect
                                        :load-options="fetchHseKeeper"
                                        :options="hsekeeper_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.house_keeper_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select "
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="">Extra Details</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.details"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <product-search @stockSearchResult="addCunsumable" />
                    </div>
                    <div class="row">
                        Utilities
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <th>Code</th>
                                <th>
                                    Item
                                </th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in consumable_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock.code }}
                                    </td>
                                    <td>
                                        {{ data.stock.product_name }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.stock.cost_price)
                                        }}
                                    </td>
                                    <td>
                                        {{ data.qty }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(row_total(data)) }}
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            class="danger"
                                            @click.native="
                                                deleteRecord(data.id, i)
                                            "
                                            >Delete</router-link
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <b>TOTAL</b>
                                    </td>
                                    <td>
                                        <b> {{ cashFormatter(total_spent) }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td>
                                        <button
                                            @click="clearRoom()"
                                            class="btn btn-secondary"
                                        >
                                            Clear Room
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
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ProductSearch from "../../utilities/ProductSearch.vue";
export default {
    props: ["room_details"],
    data() {
        return {
            hsekeeper_select_data: null,
            department_data: [],
            isLoading: false,
            consumable_data: [],
            total_spent: 0,
            form_data: {
                room_id: null,
                department_id: null,
                house_keeper_id: null,
                room_reservation_id: null,
                stock_id: null,
                unit_id: null,
                qty: 0,
                price: 0,
                total: 0,
                details: ""
            }
        };
    },
    components: {
        Treeselect,
        ProductSearch
    },
    computed: {},
    watch: {
        consumable_data: {
            deep: true,
            handler() {
                this.total_spent = this.consumable_data.reduce(function(
                    sum,
                    val
                ) {
                    return sum + val.qty * val.stock.cost_price;
                },
                0);
            }
        }
    },
    mounted() {
        this.form_data.room_id = this.room_details.id;
        this.concurrentApiReq();
    },
    methods: {
        async deleteRecord(id, i) {
            const isDeletable = await this.deleteDialogue();
            if (isDeletable) {
                this.showLoader();
                const res = await this.callApi(
                    "DELETE",
                    "data/house_keeping/destroy/" + id
                );
                this.hideLoader()
                res.status == 200
                    ? this.consumable_data.splice(i, 1)
                    : this.servo();
            }
        },
        async clearRoom() {
            this.showLoader();
            var room_reservation_id =
                this.consumable_data.length > 0
                    ? this.consumable_data[0].room_reservation_id
                    : "";
            const res = await this.callApi(
                "POST",
                "data/house_keeping/completeHouseKeeping",
                {
                    room_reservation_id: room_reservation_id,
                    room_id: this.form_data.room_id
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Successfully added");
                this.$emit("dashboard-active");
            } else {
                this.servo();
            }
        },
        row_total(data) {
            return data.qty * data.stock.cost_price;
        },
        async addCunsumable(value) {
            this.form_data.stock_id = value.id;
            this.form_data.price = value.cost_price;
            this.form_data.qty = value.qty;
            this.form_data.total = value.qty * value.cost_price;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/house_keeping/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Successfully added");
                this.consumable_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
                this.fetchDept(),
                this.fetchPendingHseKeeping()
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchPendingHseKeeping() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/house_keeping/pendingHseConsumableDetails",
                {
                    params: {
                        room_id: this.form_data.room_id
                    }
                }
            );
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.consumable_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchDept() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/dept/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.department_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async changeDepartment() {
            this.isLoading = true;
            this.fetchHseKeeper();
        },
        async fetchHseKeeper() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/employee/fetch", {
                params: { department_id: this.form_data.department_id }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.hsekeeper_select_data = this.modifyHousePeer(
                    res.data.results
                );
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyHousePeer(data) {
            let modif = data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.first_name + " " + obj.last_name
                };
            });
            return modif;
        }
    }
};
</script>
