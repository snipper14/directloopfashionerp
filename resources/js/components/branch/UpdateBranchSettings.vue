<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div
                        class="card-header d-flex align-items-center"
                        :style="dynamicStyle"
                    >
                        <h5 class="ml-2">Update Branch Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Business Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                business_center
                                            </span>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="
                                                update_form_data.business_name
                                            "
                                            placeholder="Business Name"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Branch *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                account_tree
                                            </span>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="update_form_data.branch"
                                            placeholder="Branch Name"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                home
                                            </span>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="update_form_data.address"
                                            placeholder="Address"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Postal Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                mail
                                            </span>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="
                                                update_form_data.postal_address
                                            "
                                            placeholder="Postal Address"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                email
                                            </span>
                                        </span>
                                        <input
                                            type="email"
                                            v-model="update_form_data.email"
                                            placeholder="Email Address"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                               
                                <div class="form-group">
                                    <label for="">State *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                flag
                                            </span>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="update_form_data.country"
                                            placeholder="Country"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Town *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                location_city
                                            </span>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="update_form_data.town"
                                            placeholder="Town"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                    <div class="form-group">
                                        <label for="">Payment Info  </label>
                                        <input
                                            type="text"
                                            v-model="update_form_data.till_no"
                                            placeholder="till no"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Print Options</label>
                                    <Select
                                        v-model="update_form_data.print_type"
                                    >
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
                                    <label for="">Is Discount Allowed</label>
                                    <Select
                                        v-model="update_form_data.is_discount_allowed"
                                    >
                                        <Option
                                            v-for="(value, i) in discount_options"
                                            :key="i"
                                            :value="value.value"
                                        >
                                            {{ value.name }}
                                        </Option>
                                    </Select>
                                </div>
                                 <div class="form-group">
                                    <label for=""> Editing SP OnSelling</label>
                                    <Select v-model="update_form_data.selling_price_type">
                                        <Option value="fixed">
                                            Fixed
                                        </Option>
                                        <Option value="flexible">
                                            Flexible
                                        </Option>
                                    </Select>
                                </div>
                                 <div class="form-group">
                                                <label for=""
                                                    >Loyalty Point Rate Per
                                                    Shilling*</label
                                                >
                                                <input
                                                    type="number"
                                                    v-model="
                                                        update_form_data.loyalty_points_rate
                                                    "
                                                    placeholder="Rate"
                                                    autocomplete="new-user-street-address"
                                                    class="form-control"
                                                />
                                            </div>
                                <div class="form-group">
                                    <label for=""
                                        >Cashier Balance Accounts</label
                                    >
                                    <treeselect
                                        :load-options="fetchAccounts"
                                        :options="accounts_select_data"
                                        :auto-load-root-options="false"
                                        v-model="update_form_data.account_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Account"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Mpesa Accounts</label>
                                    <treeselect
                                        :load-options="fetchAccounts"
                                        :options="accounts_select_data"
                                        :auto-load-root-options="false"
                                        v-model="
                                            update_form_data.mpesa_account_id
                                        "
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Account"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Payroll Accounts</label>
                                    <treeselect
                                        :load-options="fetchAccounts"
                                        :options="accounts_select_data"
                                        :auto-load-root-options="false"
                                        v-model="
                                            update_form_data.payroll_account_id
                                        "
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Account"
                                    />
                                </div>
                                  <div class="form-group">
                                    <label for=""
                                        >POSSale % Profit Margin Limit</label
                                    >
                                    <input
                                        class="form-control"
                                        type="number"
                                        v-model="update_form_data.min_profit_margin"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">PIN *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                pin
                                            </span>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="update_form_data.kra_pin"
                                            placeholder="KRA PIN"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Phone No *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-icons">
                                                phone
                                            </span>
                                        </span>
                                        <input
                                            type="tel"
                                            v-model="update_form_data.phone_no"
                                            placeholder="Phone No"
                                            autocomplete="new-user-street-address"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Invoice Printer *</label>
                                    <input
                                        type="text"
                                        v-model="
                                            update_form_data.invoice_printer
                                        "
                                        placeholder="Invoice Printer"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Etims Api Key</label>
                                    <input
                                        type="text"
                                        v-model="update_form_data.etims_apikey"
                                        placeholder="API Key"
                                        class="form-control"
                                    />
                                </div>
                            </div>

                            <div class="col-md-4">
                                 <div class="form-group">
                                    <label for=""
                                        >Enforce Inventory on POS*</label
                                    >
                                    <input
                                        type="checkbox"
                                        v-model="update_form_data.enforce_pos_qty"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        >Enforce Customer Select POS*</label
                                    >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            update_form_data.enforce_customer_retail
                                        "
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Etims </label>
                                    <Select v-model="update_form_data.etims_type">
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
                                    <label for="">Footer Text</label>
                                    <input
                                        type="text"
                                        v-model="update_form_data.footer_text"
                                        placeholder="Footer Text"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Logo *</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        v-on:change="selectUpdateFile"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Logo Height</label>
                                    <input
                                        class="form-control"
                                        type="text"
                                        v-model="update_form_data.logo_height"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Logo Width</label>
                                    <input
                                        class="form-control"
                                        type="text"
                                        v-model="update_form_data.logo_width"
                                    />
                                </div>
                                 <div class="form-group">
                                    <label for="">Fiscal QrCode Height</label>
                                    <input
                                        class="form-control"
                                        type="text"
                                        v-model="update_form_data.header_height"
                                    />
                                </div>
                                <span class="d-flex flex-row">
                                    <div class="form-group">
                                        <label for="">Strip Color</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            v-model="
                                                update_form_data.strip_color
                                            "
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Print Copies</label>
                                        <input
                                            class="form-control"
                                            type="number"
                                            v-model="update_form_data.print_copies"
                                        />
                                    </div>
                                </span>
                                  <div class="form-group">
                                    <label for="">Cashier Options</label>
                                    <Select v-model="update_form_data.cashier_options">
                                        <Option value="independent">
                                            Independent
                                        </Option>
                                        <Option value="consolidated">
                                            Consolidated
                                        </Option>
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for="">Project Level </label>
                                    <Select
                                        v-model="update_form_data.project_level"
                                    >
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
                                    <Select
                                        v-model="update_form_data.logo_type"
                                    >
                                        <Option value="image"> Logo </Option>
                                        <Option value="name">
                                            Company Name
                                        </Option>
                                    </Select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button
                            class="btn btn-primary"
                            v-if="isUpdatePermitted"
                            @click="updateBranch"
                        >
                            <span class="material-icons">save</span>
                            Update
                        </button>
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
    props: ["update_form_data_details"],
    data() {
        return {
            print_options: [
                { name: "dialogue", value: "dialogue" },
                { name: "server", value: "server" },
                 { name: "offline", value: "offline" },
            ],
             discount_options: [
                { name: "Yes", value: "yes" },
                { name: "No", value: "no" },
            ],
            etims_options: [
                { name: "OSCU", value: "oscu" },
                { name: "VSCU", value: "vscu" },
                { name: "NONE", value: "none" },
            ],
            update_form_data: {
                is_discount_allowed: "",
                header_height: "2px",
                loyalty_points_rate: 0,
                selling_price_type: "",
                etims_type: null,
                logo_height: "200px",
                logo_width: "100px",
                strip_color: "#000000",
                min_profit_margin: 0,
                footer_text: "",
                etims_apikey: "",
                account_id: null,
                mpesa_account_id: null,
                payroll_account_id: null,
                id: null,
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
                logo: "",
                kitchen_printer: "",
                bill_printer: "",
                invoice_printer: "",
                print_type: "",
                enforce_pos_qty: false,
                enforce_customer_retail: false,
                fisc_api: "",
                cu_ip: "",
                cu_port: "",
                cu_password: "",
                cu_serial: "",
                print_copies: 1,
                project_level: "production",
                logo_type: "image",
                cashier_options:""
            },
        };
    },
    mounted() {
        this.update_form_data.logo = null;
     this.update_form_data=this.update_form_data_details
    
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            const res = await Promise.all([this.fetchAccounts()]);
        },
        selectUpdateFile(event) {
            this.update_form_data.logo = event.target.files[0];
        },
        async updateBranch() {
            this.showLoader();
            const data = new FormData();

            data.append("file", this.update_form_data.logo);
            const json = JSON.stringify(this.update_form_data);
            data.append("data", json);
            const res = await this.callApi("post", "data/branch/update", data);
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
    },
    computed: {
        dynamicStyle() {
            return {
                backgroundColor: ` ${this.$store.state.branch.strip_color}`,
            };
        },
    },
};
</script>
<style scoped>
.container {
    margin-top: 20px;
}

.card {
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #ffffff;
}

.card-header {
    background-color: #007bff;
    color: white;
    padding: 10px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control {
    border-left: none;
}

button {
    margin-top: 20px;
}

button .material-icons {
    vertical-align: middle;
    margin-right: 5px;
}
</style>
