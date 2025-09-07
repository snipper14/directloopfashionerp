<template>
    <div class="container">
        <div class="card ">
            <div class=" d-flex justify-content-between mr-2">
                <div>
                    <h5>
                        Door
                        <span class="badge badge-success">{{
                            model_data.door_name
                        }}</span>
                    </h5>
                </div>

                <div>
                    <h5>
                        Floor
                        <span class="badge badge-success">{{
                            model_data.floor_no
                        }}</span>
                    </h5>
                </div>
                <div>
                    <h5>
                        Bed
                        <span class="badge badge-success">{{
                            model_data.qty_bed
                        }}</span>
                    </h5>
                </div>
                <div>
                    <h5>
                        Standard
                        <span class="badge badge-success">{{
                            model_data.room_standard.name
                        }}</span>
                    </h5>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label for="">Item</label>
                            <input
                                type="text"
                                v-model="form_data.item"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label for="">Cost</label>
                            <input
                                type="number"
                                v-model="form_data.price"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label for="">Qty</label>
                            <input
                                type="number"
                                v-model="form_data.qty"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label for="">Total</label>
                            <input
                                type="number"
                                disabled
                                v-model="form_data.total"
                                class="form-control"
                            />
                        </div>

                        <div class="form-group">
                            <label for="">Details</label>
                            <input
                                type="text"
                                v-model="form_data.details"
                                class="form-control"
                            />
                        </div>

                        <div class="form-group">
                            <button
                                class="btn btn-primary btn-sm"
                                @click="saveRecords()"
                                v-if="isWritePermitted"
                            >
                                Save Details
                            </button>
                        </div>
                    </div>
                    <div class="col-8">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Qty</th>

                                    <th scope="col">price</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(value, i) in records.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ value.item }}
                                    </td>
                                    <td>
                                        {{ value.qty }}
                                    </td>
                                    <td>{{ cashFormatter(value.price) }}</td>
                                    <td>{{ cashFormatter(value.total) }}</td>
                                    <td>{{ formatDateTime(value.created_at) }}</td>
                                    <td>{{ value.details }}</td>
                                    <td><button v-if="isDeletePermitted" class="btn btn-danger btn-sm" @click="deleteData(value.id,i)">Delete</button></td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                      <b> Total</b>
                                    </td>
                                        <td colspan="3">
                                     <b> {{cashFormatter(sum_total)}}</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="records !== null"
                            v-bind:results="records"
                            v-on:get-page="fetchRRecords"
                        ></Pagination>
                        Items Count {{ records.total }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Pagination from "../../utilities/Pagination.vue";
export default {
    props: ["model_data"],
    data() {
        return {
            records: [],
            sum_total:0,
            form_data: {
                item: "",
                room_id: null,
                qty: 0,
                price: 0,
                total: 0,
                details: ""
            }
        };
    },
    watch: {
        form_data: {
            handler: _.debounce(function() {
                this.form_data.total =
                    this.form_data.qty * this.form_data.price;
            }),
            deep: true
        },
        records:{
            handler:_.debounce(function(){
  this.sum_total = this.records.data.reduce(function(sum, val) {
                    return sum + val.total;
                }, 0);
            }),
            deep:true
        }
      
    },
    components: {
        Pagination
    },
    mounted() {
        this.form_data.room_id = this.model_data.id;
       
        this.fetchRRecords(1);
    },
    methods: {
        async deleteData(id,i){
            const confirm=await this.deleteDialogue()
             if(confirm)
             {this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_maintenance/destroy"+id,
                ""
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Deleted");
                this.records = res.data;
                   this.records.data.splice(i, 1);
            } else {
                this.form_error(res);
            }
             }
        },
        async saveRecords() {
              this.form_data.room_id = this.model_data.id;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_maintenance/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Added");
                this.records = res.data;
                   Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
            } else {
                this.form_error(res);
            }
        },
        async fetchRRecords(page) {
            this.showLoader();
            const res = await this.getApi("data/room_maintenance/fetch", {
                params: {
                    page,
                    room_id: this.model_data.id
                }
            });
            this.hideLoader();
            if (res.status === 200) {
                this.records = res.data;
            }
        }
    }
};
</script>
