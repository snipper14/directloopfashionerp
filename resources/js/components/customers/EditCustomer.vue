<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left> mdi-arrow-left </v-icon>
                Back
            </v-btn>
            <div class="card">
                <div class="card-header">
                    <h4>Edit Details</h4>
                </div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">Customer Details</legend>
                        <form>
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Company Name(Unique) *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="off"
                                            v-model="form_data.company_name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>PIN</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="off"
                                            v-model="form_data.pin"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Credit Limit Amount *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.credit_limit"
                                            placeholder="Company Contacts"
                                        />
                                    </div>
                                    <div class="form-group nopadding">
                                        <label for=""> Select PriceGroup</label>
                                        <treeselect
                                            width="300"
                                            :load-options="fetchPriceGroup"
                                            :options="price_group_select_data"
                                            :auto-load-root-options="false"
                                            v-model="form_data.price_group_id"
                                            :multiple="false"
                                            :show-count="true"
                                            placeholder="Select Price Group "
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Vat Deduction Status *</label>
                                        <select
                                            v-model="form_data.vat_deduction"
                                            class="form-control"
                                        >
                                            <option
                                                v-bind:value="data.name"
                                                v-for="(
                                                    data, i
                                                ) of tax_deduction_select"
                                                :key="i"
                                            >
                                                {{ data.name }}
                                            </option>
                                        </select>
                                    </div>
                                       <div class="form-group">
                                        <label for="status"
                                            >Enroll To Loyalty Program</label
                                        >
                                        <select
                                        title="APPROVE"
                                         :disabled="!isApprovePermitted"
                                            id="status"
                                            v-model="form_data.loyalty_program"
                                            class="form-control"
                                        >
                                            <option value="active">
                                                Active
                                            </option>
                                            <option value="inactive">
                                                Inactive
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="status"
                                            >Activate Discount</label
                                        >
                                        <select
                                         title="ADMIN"
                                         :disabled="!isAdmin"
                                            id="status"
                                            v-model="
                                                form_data.is_discount_qualified
                                            "
                                            class="form-control"
                                        >
                                            <option value="active">
                                                Active
                                            </option>
                                            <option value="inactive">
                                                Inactive
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Account(Unique) *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.acc_code"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label>Company Contacts *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="off"
                                            v-model="form_data.company_phone"
                                            placeholder="Company Contacts"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Company Email *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="off"
                                            v-model="form_data.company_email"
                                            placeholder="Company Email"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Company Postal Address </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="off"
                                            v-model="form_data.postal_address"
                                            placeholder="Company Postal Address"
                                        />
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Physical Address *</label>
                                        <textarea
                                            class="form-control"
                                            v-model="form_data.address"
                                            placeholder="Address"
                                        >
                                        </textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Country </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="off"
                                            v-model="form_data.country"
                                            placeholder="Country"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>City </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="off"
                                            v-model="form_data.city"
                                            placeholder="City"
                                        />
                                    </div>
                                </div>

                                <div class="col-3">
                                    <fieldset class="border">
                                        <legend class="text-center">
                                            Bank Details
                                        </legend>

                                        <div class="form-group">
                                            <label>Bank Name </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                autocomplete="off"
                                                v-model="form_data.bank_name"
                                                placeholder="Bank Name"
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label>Bank Branch </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                autocomplete="off"
                                                v-model="form_data.bank_branch"
                                                placeholder="Bank Branch"
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label>Account Name </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                autocomplete="off"
                                                v-model="form_data.account_name"
                                                placeholder="Account Name"
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label>Account No </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                autocomplete="off"
                                                v-model="form_data.account_no"
                                                placeholder="Acc. No"
                                            />
                                        </div>
                                    </fieldset>
                                </div>

                                <div class="col-3">
                                    <fieldset class="border">
                                        <legend class="text-center">
                                            Contact Person Details
                                        </legend>
                                        <div class="form-group">
                                            <label>Fullname Name * </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                autocomplete="off"
                                                v-model="
                                                    form_data.contact_person
                                                "
                                                placeholder="Full Name"
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label>Email * </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                autocomplete="off"
                                                v-model="
                                                    form_data.contact_email
                                                "
                                                placeholder="Email"
                                            />
                                        </div>

                                        <div class="form-group">
                                            <label>Phone * </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                autocomplete="off"
                                                v-model="
                                                    form_data.contact_phone
                                                "
                                                placeholder="Phone"
                                            />
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <button
                                type="button"
                                v-if="isUpdatePermitted"
                                class="btn btn-primary"
                                @click="updateCustomer()"
                            >
                                Save
                            </button>
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    props: ["data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            select_data: {
                employee_data: "",
                dept_data: "",
                employee_type_data: "",
            },
            tax_deduction_select: [{ name: "vatable" }, { name: "exempted" }],
            form_data: {
                credit_limit: 0,
                acc_code: "",
                company_name: "",
                company_phone: "",
                address: "",
                postal_address: "",
                city: "",
                country: "",
                company_email: "",
                bank_name: "",
                bank_branch: "",
                account_name: "",
                account_no: "",
                contact_person: "",
                contact_email: "",
                contact_phone: "",
                pin: "",
                vat_deduction: null,
                  loyalty_program: "", //['active','inactive']
                is_discount_qualified: "", // ['active', 'inactive']
            },
        };
    },
    components: { Treeselect },
    mounted() {
        this.form_data = this.data;
    },
    methods: {
        async updateCustomer() {
            this.showLoader();
            const res = await this.callApi(
                "put",
                "data/customers/update",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active");
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
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
</style>
