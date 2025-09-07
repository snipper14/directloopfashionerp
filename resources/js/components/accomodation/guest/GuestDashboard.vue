<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                  <GuestManagerNav/>
            </div>
            <div class="col-md-10">
                <button
                    class="btn btn-primary btn-sm center"
                    @click="modal_guest = true"
                >
                    <Icon type="md-add" />Add Guest
                </button>
                <div class="card">
                    <div class="card-header row">
                        <div class="col-4">
                            <h4><b>Guest Manager</b></h4>
                        </div>
                        <div class="col-6">
                            <input
                                placeholder="search"
                                type="text"
                                class="form-control"
                                v-model="search"
                            />
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
                                            <th scope="col">Name</th>
                                            <th>Company</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">ID NO</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Gender</th>
                                            <th scope="col">Age</th>
                                            <th scope="col">Members</th>
                                            <th scope="col">Postal Addr</th>
                                            <th scope="col">Country Origin</th>
                                            <th>BirthDate</th>
                                            <th scope="col">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in guest_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        editGuestModal(data)
                                                    "
                                                >
                                                    {{ data.name }}</router-link
                                                >
                                            </td>
                                            <td>{{ data.guest_company.name }}</td>
                                            <td>
                                                {{ data.phone }}
                                            </td>
                                            <td>
                                                {{ data.id_no }}
                                            </td>

                                            <td>
                                                {{ data.email }}
                                            </td>
                                            <td>
                                                {{ data.gender }}
                                            </td>

                                            <td>
                                                {{ data.age }}
                                            </td>

                                            <td>{{ data.members }}</td>
                                            <td>{{ data.postal_address }}</td>
                                            <td>
                                                {{ data.country_origin }}
                                            </td>
                                         
                                            <td>
                                                {{ data.birth_date }}
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    v-if="isDeletePermitted"
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
                                v-if="guest_data !== null"
                                v-bind:results="guest_data"
                                v-on:get-page="fetchGuests"
                            ></Pagination>
                            Items Count {{ guest_data.total }}

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
        <Modal v-model="modal_guest" width="860">
            <create-guest v-on:onSuccess="onSuccess" />
        </Modal>
        <Modal v-model="modal_update_guest" width="860">
            <edit-guest
                v-if="edit_guest_data"
                :edit_guest_data="edit_guest_data"
                v-on:onSuccess="onSuccess"
            />
        </Modal>
    </div>
</template>

<script>
import CreateGuest from "./CreateGuest.vue";
import EditGuest from "./EditGuest.vue";
import Pagination from "../../utilities/Pagination.vue";
import GuestManagerNav from './GuestManagerNav.vue'
export default {
    data() {
        return {
            modal_guest: false,
            modal_update_guest: false,
            edit_guest_data: null,
            guest_data: [],
            search: ""
        };
    },
    components: {
        CreateGuest,
        Pagination,
        EditGuest,
        GuestManagerNav
    },
    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.fetchGuests(1);
            })
        }
    },

    mounted() {
        this.fetchGuests(1);
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
        editGuestModal(data) {
            this.edit_guest_data = data;
            this.modal_update_guest = true;
        },
        async deleteRecord(id, i) {
            const del = await this.deleteDialogue();
            if (del) {
                const res = await this.callApi(
                    "delete",
                    "data/guest/destroy/" + id
                );
                if (res.status == 200) {
                    this.successNotif("Successfully deleted");
                    this.guest_data.data.splice(i, 1);
                } else {
                    this.servo();
                }
            }
        },
        onSuccess() {
            this.modal_guest = false;
            this.modal_update_guest = false;
            this.isLoading = true;
            this.fetchGuests(1);
        },
        async fetchGuests(page) {
            this.showLoader();
            const res = await this.getApi("data/guest/fetch", {
                params: {
                    page: page,
                    search: this.search
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                this.guest_data = res.data;
            } else {
                this.servo();
            }
        }
    }
};
</script>
