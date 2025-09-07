<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b> Internal Stock Issue</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">Search Item</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Item Name</th>
                                                <th scope="col">Loc</th>

                                                <th>Store Qty</th>

                                                <th scope="col">Unit</th>
                                                <th>Issued Qty</th>
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
                                                <td>
                                                    {{ value.department }}
                                                </td>

                                                <td>{{ value.store_qty }}</td>

                                                <td>{{ value.unit }}</td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        v-model="
                                                            value.qty_issued
                                                        "
                                                        class="form-control"
                                                    />
                                                </td>
                                                <td>
                                                    <button
                                                        class="btn btn-primary btn-sm"
                                                        @click="
                                                            issueStock(value)
                                                        "
                                                    >
                                                        Add
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <fieldset class="border">
                                    <legend class="text-center">Issue</legend>

                                    <div class="row">
                                        <div
                                            class="col-md-3 form-group nopadding"
                                        >
                                            <label>Issue No: </label>
                                            <input
                                                type="text"
                                                disabled
                                                v-model="form_data.issue_no"
                                                class="form-control"
                                            />
                                        </div>
                                        <div
                                            class="col-md-3 form-group nopadding"
                                        >
                                            <label>Internal Issue No: </label>
                                            <input
                                                type="text"
                                                v-model="
                                                    form_data.internal_issue_no
                                                "
                                                class="form-control"
                                            />
                                        </div>

                                        <div
                                            class="col-md-3 form-group nopadding d-flex flex-column"
                                        >
                                            <label for="">Source. Loc.*</label>

                                            <Select
                                                v-model="
                                                    form_data.source_department_id
                                                "
                                            >
                                                <Option
                                                    v-for="item in dept_data"
                                                    :value="item.id"
                                                    :key="item.id"
                                                    >{{
                                                        item.department
                                                    }}</Option
                                                >
                                            </Select>
                                        </div>
                                        <div
                                            class="col-md-3 form-group nopadding d-flex flex-column"
                                        >
                                            <label for="">Dest. Loc.*</label>

                                            <Select
                                                v-model="
                                                    form_data.department_id
                                                "
                                            >
                                                <Option
                                                    v-for="item in dept_data"
                                                    :value="item.id"
                                                    :key="item.id"
                                                    >{{
                                                        item.department
                                                    }}</Option
                                                >
                                            </Select>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group nopadding">
                                                <label for=""
                                                    >Search Staff</label
                                                >
                                                <treeselect
                                                    :load-options="fetchUser"
                                                    :options="user_select_data"
                                                    :auto-load-root-options="
                                                        false
                                                    "
                                                    v-model="
                                                        form_data.issued_staff_id
                                                    "
                                                    :multiple="false"
                                                    :show-count="true"
                                                    placeholder="Select Staff"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Item Name</th>

                                                <th>Issue Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(value, i) in issue_data"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ value.product_name }}
                                                </td>

                                                <td>
                                                    <input
                                                        class="form-control"
                                                        style="min-width: 4rem"
                                                        type="text"
                                                        v-model="
                                                            value.qty_issued
                                                        "
                                                    />
                                                </td>

                                                <td>
                                                    <button
                                                        class="btn btn-danger btn-sm"
                                                        @click="
                                                            deleteData(value, i)
                                                        "
                                                    >
                                                        remove
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <v-btn
                                    :loading="btn_loading"
                                    small
                                    color="primary"
                                    dark
                                    @click="completeIssue()"
                                >
                                    Complete Issue</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="receive_modal" title="Receive Stock" width="70px">
            <ReceiveIssueStock v-if="receive_modal" />
        </Modal>
    </div>
</template>

