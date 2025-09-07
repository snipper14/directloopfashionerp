<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><settings-nav /></div>
            <div class="col-md-10">
                <v-btn color="primary" small @click="add_modal=true"
                    >Add Card Terminal Account</v-btn
                >
                <div class="card">
                    <div class="card-header">
                        <h3><b>Cashier Card Terminals</b></h3>
                    </div>

                    <div class="card-body">
                         <table class="table table-sm table-dark">
                          <thead> <th>Account</th>
                          <th>Del</th></thead> 
                            <tbody>
                                 <tr v-for="(value, i) in card_account_data" :key="i">
                                <td>{{ value.account.account }}</td>
                                <td>
                                    <v-btn color="danger" x-small @click="deleteData(value.id,i)">Del</v-btn>
                                </td>
                            </tr>
                            </tbody>
                           
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="add_modal">
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="">Cashier Balance Accounts</label>
                    <treeselect
                        :load-options="fetchAccounts"
                        :options="accounts_select_data"
                        :auto-load-root-options="false"
                        v-model="formData.account_id"
                        :multiple="false"
                        :show-count="true"
                        placeholder="Select   Account "
                    />
                </div>
                  <div class="form-group col-md-6">
                <button class="btn btn-primary btn-small mt-3" @click="submitTerminalAccounts">Save</button></div>
            </div>
        </Modal>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import SettingsNav from "./SettingsNav.vue";
export default {
    data() {
        return {
            add_modal: false,
            card_account_data: [],
            accounts_select_data:null,
            formData:{
                account_id:null,

            }
        };
    },
    components: { SettingsNav,Treeselect },
    mounted() {
        this.fetch()
    },
    methods:{
         async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/card_terminal/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.card_account_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async fetch() {
            this.showLoader();
            const res = await this.getApi("data/card_terminal/fetch", {
              
            });
            this.hideLoader();

            if (res.status === 200) {
                this.card_account_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        async submitTerminalAccounts(){
             this.showLoader();
            const res = await this.callApi(
                "post",
                "data/card_terminal/create",
                this.formData
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                Object.keys(this.formData).forEach(
                    (key) => (this.formData[key] = null)
                );
                this.add_modal=false
                  this.fetch()
            } else {
                this.form_error(res)
            }
        }
    }
};
</script>
