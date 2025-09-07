<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><etim-stock-nav/></div>
            <div class="col-md-8">
              
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Unit</div>

                    <div class="card-body">
                        <Button
                            v-if="isWritePermitted"
                            type="primary"
                            @click="add_unit_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                            Unit
                        </Button>

                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Info</th>
                                    <th scope="col">Code</th>

                                    <th scope="col">Edit</th>
                                    <th>Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in unit_data.data"
                                    :key="i"
                                >
                                    <td>{{ data.id }}</td>
                                    <td>{{ data.name }}</td>
                                    <td>{{ data.description }}</td>
                                    <td>
                                        {{ data.code }}
                                    </td>

                                    <td>
                                        <button
                                            class="btn btn-secondary custom-button"
                                            to="#"
                                            @click="updateDialogue(data)"
                                            v-if="isUpdatePermitted"
                                        >
                                            Edit
                                        </button>
                                    </td>

                                    <td>
                                        <button
                                            @click="deleteUnit(data.id, i)"
                                            class="btn btn-danger custom-button"
                                            v-if="isDeletePermitted"
                                        >
                                            del
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Modal v-model="add_unit_modal" title="Add Unit">
                            <div class="row">
                                <div
                                    v-for="(value, key) in form_data"
                                    :key="key"
                                    class="col-md-9 form-group"
                                >
                                    <label :for="key">{{
                                        key.replace(/_/g, " ").toUpperCase()
                                    }}</label>
                                    <input
                                        v-model="form_data[key]"
                                        type="text"
                                        class="form-control"
                                        :id="key"
                                    />

                                 
                                </div>
                                   <div
                                        class="col-9 d-flex justify-content-center"
                                    >
                                        <button
                                            class="btn btn-secondary"
                                            type="secondary"
                                            v-if="isWritePermitted"
                                            @click="saveUnit()"
                                        >
                                            Save
                                        </button>
                                    </div>
                            </div>
                        </Modal>

                        <Modal v-model="update_unit_modal" title="Update Unit">
                            <div class="row">
                               
                                <div
                                    v-for="(value, key) in update_form_data"
                                    :key="key"
                                    class="col-md-9 form-group"
                                >
                                    <label :for="key">{{
                                        key.replace(/_/g, " ").toUpperCase()
                                    }}</label>
                                    <input
                                        v-model="update_form_data[key]"
                                        type="text"
                                        class="form-control"
                                        :id="key"
                                    />
                                </div>
                                <div
                                    class="col-9 d-flex justify-content-center"
                                >
                                    <button
                                        class="btn btn-secondary"
                                        type="secondary"
                                        v-if="isUpdatePermitted"
                                        @click="updateUnit()"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </Modal>
                    </div>
                </div>
                <center v-else>
                    <b style="color: red; font-size: 1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import StockNav from "../../StockNav.vue";
import EtimStockNav from '../etims_stock/EtimStockNav.vue';
export default {
    components: { StockNav, EtimStockNav },
    data() {
        return {
            update_unit_modal: false,
            add_unit_modal: false,
            isLoading: false,
            update_id:null,
            form_data: {
                name: "",
                description: "",
                code: "",
            },
            update_form_data: {
                name: "",
                description: "",
                code: "",
              
            },
            unit_data: [],
        };
    },
    methods: {
        async deleteUnit(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/unit/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.unit_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async saveUnit() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/unit/create",
                this.form_data
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.add_unit_modal = false;
                this.form_data.name = "";

                this.unit_data = res.data;
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        updateDialogue(data) {
            this.update_unit_modal = true;
            this.update_form_data.code = data.code;
            this.update_form_data.name = data.name;
            this.update_form_data.description = data.description;
            
this.update_id=data.id
        },
        async updateUnit() {
                const obj1 = { id: this.update_id };
            const combinedObject = Object.assign(
                
                obj1,
                this.update_form_data
            );
            this.showLoader();
            const res = await this.callApi(
                "put",
                "data/unit/update",
              combinedObject
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.update_unit_modal = false;
                this.update_form_data.name = "";

                this.unit_data = res.data;
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getUnit() {
            this.showLoader();
            const res = await this.getApi("data/unit/fetch", "");
            this.hideLoader();
            if (res.status == 200) {
                this.unit_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
    mounted() {
        this.getUnit();
    },
};
</script>
