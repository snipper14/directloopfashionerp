<template>
    <div class="row justify-content-center">
        <div class="row col-md-12 mt-2">
            <div class="col-md-4">
                <h3><b>Point Of Sale</b></h3>
            </div>
            <div class="col-md-6">
                <SearchCustomer
                    v-on:searchCustomerResults="searchCustomerResults"
                />
            </div>
            <div class="col-md-2">
                <v-btn color="primary" @click="$router.push('customers')" small
                    >+Add Customer</v-btn
                >
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div
                            v-if="cashier_details"
                            class="col-md-6 d-flex flex-row"
                        >
                            <b>Cashier: {{ cashier_details.user.name }} </b>
                            <b> / Status:{{ cashier_details.status }}</b>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 row">
                            <div class="form-group col-md-2">
                                <label>Scan Code... </label>
                                <input
                                    @input="clearOtherFields('code')"
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.code"
                                    autocomplete="new-user-street-address"
                                    autofocus
                                />
                            </div>
                            <div class="form-group col-md-2">
                                <label>Product Name [shift] </label>
                                <input
                                    @input="clearOtherFields('product_name')"
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.product_name"
                                    autocomplete="new-user-street-address"
                                    placeholder="Product Name"
                                />
                            </div>
                            <div class="form-group col-md-2">
                                <label>Search Code. </label>
                                <input
                                    @input="clearOtherFields('search_code')"
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.search_code"
                                    autocomplete="new-user-street-address"
                                    placeholder="code"
                                />
                            </div>
                            <div class="form-group col-md-2">
                                <label>Search Desc.. </label>
                                <input
                                    @input="
                                        clearOtherFields('search_description')
                                    "
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.search_description"
                                    autocomplete="new-user-street-address"
                                    placeholder="Desc."
                                />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">(press ctrl)</label><br />
                                <v-btn
                                    v-shortkey.once="['ctrl']"
                                    color="primary"
                                    @shortkey="clearSearch()"
                                    @click="clearSearch()"
                                >
                                    Clear Search
                                </v-btn>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table modern-table"
                                >
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Batchno</th>

                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">B.Price</th>
                                            <!-- <th>Disc(%)</th> -->
                                            <th>Margin%</th>
                                            <th scope="col">Sell Price</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Sub Total</th>
                                            <th>Discount(%)</th>
                                            <th>Shelf</th>
                                            <th>%Markup</th>
                                            <th>Description</th>
                                            <th>Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in search_results"
                                            :key="i"
                                        >
                                            <td>
                                                <v-btn
                                                    title="WRITE"
                                                    v-if="isWritePermitted"
                                                    color="primary"
                                                    small
                                                    @click="submitRecords(data)"
                                                >
                                                    Add Item
                                                </v-btn>
                                            </td>
                                            <td>
                                                <input
                                                    class="form-control"
                                                    style="
                                                        width: 80px;
                                                        font-size: 12px;
                                                        font-style: bold;
                                                    "
                                                    type="text"
                                                    v-model="data.batch_no"
                                                />
                                            </td>
                                            <td>
                                                {{ data.code }}
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        checkInventory(data)
                                                    "
                                                    >{{
                                                        data.product_name
                                                    }}</router-link
                                                >
                                            </td>

                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        checkAveragePrice(data)
                                                    "
                                                >
                                                    {{
                                                        cashFormatter(
                                                            data.purchase_price
                                                        )
                                                    }}</router-link
                                                >
                                            </td>

                                            <td>
                                                {{
                                                    calculateProfitPercentage(
                                                        data.selling_price,
                                                        data.purchase_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <input
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    :disabled="
                                                        b_setting.selling_price_type ===
                                                        'fixed'
                                                    "
                                                    class="form-control table-input"
                                                    type="number"
                                                    @input="
                                                        updateSubTotalEvent(i)
                                                    "
                                                    v-model="data.selling_price"
                                                />
                                            </td>

                                            <td>
                                                <input
                                                    @input="
                                                        updateSubTotalEvent(i)
                                                    "
                                                    class="form-control table-input"
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    type="number"
                                                    v-model="data.qty"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    @input="updateQtyEvent(i)"
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    :disabled="
                                                        b_setting.selling_price_type ===
                                                        'fixed'
                                                    "
                                                    class="form-control table-input"
                                                    type="number"
                                                    v-model="data.sub_total"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    disabled
                                                    class="form-control table-input"
                                                    type="number"
                                                    @input="updateDiscount(i)"
                                                    v-model="data.discount"
                                                />
                                            </td>
                                            <td>{{ data.shelve }}</td>

                                            <td>
                                                <input
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    :disabled="
                                                        b_setting.selling_price_type ===
                                                        'fixed'
                                                    "
                                                    class="form-control table-input"
                                                    type="number"
                                                    v-model="
                                                        data.profit_markup_calculator
                                                    "
                                                    @input="
                                                        updateSellingPrice(i)
                                                    "
                                                />
                                            </td>
                                            <td>{{ data.description }}</td>
                                            <td>{{ data.unit }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="add_customer" width="600px">
            <AddCustomer
                v-if="add_customer"
                v-on:addCustomerComplete="addCustomerComplete"
            />
        </Modal>
        <Modal v-model="check_inventory_modal">
            <check-inventory-modal
                v-if="check_inventory_modal"
                :inventory_data="inventory_data"
        /></Modal>
        <Modal v-model="average_price_modal">
            <check-average-purchase-price-modal
                v-if="average_price_modal"
                :average_price_data="average_price_data"
        /></Modal>

        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiBackburger,
    mdiNotebookEdit,
    mdiArrowRightBold,
    mdiPrinter,
    mdiArrowSplitVertical,
    mdiBookmarkOutline,
    mdiTrashCanOutline,
    mdiPlusThick,
    mdiMinusThick,
    mdiHome,
    mdiCashPlus,
    mdiAccountPlus,
    mdiArrowDownCircleOutline,
    mdiArrowUpCircleOutline,
} from "@mdi/js";
import _ from "lodash";
import SearchCustomer from "./SearchCustomer.vue";
import AddCustomer from "./AddCustomer.vue";
import { debounce } from "lodash";
import CheckInventoryModal from "./CheckInventoryModal.vue";
import CheckAveragePurchasePriceModal from "./CheckAveragePurchasePriceModal.vue";
export default {
    components: {
        SearchCustomer,
        AddCustomer,
        CheckInventoryModal,
        CheckAveragePurchasePriceModal,
    },

    data() {
        return {
            b_setting: this.$store.state.branch,
            cashier_options: this.$store.state.branch.cashier_options,
            cashier_details: null,
            check_inventory_modal: false,
            average_price_modal: false,
            add_customer: false,
            search_results: [],
            customer_id: "98",
            customer_name: "",
            customer_pin: "",
            inventory_data: [],
            average_price_data: null,
            search_query: {
                code: "",
                product_name: "",
                search_code: "",
                search_description: "",
            },
            suppressNextSearch: false,
            batch_no: "",
            icons: {
                mdiBackburger,
                mdiNotebookEdit,
                mdiArrowRightBold,
                mdiPrinter,
                mdiArrowSplitVertical,
                mdiBookmarkOutline,
                mdiTrashCanOutline,
                mdiPlusThick,
                mdiMinusThick,
                mdiHome,
                mdiCashPlus,
                mdiAccountPlus,
                mdiArrowDownCircleOutline,
                mdiArrowUpCircleOutline,
            },
        };
    },

    mounted() {
        window.addEventListener("keydown", this.handleKeyDown);
        if (this.cashier_options == "consolidated") {
            this.fetchCashierOnShift();
        }
    },
    beforeDestroy() {
        window.removeEventListener("keydown", this.handleKeyDown);
    },
    watch: {
        "search_query.code": {
            handler: _.debounce(function (val) {
                if (this.suppressNextSearch) {
                    // 👈
                    this.suppressNextSearch = false; // reset once
                    return; // don't parse/trigger search
                }
                const raw = (val || "").trim();
                if (!raw) {
                    this.batch_no = "";
                    this.searchProduct();
                    return;
                }

                const i = raw.indexOf("-");
                if (i > -1) {
                    const code = raw.slice(0, i).trim();
                    const batch = raw.slice(i + 1).trim(); // supports more hyphens after the first
                    // Only write back if changed, to avoid needless watcher loops
                    if (this.search_query.code !== code)
                        this.search_query.code = code;
                    this.batch_no = batch || "";
                    this.searchProduct();
                    console.log("Batch no set to:", this.batch_no);
                } else {
                  //  this.errorNotif("No batch no found in scanned code");
                  //  this.batch_no = "";
                }
                console.log("Batch no set to before watch:", this.batch_no);
                 // now you can use this.search_query.code and this.batch_no
            }, 500),
        },
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                if (this.search_query.code) return;
                if (this.search_query.code !== "") {
                    this.search_query.code = this.search_query.code;
                }
                this.searchProduct();
            }, 500),
        },
    },

    methods: {
        clearOtherFields(activeField) {
            for (const field in this.search_query) {
                if (
                    field !== activeField &&
                    this.search_query[activeField] !== ""
                ) {
                    this.search_query[field] = ""; // Clear other fields **only if input is not empty**
                }
            }
        },
        clearSearch() {
            this.search_results = [];

            Object.keys(this.search_query).forEach((key) => {
                this.search_query[key] = "";
            });
        },
        async fetchCashierOnShift() {
            const res = await this.getApi(
                "data/register/fetchCashierOnShift",
                {}
            );
            if (res.status == 200) {
                if (res.data.status) {
                    this.cashier_details = res.data;
                }
            } else {
                this.form_error(res);
            }
        },
        updateSellingPrice(index) {
            const item = this.search_results[index];
            const markupPercentage = item.profit_markup_calculator || 0;

            // Calculate selling price: selling_price = purchase_price * (1 + (markupPercentage / 100))
            if (markupPercentage >= 0) {
                item.selling_price = Math.ceil(
                    item.purchase_price * (1 + markupPercentage / 100)
                );
                this.updateSubTotalEvent(index); // Update the subtotal as well
            }
        },

        // Method to update subtotal when selling price or quantity changes
        updateSubTotalEvent(index) {
            console.log("Updating subtotal for index:", index);

            const item = this.search_results[index];
            console.log(JSON.stringify(item));

            const qty = Number(item.qty) || 0;
            const price = Number(item.selling_price) || 0;
            const discount = Number(item.discount) || 0; // percentage %

            const gross = qty * price;
            const net = gross - (gross * discount) / 100;

            item.sub_total = Math.ceil(net); // round as you prefer
        },
        handleKeyDown(event) {
            if (event.key === "Shift") {
                this.$refs.inputField.focus();
            }
        },
        updateQtyEvent(index) {
            const item = this.search_results[index];

            item.selling_price = item.sub_total / item.qty;
        },
        updateDiscount(index) {
            const item = this.search_results[index];
            var selling_p = item.selling_price;

            selling_p = Math.ceil(
                selling_p - selling_p * (item.discount / 100)
            );

            item.sub_total = item.qty * item.selling_price;
        },

        handleSearch() {
            this.debouncedSearch();
        },
        addCustomerComplete() {
            this.add_customer = false;
        },

        async searchCustomerResults(value) {
            this.customer_id = value.value;
            const res = await this.getApi("data/customers/getCustomerBalance", {
                params: { customer_id: this.customer_id },
            });

            // 🟢 Define the clear function BEFORE registering it
            const clearToastsOnClick = () => {
                this.$toasted.clear();
                // Remove listeners after the first trigger
                ["click", "keydown", "mousemove"].forEach((event) => {
                    window.removeEventListener(event, clearToastsOnClick);
                });
            };

            // Show toast with no auto-dismiss
            this.$toasted.show(
                `${res.data.company_name} Amount Pending ${this.cashFormatter(
                    res.data.pending_amount
                )}\nCredit Limit: ${res.data.credit_limit}`,
                {
                    type: "success",
                    duration: null,
                    action: {
                        text: "×",
                        onClick: (e, toastObject) => {
                            toastObject.goAway(0);
                            clearToastsOnClick(); // also call it on manual close
                        },
                    },
                }
            );

            // 🟢 Add interaction listeners
            ["click", "keydown", "mousemove"].forEach((event) => {
                window.addEventListener(event, clearToastsOnClick);
            });

            // Rest of your logic
            this.customer_name = value.label;
            this.customer_pin = value.pin;
            this.search_results.customer_id = this.customer_id;
            this.search_results.customer_name = this.customer_name;
            this.$emit("customerSearch", {
                customer_id: this.customer_id,
                customer_name: this.customer_name,
                pin: value.pin,
                loyalty_program: value.loyalty_program,
                is_discount_qualified: value.is_discount_qualified,
                created_at: value.created_at,
                price_group_id: value.price_group_id,
            });
            this.$store.commit("setCustomerData", {
                customer_id: this.customer_id,
                customer_name: this.customer_name,
                pin: value.pin,
                loyalty_program: value.loyalty_program,
                is_discount_qualified: value.is_discount_qualified,
                created_at: value.created_at,
                price_group_id: value.price_group_id,
            });
        },
        async submitRecords(data) {
            var min_profit_margin = data.min_profit_margin;

            var profit_percent = this.calculateProfitPercentage(
                data.selling_price,
                data.purchase_price
            );
            // if (
            //     profit_percent < 0
            // ) {
            //    this.errorNotif("You can't sale below purchase price")
            //    return
            // }
            var profit_limit = this.$store.state.branch.min_profit_margin;
            if (min_profit_margin > 0) {
                if (profit_percent < min_profit_margin) {
                    this.errorNotif(
                        "The set price cannot have margin lower than min product margin of %" +
                            min_profit_margin
                    );
                    return;
                }
            } else if (profit_limit > 0 && min_profit_margin == 0) {
                if (profit_percent < profit_limit) {
                    this.errorNotif(
                        "The set price cannot have margin lower than %" +
                            profit_limit
                    );
                    return;
                }
            }
            data.customer_id = this.customer_id;
            data.customer_name = this.customer_name;
            data.customer_pin = this.customer_pin;
            this.$emit("stockSearchResult", data);
        },

        async searchProduct() {
            const price_group_id =
                this.$store.state.customer_data.price_group_id;
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0 ||
                this.search_query.search_code.length > 0 ||
                this.search_query.search_description.length > 0
            ) {
                //searchItem
                const res = await this.getApi("data/stock/searchPriceGroup", {
                    params: {
                        ...this.search_query,
                        price_group_id: price_group_id,
                    },
                });
                const currentBatch = this.batch_no;
                this.search_results = this.modifyResData(
                    res.data,
                    currentBatch
                );
                console.log(
                    "Search results:",
                    JSON.stringify(this.search_results)
                );
            //    if (this.search_query.code !== "") {
                    if (this.search_results[0]) {
                        this.$emit("stockSearchResult", this.search_results[0]);
                        this.suppressNextSearch = true;

                        this.search_query.code = "";
                        this.batch_no = "";
                    } else {
                        this.e("Code not found");
                    }
             //   }
            } else {
                this.search_results = [];
            }
        },
        modifyResData(data, batch_no) {
            let modif = data.map((obj) => {
                const discount = obj.item_discount || 0; // %
                const price = obj.selling_price || 0;
                const qty = 1;
                const netPrice = price - (price * discount) / 100;
                console.log("Obj>>>" + batch_no);

                return {
                    hs_code: obj.hs_code,
                    id: obj.id,
                    qty: qty,
                    code: obj.code,
                    product_name: obj.product_name,
                    purchase_price: obj.purchase_price,
                    shelve: obj.branchshelves
                        ? obj.branchshelves.shelf.name
                        : "NA",
                    selling_price: price,
                    tax_rate: obj.tax_dept.tax_rate,
                    tax_dept: obj.tax_dept,
                    stock_amount: obj.main_store_qty,
                    customer_name: this.customer_name,
                    customer_id: this.customer_id,
                    customer_pin: this.customer_pin,
                    batch_no: batch_no,
                    sub_total: netPrice * qty, // ✅ apply discount
                    item_id: obj.item_id,
                    min_profit_margin: obj.min_profit_margin,
                    discount: discount,
                    description: obj.description,
                    unit: obj.unit.name,
                };
            });
            return modif;
        },
        async checkInventory(data) {
            this.showLoader();
            const res = await this.getApi("data/inventory/getItemQty", {
                params: {
                    id: data.id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.inventory_data = res.data;
                this.check_inventory_modal = true;
            } else {
                this.form_error(res);
            }
        },
        async checkAveragePrice(data) {
            this.showLoader();
            const res = await this.getApi(
                "data/po_receivable/getAveragePurchasePrice",
                {
                    params: {
                        id: data.id,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.average_price_data = res.data;
                this.average_price_modal = true;
            } else {
                this.form_error(res);
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
