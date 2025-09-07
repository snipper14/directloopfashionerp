<template>
    <div class="">
        <div v-if="isReadPermitted" class="row justify-content-center">
            <div class="col-md-2"><settings-nav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">branch Managment</div>

                    <div class="card-body">
                        <!-- <Button
                            v-if="isWritePermitted"
                            type="primary"
                            @click="add_branch_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                            branch</Button
                        > -->
                        <div class="table table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Business Name</th>
                                        <th scope="col">Branch Name</th>
                                        <th scope="col">Go to</th>
                                        <th>Set Accounts</th>
                                        <th scope="col">Branch Address</th>
                                        <th scope="col">Postal Address</th>
                                        <th>Country</th>
                                        <th>Town</th>
                                        <th>KRA PIN</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th scope="col">Details</th>
                                        <th scope="col">Created At</th>
                                        <th>till no</th>
                                        <th>Logo</th>

                                        <th scope="col">Edit</th>
                                        <th>Del</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in branch_data"
                                        :key="i"
                                    >
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    updateDialogue(data)
                                                "
                                                >{{
                                                    data.business_name
                                                }}</router-link
                                            >
                                        </td>
                                        <td>{{ data.branch }}</td>
                                        <td>
                                            <v-btn
                                                x-small
                                                color="primary"
                                                @click="visitBranch(data)"
                                                >visit branch</v-btn
                                            >
                                        </td>
                                        <td>
                                            <v-btn
                                                x-small
                                                color="success"
                                                @click="setAccount(data)"
                                                >Set Accounts</v-btn
                                            >
                                        </td>
                                        <td>{{ data.address }}</td>
                                        <td>{{ data.postal_address }}</td>
                                        <td>{{ data.country }}</td>
                                        <td>{{ data.town }}</td>
                                        <td>{{ data.kra_pin }}</td>
                                        <td>{{ data.phone_no }}</td>
                                        <td>{{ data.email }}</td>
                                        <td>{{ data.details }}</td>

                                        <td>{{ data.created_at }}</td>
                                        <td>{{ data.till_no }}</td>
                                        <td>
                                            <img
                                                :src="data.img_url"
                                                alt=" image"
                                                style="
                                                    max-width: 100px;
                                                    height: 60px;
                                                "
                                            />
                                        </td>
                                        <td></td>
                                        <td>
                                            <v-btn
                                                x-small
                                                @click="copyBranch(data)"
                                                title="WRITE"
                                                color="info"
                                                v-if="isWritePermitted"
                                            >
                                                Copy
                                            </v-btn>
                                        </td>
                                        <td>
                                            <button
                                                @click="
                                                    deleteBranch(data.id, i)
                                                "
                                                class="btn btn-danger custom-button"
                                                v-if="isDeletePermitted"
                                            >
                                                del
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Modal
                            v-model="add_branch_modal"
                            width="1200"
                            title="Add branch"
                        >
                            <add-branch-settings
                                v-if="add_branch_modal"
                                v-on:dismiss-modal="dismissModal"
                                :formData="form_data"
                            />
                        </Modal>

                        <Modal
                            v-model="update_branch_modal"
                            title="Update branch"
                            width="1200"
                        >
                            <update-branch-settings
                                :update_form_data_details="update_form_data"
                                v-if="update_branch_modal"
                                v-on:dismiss-modal="dismissModal"
                            />
                        </Modal>
                    </div>
                </div>
            </div>
        </div>
        <div v-else><unauthorized /></div>
        <notifications group="foo" />
        <Modal v-model="set_branch_accounts_modal"
           width="1000px" ><set-branch-accounts-modal 
            :update_form_data="update_form_data"
            v-if="set_branch_accounts_modal"
        /></Modal>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Unauthorized from "../utilities/Unauthorized.vue";
import SettingsNav from "./SettingsNav.vue";
import AddBranchSettings from "./AddBranchSettings.vue";
import UpdateBranchSettings from "./UpdateBranchSettings.vue";
import SetBranchAccountsModal from "./SetBranchAccountsModal.vue";
export default {
    data() {
        return {
            update_branch_modal: false,
            add_branch_modal: false,
            isLoading: false,
            update_form_data: null,
            form_data: null,
            branch_data: [],
            set_branch_accounts_modal: false,
        };
    },
    components: {
        Treeselect,
        Unauthorized,
        SettingsNav,
        AddBranchSettings,
        UpdateBranchSettings,
        SetBranchAccountsModal,
    },

    methods: {
        setAccount(data) {
            this.update_form_data = data;
            this.set_branch_accounts_modal = true;
        },
        async visitBranch(data) {
            this.showLoader();
            const res = await this.callApi("post", "data/branch/visitBranch", {
                branch_id: data.id,
                branch: data.branch,
            });
            this.hideLoader();
            if (res.status === 200) {
                this.s("branch swapped successfully ");
                window.location.reload();
            } else {
                this.swr("Server error try again later");
            }
        },

        async deleteBranch(id, i) {
            const shouldDelete = await this.deleteDialogue();
            this.showLoader();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/branch/destroy/" + id,
                    ""
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.s("deleted ");
                    this.branch_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        dismissModal() {
            this.add_branch_modal = false;
            this.update_branch_modal = false;
            this.concurrentApiReq();
        },
        updateDialogue(data) {
            this.form_data = null;
            this.update_form_data = data;
            this.update_form_data.logo = null;
            this.add_branch_modal = false;
            this.update_branch_modal = true;
        },
        async copyBranch(data) {
            this.update_branch_modal = false;
            this.update_form_data = null;
            this.form_data = data;
            this.add_branch_modal = true;
        },

        async fetchbranch() {
            this.showLoader();
            const res = await this.getApi("data/branch/fetch", "");
            this.hideLoader();
            if (res.status == 200) {
                this.branch_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchbranch(),
                this.fetchAccounts(),
            ]);

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
