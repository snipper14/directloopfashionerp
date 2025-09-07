<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                  <GuestManagerNav/>
            </div>
            <div class="col-md-10">
                <button
                    class="btn btn-primary btn-sm center"
                    @click="modal_guest_co = true"
                >
                    <Icon type="md-add" />Add Company
                </button>
                <div class="card">
                    <div class="card-header row">
                        <div class="col-4">
                            <h4><b>Visitors Companies</b></h4>
                        </div>
                        
                    </div>

                    <div class="card-body">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                           
                                            <th>Company</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Address</th>
                                            <th scope="col">Email</th>
                                           <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in guest_co_data"
                                            :key="i"
                                        >
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        editGuestCOModal(data)
                                                    "
                                                >
                                                    {{ data.name }}</router-link
                                                >
                                            </td>
                                            <td>{{ data.contacts }}</td>
                                            <td>
                                                {{ data.address }}
                                            </td>
                                            <td>
                                                {{ data.email }}
                                            </td>

                                            <td>
                                                {{ formatDateTime(data.created_at) }}
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                   
                                                    @click.native="
                                                        deleteRecord(data.id, i)
                                                    "
                                                    class="danger "
                                                >
                                                    Delete
                                                </router-link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <Pagination
                                v-if="guest_co_data !== null"
                                v-bind:results="guest_co_data"
                                v-on:get-page="fetchGuestsCo"
                            ></Pagination>
                            Items Count {{ guest_co_data.total }}

                            <div class="row">
                                <div
                                    class="col-4 d-flex"
                                    v-if="isDownloadPermitted"
                                >
                                    <export-excel
                                        class="btn btn-default btn-export ml-2 "
                                        :fetch="exportExcel"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                        <button class="btn btn-primary btn-sm">
                                            Export Excel
                                        </button>
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2 "
                                        :fetch="exportExcel"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="filename.xls"
                                    >
                                        <button class=" btn btn-primary btn-sm">
                                            Export CSV
                                        </button>
                                    </export-excel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="modal_guest_co" width="860">
            <CreateCOGuest v-on:onAddCompanySuccess="onAddCompanySuccess" />
        </Modal>
        <Modal v-model="modal_update_guest_co" width="860">
            <EditGuestCO
                v-if="edit_guest_co_data"
                :edit_guest_co_data="edit_guest_co_data"
                v-on:onAddCompanySuccess="onAddCompanySuccess"
            />
        </Modal>
    </div>
</template>

<script>
import CreateCOGuest from "./CreateCOGuest.vue";
import EditGuestCO from "./EditGuestCO.vue";
import Pagination from "../../utilities/Pagination.vue";
import GuestManagerNav from './GuestManagerNav.vue'
export default {
    data() {
        return {
            modal_guest_co: false,
            modal_update_guest_co: false,
            edit_guest_co_data: null,
            guest_co_data: [],
            search: ""
        };
    },
    components: {
        CreateCOGuest,
        Pagination,
        EditGuestCO,
        GuestManagerNav
    },
    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.fetchGuestsCo(1);
            })
        }
    },

    mounted() {
        this.fetchGuestsCo(1);
    },
    methods: {
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi("data/guest/fetch", {
                params: {
                    search: this.search
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                return res.data;
            } else {
                this.servo();
            }
        },
        editGuestCOModal(data) {
            this.edit_guest_co_data = data;
            this.modal_update_guest_co = true;
        },
        async deleteRecord(id, i) {
            const del = await this.deleteDialogue();
            if (del) {
                const res = await this.callApi(
                    "delete",
                    "data/guest_co/destroy/" + id
                );
                if (res.status == 200) {
                    this.successNotif("Successfully deleted");
                    this.guest_co_data.splice(i, 1);
                } else {
                    this.servo();
                }
            }
        },
        onAddCompanySuccess() {
            this.modal_guest_co = false;
            this.modal_update_guest_co = false;
            this.isLoading = true;
            this.fetchGuestsCo(1);
        },
        async fetchGuestsCo(page) {
            this.showLoader();
            const res = await this.getApi("data/guest_co/fetch", {
                params: {
                    page: page,
                    search: this.search
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                this.guest_co_data = res.data;
            } else {
                this.servo();
            }
        }
    }
};
</script>
