<template>
    <div class="container">
        <div class="row justify-content-center" v-if="isReadPermitted">
            <div class="col-md-2"><receivable-nav /></div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Direct Credit Note</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="col-md-10">
                            <div class="card">
                                <div
                                    class="card-header d-flex justify-content-between align-items-center"
                                >
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input
                                                type="text"
                                                placeholder="Search Customer.."
                                                class="form-control small-input"
                                                v-model="search"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered"
                                        >
                                            <thead>
                                                <tr>
                                                    <th scope="col">Company</th>
                                                    <th>Add Cr</th>
                                                    <th scope="col">
                                                        Co. Contacts
                                                    </th>
                                                    <th scope="col">
                                                        Co. Address
                                                    </th>

                                                    <th scope="col">
                                                        C. Email
                                                    </th>
                                                    <th scope="col">
                                                        Contact Person
                                                    </th>
                                                    <th scope="col">
                                                        Contact Email
                                                    </th>
                                                    <th scope="col">
                                                        Contact Phone
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    class="small-tr"
                                                    v-for="(
                                                        data, i
                                                    ) in customer_data.data"
                                                    :key="i"
                                                >
                                                    <td>
                                                        {{ data.company_name }}
                                                    </td>
                                                    <td>
                                                        <v-btn
                                                            small
                                                            color="primary"
                                                            v-if="
                                                                isWritePermitted
                                                            "
                                                            to="#"
                                                            @click="goto(data)"
                                                            >New Credit Note</v-btn
                                                        >
                                                    </td>
                                                    <td>
                                                        {{ data.company_phone }}
                                                    </td>
                                                    <td>{{ data.address }}</td>

                                                    <td>
                                                        {{ data.company_email }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.contact_person
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{ data.contact_email }}
                                                    </td>
                                                    <td>
                                                        {{ data.contact_phone }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <pagination
                                        v-if="customer_data !== null"
                                        v-bind:results="customer_data"
                                        v-on:get-page="fetchCustomers"
                                    ></pagination>
                                    Items Count {{ customer_data.total }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else><unauthorized /></div>
        <Modal v-model="add_credinote_modal" width="1200px"
            ><add-direct-credit-note
                :customer_details="customer_details"
                v-if="add_credinote_modal"
                @dismiss-diag="dismissDiag"
        /></Modal>
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import ReceivableNav from "../ReceivableNav.vue";
import AddDirectCreditNote from "./AddDirectCreditNote.vue";
export default {
    components: {
        Unauthorized,
        ReceivableNav,
        AddDirectCreditNote,
        Pagination,
    },
    data() {
        return {
            search: "",
            showLoading: true,
            customer_data: [],
            add_credinote_modal: false,
            customer_details: null,
        };
    },
    mounted() {
        this.fetchCustomers(1);
    },
    watch: {
        search: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.showLoading = false;
                this.fetchCustomers(1);
            }, 500),
        },
    },
    methods: {
        dismissDiag() {
            this.add_credinote_modal = false;
        },
        goto(data) {
            this.customer_details = data;
            this.add_credinote_modal = true;
        },
        async fetchCustomers(page) {
            this.showLoading ? this.showLoader() : "";
            const res = await this.getApi("data/customers/fetch", {
                params: {
                    page,
                    search: this.search,
                },
            });
            this.showLoading ? this.hideLoader() : "";

            if (res.status === 200) {
                this.customer_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
