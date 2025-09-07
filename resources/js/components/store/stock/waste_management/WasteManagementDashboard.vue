<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <WasteNav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="reasons_modal = true"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add Reasons
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h3>Waste Management</h3>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label for="">Search Item</label>
                                        <input
                                            type="text"
                                            v-model="search_query.product_name"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for=""> Purpose</label>
                                            <treeselect
                                                :load-options="fetchReasons"
                                                :options="waste_reasons_data"
                                                :auto-load-root-options="false"
                                                v-model="form_data.waste_reason_id"
                                                :multiple="false"
                                                :show-count="true"
                                                placeholder="Select Purpose"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="col-3 form-group d-flex flex-column"
                                    >
                                        <label for=""> Department *</label>

                                        <Select
                                            v-model="form_data.department_id"
                                        >
                                            <Option
                                                v-for="item in dept_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.department }}</Option
                                            >
                                        </Select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Search Staff</label>
                                            <treeselect
                                                :load-options="fetchUser"
                                                :options="user_select_data"
                                                :auto-load-root-options="false"
                                                v-model="form_data.staff_id"
                                                :multiple="false"
                                                :show-count="true"
                                                placeholder="Select Staff"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-md-3 form-group">
                                        <label>Date</label
                                        ><date-picker
                                            v-model="form_data.date_wasted"
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="">No</label>
                                        <input
                                            type="text"
                                            v-model="form_data.waste_no"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="col-md-2">
                                        <button
                                            class="btn btn-secondary btn-small"
                                            @click="search_results = []"
                                        >
                                            clear
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Item Name</th>

                                                <th scope="col">Unit</th>
                                                <th scope="col">P.price</th>
                                                <th>Waste Qty</th>
                                                <th>Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    value, i
                                                ) in search_results"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ value.product_name }}
                                                </td>

                                                <td>{{ value.unit }}</td>
                                                <td>
                                                    {{ value.purchase_price }}
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        v-model="value.qty"
                                                        class="form-control"
                                                    />
                                                </td>
                                                <td>
                                                    {{
                                                        value.purchase_price *
                                                        value.qty
                                                    }}
                                                </td>
                                                <td>
                                                    <button
                                                        class="btn btn-primary btn-sm"
                                                        @click="addWaste(value)"
                                                    >
                                                        Add
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                            </div>
                            <div class="col-md-10">
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Item Name</th>

                                                <th scope="col">Unit</th>
                                                <th scope="col">Purpose</th>
                                                <th scope="col">P.price</th>
                                                <th>Waste Qty</th>
                                                <th>Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(value, i) in waste_data"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ value.stock.product_name }}
                                                </td>

                                                <td>{{ value.unit.name }}</td>
                                                 <td>{{ value.waste_reason.reasons }}</td>
                                                <td>
                                                    {{ value.purchase_price }}
                                                </td>
                                                <td>
                                                 {{value.qty}}
                                                     
                                                  
                                                </td>
                                                <td>
                                                    {{
                                                        value.purchase_price *
                                                        value.qty
                                                    }}
                                                </td>
                                                <td>
                                                    <button
                                                        class="btn btn-danger btn-sm"
                                                        @click="
                                                            deleteWaste(value.id,i)
                                                        "
                                                    >
                                                        delete
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div><v-btn color="warning" @click="complete()">Complete</v-btn></div>
                            </div>
                        </div>

                        <Modal
                            v-model="reasons_modal"
                            width="800px"
                            title="Waste Reasons"
                        >
                            <create-waste-reasons
                                v-if="reasons_modal"
                                v-on:dashboard-active="dismissReasonsModal"
                            />
                        </Modal>
                    </div>
                </div>
                <center v-else>
                    <b style="color: red; font-size: 1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Pagination from "../../../utilities/Pagination.vue";
import CreateWasteReasons from "./CreateWasteReasons.vue";
import WasteNav from "./WasteNav.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    data() {
        return {
            search_results: [],
            search_query: {
                product_name: "",
                code: "",
            },
            user_select_data: null,
            dept_data: [],
            waste_data: [],
            reasons_modal: false,
            waste_reasons_data: null,
            form_data: {
                department_id: null,
                waste_reason_id: null,
                waste_no: "",
                staff_id: null,
                unit_id: null,
                purchase_price: 0,
                total: 0,
                date_wasted: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
        CreateWasteReasons,
        WasteNav,
        Treeselect,
    },
    mounted() {
        this.form_data.date_wasted = this.getCurrentDate();
        this.concurrentApiReq();
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
       async complete(){
         this.showLoader();

            const res = await this.callApi(
                "post",
                "data/waste_record/complete",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
                this.waste_data = [];
               this.$router.push("waste_report");
            } else {
                this.form_error(res);
            }
       },
            async deleteWaste(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/waste_record/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.waste_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchDept(),
                this.fetchWasteNo(),
                this.fetchUser(),
            ]);
            this.hideLoader();
        },
        async fetchReasons() {
            const res = await this.getApi("data/waste_reason/fetch", {});

            if (res.status == 200) {
                this.waste_reasons_data = this.modifyReasonsSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyReasonsSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.reasons,
                };
            });
            return modif;
        },
        async fetchUser() {
            const res = await this.getApi("data/users/fetchAll", {});

            if (res.status == 200) {
                this.user_select_data = this.modifyWaiterSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyWaiterSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        async fetchWasteNo() {
            const res = await this.getApi("data/waste_record/fetchWasteNo", "");

            if (res.status == 200) {
                if (res.data.waste_data.length > 0) {
                    this.form_data.staff_id = res.data.waste_data[0].staff_id;
                    this.form_data.waste_no = res.data.waste_data[0].waste_no;
                     this.form_data.waste_reason_id = res.data.waste_data[0].waste_reason_id;
                    this.form_data.department_id =
                        res.data.waste_data[0].department_id;

                    this.waste_data = res.data.waste_data;
                } else {
                    this.form_data.waste_no = res.data.waste_no;
                }
            } else {
                this.swr("Server error occurred");
            }
        },

        async addWaste(value) {
            this.form_data.qty = parseFloat(value.qty);
            this.form_data.stock_id = value.id;
            this.form_data.unit_id = value.unit_id;

            this.form_data.purchase_price = value.purchase_price;

            this.form_data.total =
                this.form_data.purchase_price * this.form_data.qty;

           

            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/waste_record/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
                this.waste_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query,
                    },
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },

        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    unit_id: obj.unit_id,
                    qty: 1,

                    code: obj.code,

                    product_name: obj.product_name,
                    purchase_price: obj.purchase_price,
                    total: obj.purchase_price * 1,
                    unit: obj.unit.name,
                };
            });
            return modif;
        },
        async fetch() {},
        dismissReasonsModal() {
            this.reasons_modal = false;
        },
    },
};
</script>
<style scoped>
.show-img {
    border-color: #ff6f00;
    background: #ff6f00;
}
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
</style>
