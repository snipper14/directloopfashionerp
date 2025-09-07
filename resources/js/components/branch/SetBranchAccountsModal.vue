<template>
    <v-app class="">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card">
                    <div class="card-header">{{ update_form_data.branch }}</div>

                    <div class="card-body">
                        <div class="row">
                        <div class="form-group col-md-6">
                            <label for="">Cashier Balance Accounts</label>
                            <treeselect
                                :load-options="fetchBranchAccounts"
                                :options="accounts_select_data"
                                :auto-load-root-options="false"
                                v-model="form_data.account_id"
                                :multiple="false"
                                :show-count="true"
                                placeholder="Select   Account "
                            />
                        </div>
                        <div class="col-md-3"><v-btn color="primary" small @click="saveRecords">Save</v-btn></div>
                        </div>
                        <div class="row">
                            <table class="table table-sm table-dark">
                                <thead>
                                    <th>Branch</th>
                                    <th>Account</th>
                                    <th>User</th>
                                    <th>Del</th>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(value, i) in account_data"
                                        :key="i"
                                    >
                                    <td>{{value.branch.branch}}</td>
                                        <td>{{ value.account.account }}</td>
                                        <td>{{value.user.name}}</td>
                                        <td>
                                            <v-btn
                                            v-if="isDeletePermitted"
                                                color="danger"
                                                x-small
                                                @click="deleteData(value.id, i)"
                                                >Del</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
export default {
    props: ["update_form_data"],
    data() {
        return {
            form_data: {
                account_id: null,
                branch_id: this.update_form_data.id,
            },
            account_data:[],
        };
    },
    mounted() {
        console.log(JSON.stringify(this.update_form_data));
this.fetch()
        console.log("Component mounted.");
    },
    methods:{
        async fetch() {
            this.showLoader();
            const res = await this.getApi("data/branch_accounts/fetch", {
              params:{
                ...this.form_data
              }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.account_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
         async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/branch_accounts/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.account_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async saveRecords(){
             this.showLoader();
            const res = await this.callApi(
                "post",
                "data/branch_accounts/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
              this.form_data.account_id=null
              
                  this.fetch()
            } else {
                this.form_error(res)
            }
        }
    }
};
</script>
