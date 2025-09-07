<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Create Branch</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Business Name*</label>
                                    <input
                                        type="text"
                                        v-model="form_data.business_name"
                                        placeholder="Business Name"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Branch *</label>
                                    <input
                                        type="text"
                                        v-model="form_data.branch"
                                        placeholder="Branch Name"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Address *</label>
                                    <input
                                        type="text"
                                        v-model="form_data.address"
                                        placeholder="Address"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Postal Address </label>
                                    <input
                                        type="text"
                                        v-model="form_data.postal_address"
                                        placeholder="Postal Address"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Email Address </label>
                                    <input
                                        type="email"
                                        v-model="form_data.email"
                                        autocomplete="new-user-street-address"
                                        placeholder="Email Address"
                                        class="form-control"
                                    />
                                </div>
                                <span class="d-flex flex-row">
                                    <div class="form-group">
                                        <label for="">State *</label>
                                        <input
                                            type="text"
                                            v-model="form_data.country"
                                            placeholder="Country"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Town *</label>
                                        <input
                                            type="text"
                                            v-model="form_data.town"
                                            placeholder="Town"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </span>
                                <div class="form-group">
                                    <label for="">PIN *</label>
                                    <input
                                        type="text"
                                        v-model="form_data.kra_pin"
                                        placeholder="kra pin"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Phone No *</label>
                                    <input
                                        type="tel"
                                        v-model="form_data.phone_no"
                                        placeholder="Phone no"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Print Options</label>
                                    <Select v-model="form_data.print_type">
                                        <Option
                                            v-for="(value, i) in print_options"
                                            :key="i"
                                            :value="value.value"
                                        >
                                            {{ value.name }}
                                        </Option>
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        >Cashier Balance Accounts</label
                                    >
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
                                <div class="form-group">
                                    <label for="">Mpesa Accounts</label>
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
                                <div class="form-group">
                                    <label for="">Payroll Accounts</label>
                                    <treeselect
                                        :load-options="fetchAccounts"
                                        :options="accounts_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.payroll_account_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select   Account "
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        >POSSale % Profit Margin Limit</label
                                    >
                                    <input
                                        class="form-control"
                                        type="number"
                                        v-model="form_data.min_profit_margin"
                                    />
                                </div>

                                <span class="d-flex flex-row">
                                    <div class="form-group">
                                        <label for="">Payment Info</label>
                                        <input
                                            type="text"
                                            v-model="form_data.till_no"
                                            placeholder="till no"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Invoice Printer*</label>
                                        <input
                                            type="text"
                                            v-model="form_data.invoice_printer"
                                            placeholder="Invoice Printer"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </span>

                                <div class="form-group">
                                    <label for="">Etims ApiKey </label>
                                    <input
                                        type="text"
                                        v-model="form_data.etims_apikey"
                                        placeholder="API KEYS"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Footer Text </label>
                                    <input
                                        type="text"
                                        v-model="form_data.footer_text"
                                        placeholder="Footer Text"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                    />
                                </div>
                                <button
                                    class="btn btn-primary"
                                    @click="saveBranch"
                                >
                                    Save Info
                                </button>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for=""
                                        >Enforce Inventory on POS*</label
                                    >
                                    <input
                                        type="checkbox"
                                        v-model="form_data.enforce_pos_qty"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        >Enforce Customer Select POS*</label
                                    >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            form_data.enforce_customer_retail
                                        "
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Etims </label>
                                    <Select v-model="form_data.etims_type">
                                        <Option
                                            v-for="(value, i) in etims_options"
                                            :key="i"
                                            :value="value.value"
                                        >
                                            {{ value.name }}
                                        </Option>
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for="">Logo *</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        v-on:change="selectFile"
                                    />
                                </div>
                                <span class="d-flex flex-row">
                                    <div class="form-group">
                                        <label for="">Logo Height</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            v-model="form_data.logo_height"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Logo Width</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            v-model="form_data.logo_width"
                                        />
                                    </div>
                                </span>
                                <span class="d-flex flex-row">
                                    <div class="form-group">
                                        <label for="">Strip Color</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            v-model="form_data.strip_color"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Print Copies</label>
                                        <input
                                            class="form-control"
                                            type="number"
                                            v-model="form_data.print_copies"
                                        />
                                    </div>
                                </span>
                                <div class="form-group">
                                    <label for="">Cashier Options</label>
                                    <Select v-model="form_data.cashier_options">
                                        <Option value="independent">
                                            Independent
                                        </Option>
                                        <Option value="consolidated">
                                            Consolidated
                                        </Option>
                                    </Select>
                                </div>
                                 <div class="form-group">
                                    <label for=""> Editing SP OnSelling</label>
                                    <Select v-model="form_data.selling_price_type">
                                        <Option value="fixed">
                                            Fixed
                                        </Option>
                                        <Option value="flexible">
                                            Flexible
                                        </Option>
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for="">Project Level </label>
                                    <Select v-model="form_data.project_level">
                                        <Option value="production">
                                            Production
                                        </Option>
                                        <Option value="development">
                                            development
                                        </Option>
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for="">Logo Type </label>
                                    <Select v-model="form_data.logo_type">
                                        <Option value="image"> Logo </Option>
                                        <Option value="name">
                                            Company Name
                                        </Option>
                                    </Select>
                                </div>
                            </div>
                            <div class="col-md-4" v-if="false">
                                <div class="form-group">
                                    <label for=" ">Fisc API</label>
                                    <input
                                        type="text"
                                        v-model="form_data.fisc_api"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=" ">CU API</label>
                                    <input
                                        type="text"
                                        v-model="form_data.cu_ip"
                                        class="form-control"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for=" ">CU PORT</label>
                                    <input
                                        type="text"
                                        v-model="form_data.cu_port"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=" ">CU PASSWORD</label>
                                    <input
                                        type="text"
                                        v-model="form_data.cu_password"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=" ">CU SERIAL</label>
                                    <input
                                        type="text"
                                        v-model="form_data.cu_serial"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    components: { Treeselect },
    props: ["formData"],
    data() {
        return {
            isLoading: false,
            print_options: [
                { name: "dialogue", value: "dialogue" },
                { name: "server", value: "server" },
                { name: "offline", value: "offline" },
            ],
             
            etims_options: [
                { name: "OSCU", value: "oscu" },
                { name: "VSCU", value: "vscu" },
                { name: "NONE", value: "none" },
            ],

            form_data: {
                selling_price_type:"",
                cashier_options:"",
                etims_type: null,
                logo_height: "",
                logo_width: "",
                strip_color: "#000000",
                min_profit_margin: 0,
                footer_text: "",
                etims_apikey: "",
                business_name: "",
                branch: "",
                branch_code: "",
                address: "",
                postal_address: "",
                country: "",
                town: "",
                kra_pin: "",
                phone_no: "",
                email: "",
                details: "",
                till_no: "",
                logo: null,
                kitchen_printer: "",
                bill_printer: "",
                invoice_printer: "",
                print_type: "",
                enforce_pos_qty: false,
                enforce_customer_retail: false,
                account_id: null,
                mpesa_account_id: null,
                payroll_account_id: null,
                fisc_api: "",
                cu_ip: "",
                cu_port: "",
                cu_password: "",
                cu_serial: "",
                print_copies: 1,
                project_level: "production",
                logo_type: "image",
            },
        };
    },
    mounted() {
       this.form_data=this.formData
     
       
    },
    methods: {
        selectFile(event) {
            this.form_data.logo = event.target.files[0];
        },
        async saveBranch() {
            const data = new FormData();
            data.append("file", this.form_data.logo);
            const json = JSON.stringify(this.form_data);
            data.append("data", json);
            this.showLoader();
            const res = await this.callApi("post", "data/branch/create", data);

            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
