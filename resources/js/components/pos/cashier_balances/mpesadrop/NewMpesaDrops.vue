<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><cashier-balance-nav /></div>
            <div class="col-md-10" >
                <div class="card">
                    <div class="card-header" > <h3><b> Mpesa Drops</b></h3></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="">MPESA Account</label>
                                <treeselect
                                    :load-options="fetchAccounts"
                                    :options="accounts_select_data"
                                    :auto-load-root-options="false"
                                    v-model="form_data.mpesa_account_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select   Account "
                                />
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Select Account Deposited</label>
                                <treeselect
                                    :load-options="fetchAccounts"
                                    :options="accounts_select_data"
                                    :auto-load-root-options="false"
                                    v-model="form_data.account_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select   Account "
                                />
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Mpesa Amount</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.mpesa_amount"
                                />
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="">Details</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.note"
                                />
                            </div>
                            <div class="col-md-6 form-group">
                                <v-btn
                                    color="primary"
                                    :loading="btn_loader"
                                    @click="submitCash()"
                                    >Submit</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
        <div class="row">
            <MpesadropReceipt
                v-if="show_print"
                ref="MpesadropReceipt"
                :form_data="form_data"
                :printData="printData"
                :ref_no="ref_no"
            />
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CashierBalanceNav from "../CashierBalanceNav.vue";
import MpesadropReceipt from "./MpesadropReceipt.vue";

export default {
    components: { CashierBalanceNav, Treeselect, MpesadropReceipt },
    data() {
        return {
            show_print: false,
            btn_loader: false,
            form_data: {
                mpesa_account_id: null,
                account_id: null,
                mpesa_amount: 0,
                note: "",
                user: "",
            },
            ref_no:"",
            printData: {
                cash_drawer_name: "",
                account: "",
            },
        };
    },
    mounted() {
        this.fetchAccounts();
        console.log("Component mounted.");
        const branch = this.$store.state.branch;
        this.form_data.mpesa_account_id = branch.mpesa_account_id;
        this.form_data.user = this.$store.state.user.name;
    },
    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                console.log(JSON.stringify(this.printData));
            }, 500),
        },
    },
    methods: {
        async fetchAccount(id) {
            const res = await this.getApi("data/mpesadrops/getAccountById", {
                params: {
                    id: id,
                },
            });
            if (res.status == 200) {
                return res;
            }
        },
        async submitCash() {
            this.btn_loader = true;
            const res = await this.callApi(
                "POST",
                "data/mpesadrops/create",
                this.form_data
            );
            this.btn_loader = false;
            if (res.status == 200) {
                this.ref_no=res.data.ref_no
                this.successNotif("Submitted successfully");
                const res2 = await this.getAccountAndPrint();
              
            } else {
                this.form_error(res);
            }
        },
        async getAccountAndPrint() {
            this.showLoader();

            const res = await this.getApi("data/mpesadrops/getAccountById", {
                params: {
                    mpesa_account_id: this.form_data.mpesa_account_id,
                    account_id: this.form_data.account_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.printData = res.data;
                console.log(JSON.stringify(this.printData));
                this.show_print = true;

                setTimeout(() => {
                    this.$refs.MpesadropReceipt.printReceipt();
                      this.form_data.account_id = null;
                    this.form_data.mpesa_amount = 0;
                    this.form_data.note = "";
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