<script>
import ReceiveIssueStock from "./ReceiveIssueStock.vue";
import { mdiPlusThick } from "@mdi/js";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    components: { ReceiveIssueStock, Treeselect },
    data() {
        return {
            icons: {
                mdiPlusThick,
            },
            btn_loading: false,
            search_results: [],
            search_query: {
                product_name: "",
                code: "",
            },
            search: "",
            user_select_data: null,
            receive_modal: false,
            issue_data: [],
            dept_data: null,
            form_data: {
                stock_id: null,
                unit_id: null,
                qty_issued: 1,
                mapping_value: 1,
                purchase_price: 0,
                issue_no: "",
                internal_issue_no: "",
                department_id: null,
                source_department_id: null,
                unit: "",
                row_total: 0,
                final_qty: 0,
                opening_stock: 0,
                product_department_id: null,
                issued_staff_id: null,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
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
        async deleteData(data, i) {
            console.log(JSON.stringify(data));
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/internal_issue/destroy",
                    data
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.issue_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async downloadIssueNote() {
            this.btn_loading = true;
            axios({
                url: "data/internal_issue/completeIssue",

                data: {
                    issue_data: this.issue_data,
                    issue_no: this.form_data.issue_no,
                    internal_issue_no: this.form_data.internal_issue_no,
                    department_id: this.form_data.department_id,
                    issued_staff_id: this.form_data.issued_staff_id,
                },
                method: "POST",
                responseType: "blob", // important
            })
                .then((response) => {
                    this.btn_loading = false;
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute(
                        "download",
                        this.form_data.issue_no + ".pdf"
                    );
                    document.body.appendChild(link);
                    link.click();
                    this.$router.push("internal_issue_report");
                })
                .catch((error) => {
                    this.btn_loading = false;
                    if (error.response.status == 422) {
                        this.errorNotif("Some items don't have enough qty");
                        return;
                    } else {
                        this.servo();
                    }
                });
        },

        async completeIssue() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/internal_issue/completeIssue",
                {
                    internal_issue_no: this.form_data.internal_issue_no,
                    issue_data: this.issue_data,
                    issue_no: this.form_data.issue_no,
                    department_id: this.form_data.department_id,
                    source_department_id: this.form_data.source_department_id,
                    issued_staff_id: this.form_data.issued_staff_id,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Successfully issued");
                // this.$router.go(0)
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = 0)
                );
                this.form_data.qty_issued = 1;
                this.form_data.department_id = null;
                this.form_data.issued_staff_id = null;
                this.issue_data = [];
                this.form_data.issue_no = res.data;
                this.form_data.internal_issue_no = "";
                this.search_results = [];
            } else {
                this.form_error(res);
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchDept(),
                this.fetchIssueNo(),
                this.fetchUser(),
            ]);
            this.hideLoader();
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchIssueNo() {
            const res = await this.getApi(
                "data/internal_issue/fetchIssueNo",
                ""
            );

            if (res.status == 200) {
                if (res.data.issue_data.length > 0) {
                    this.form_data.issued_staff_id =
                        res.data.issue_data[0].issued_staff_id;
                    this.form_data.issue_no = res.data.issue_data[0].issue_no;
                    this.form_data.department_id =
                        res.data.issue_data[0].department_id;
                    this.form_data.source_department_id =
                        res.data.issue_data[0].source_department_id;
                    this.issue_data = this.modifyIssueData(res.data.issue_data);
                } else {
                    this.form_data.issue_no = res.data.issue_no;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async issueStock(value) {
            this.form_data.qty_issued = parseFloat(value.qty_issued);
            this.form_data.stock_id = value.id;
            this.form_data.unit_id = value.unit_id;
            this.form_data.mapping_value = value.mapping_value;
            this.form_data.purchase_price = value.purchase_price;
            this.form_data.unit = value.unit;
            this.form_data.row_total =
                this.form_data.purchase_price * this.form_data.qty_issued;
            this.form_data.final_qty = (
                this.form_data.qty_issued * this.form_data.mapping_value
            ).toFixed(2);
            this.form_data.opening_stock = value.store_qty;
            this.form_data.product_department_id =
                this.form_data.source_department_id;
            this.form_data.department_id = this.form_data.department_id;

            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/internal_issue/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
                this.issue_data = this.modifyIssueData(res.data);
            } else {
                this.form_error(res);
            }
        },

        async searchProduct() {
            const res = await this.getApi("data/inventory/searchItemChunk", {
                params: {
                    search: this.search,
                },
            });

            this.search_results = this.modifyResData(res.data);
        },

        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.stock_id,
                    unit_id: obj.stock.unit_id,
                    qty: 1,
                    department_id: obj.department_id,
                    department: obj.department.department,
                    code: obj.stock.code,
                    mapping_value: 1,
                    product_name: obj.stock.product_name,
                    purchase_price: obj.stock.purchase_price,
                    unit: obj.stock.unit.name,

                    store_qty: obj.qty,

                    qty_issued: 1,
                };
            });
            return modif;
        },
        modifyIssueData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    unit_id: obj.unit_id,
                    qty_issued: obj.qty_issued,
                    stock_id: obj.stock_id,
                    branch_id: obj.branch_id,
                    user_id: obj.user_id,
                    date_issued: obj.date_issued,
                    code: obj.code,
                    mapping_value: obj.mapping_value,
                    issue_no: obj.issue_no,
                    product_name: obj.stock.product_name,
                    purchase_price: obj.purchase_price,
                    unit: obj.unit.name,
                    row_total: obj.row_total,
                    final_qty: obj.final_qty,
                    opening_stock: obj.opening_stock,
                    source_department_id: obj.source_department_id,
                    department_id: obj.department_id,
                };
            });
            return modif;
        },
    },
};
</script>
