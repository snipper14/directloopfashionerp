<template>
    <div class="container"  >
        <div class="row justify-content-center">
            <div class="col-md-2">
                <IssueConversionNav />
            </div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">Stock Conversion</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label> Issue Date *</label
                                    ><date-picker
                                        v-model="form_data.date_issued"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Issue No.</label>
                                    <input
                                        disabled
                                        type="text"
                                        v-model="form_data.issue_no"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-md-2 form-group d-flex flex-column">
                                <label for=""> Loc From*</label>

                                <Select v-model="form_data.department_id">
                                    <Option
                                        v-for="item in dept_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.department }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="col-md-2 form-group d-flex flex-column">
                                <label for=""> Loc To*</label>

                                <Select v-model="form_data.department_to_id">
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
                                    <label for="">Product Search</label>
                                    <Select
                                        v-model="stock_id"
                                        filterable
                                        :remote-method="searchProducts"
                                        :loading="loading1"
                                    >
                                        <Option
                                            v-for="(
                                                option, index
                                            ) in search_stock_list"
                                            :value="option.value"
                                            :key="index"
                                            >{{ option.label }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>

                            <div class="col-md-2 form-group d-flex flex-column">
                                <label for=""> Elements*</label>

                                <Select v-model="element_id">
                                    <Option
                                        v-for="item in element_data"
                                        :value="item.element_id"
                                        :key="item.id"
                                        >{{ item.element.product_name }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Mapping Qty</label>
                                    <input
                                        disabled
                                        type="number"
                                        v-model="form_data.mapping_value"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Parent Issue Qty </label>
                                    <input
                                        disabled
                                        type="number"
                                        v-model="qty_issued"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for=""> Final Issue</label>
                                    <input
                                        type="number"
                                        v-model="form_data.qty_issued"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Search Staff</label>
                                    <treeselect
                                        :load-options="fetchUser"
                                        :options="user_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.issued_staff_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Staff"
                                    />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <v-btn
                                v-if="isWritePermitted"
                                title="write"
                                    color="primary"
                                    small
                                    :loading="btn_loading"
                                    @click="createIssue()"
                                    >+ Add
                                </v-btn>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered mt-3"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Parent Item</th>
                                        <th scope="col">Parent Issue Qty</th>
                                        <th scope="col">Mapping Val</th>
                                        <th scope="col">Child Item</th>
                                        <th>Issue Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(value, i) in issue_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                value.parent_stock.product_name
                                            }}
                                        </td>
                                        <td>
                                            {{ value.parent_issue_qty }}
                                        </td>
                                        <td>
                                            {{ value.mapping_value }}
                                        </td>
                                        <td>
                                            {{ value.stock.product_name }}
                                        </td>
                                        <td>
                                            {{ value.qty_issued }}
                                        </td>

                                        <td>
                                            <v-btn
                                            title="delete"
                                            v-if="isDeletePermitted"
                                                color="danger"
                                                x-small
                                                @click="deleteData(value, i)"
                                            >
                                                remove
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <v-btn
                        title="update"
                        v-if="isUpdatePermitted"
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
            <div v-else><unauthorized/></div>
        </div>
    </div>
</template>

<script>
import IssueConversionNav from "./IssueConversionNav";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Unauthorized from '../../../utilities/Unauthorized.vue';
export default {
    components: { Treeselect, IssueConversionNav,Unauthorized },
    data() {
        return {
            user_select_data: null,
            loading1: false,
            dept_data: null,
            search_stock_list: [],
            element_data: null,
            element_id: null,
            btn_loading: false,
            stock_id: null,
            qty_issued: 0,
            issue_data: [],
            form_data: {
                stock_id: null,
                parent_stock_id: null,
                issued_staff_id: null,
                issue_no: null,
                department_id: null,
                parent_issue_qty: 0,
                mapping_value: 0,
                qty_issued: 0,
                date_issued: null,
            },
        };
    },

    mounted() {
        this.form_data.date_issued = this.getCurrentDate();
        this.concurrentApiReq();
    },
    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.qty_issued =
                    (parseFloat(this.form_data.qty_issued) * 1) /
                    parseFloat(this.form_data.mapping_value);
                this.qty_issued = parseFloat(this.qty_issued).toFixed(4);
            }, 500),
        },
        stock_id: {
            handler: _.debounce(function (val, old) {
                this.searchProductElement();
            }, 500),
        },
        element_id: {
            handler: _.debounce(function (val, old) {
                this.getConversionValue();
            }, 100),
        },
    },
    methods: {
        async deleteData(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/issue_conversion/destroy",
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
        async completeIssue() {
            const res = await this.callApi(
                "post",
                "data/issue_conversion/completeIssue",
                { issue_no: this.form_data.issue_no }
            );

            if (res.status == 200) {
                this.s("completed");
                 this.$router.push("stock_conversion_report");
            } else {
                this.swr("Server error occurred");
            }
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
        async createIssue() {
            this.form_data.stock_id = this.element_id;
            this.form_data.parent_issue_qty = this.qty_issued;
            this.form_data.parent_stock_id = this.stock_id;
            this.btn_loading = true;
            const res = await this.callApi(
                "post",
                "data/issue_conversion/create",
                this.form_data
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.element_data = null;
                this.element_id = null;
                this.stock_id = null;
                this.form_data.parent_stock_id = null;
                this.form_data.stock_id = null;
                this.form_data.qty_issued = 0;
                this.issue_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchIssueNo() {
            const res = await this.getApi(
                "data/issue_conversion/fetchIssueNo",
                ""
            );

            if (res.status == 200) {
                if (res.data.issue_data.length > 0) {
                    this.form_data.issued_staff_id =
                        res.data.issue_data[0].issued_staff_id;
                    this.form_data.issue_no = res.data.issue_data[0].issue_no;
                    this.form_data.department_id =
                        res.data.issue_data[0].department_id;
                    this.form_data.department_to_id =
                        res.data.issue_data[0].department_to_id;
                    this.issue_data = res.data.issue_data;
                } else {
                    this.form_data.issue_no = res.data.issue_no;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async searchProductElement() {
            this.showLoader();

            const res = await this.getApi("data/product_mapping/fetch", {
                params: {
                    stock_id: this.stock_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.element_data = res.data;
            }
        },
        async getConversionValue() {
            this.showLoader();
            const res = await this.getApi("data/product_mapping/fetchQty", {
                params: {
                    stock_id: this.stock_id,
                    element_id: this.element_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.form_data.mapping_value = res.data.qty;
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchDept(), this.fetchIssueNo()]);
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
        searchProducts: _.debounce(async function (query) {
            if (query.length !== "") {
                this.loading1 = true;
                const res = await this.getApi("data/stock/search_items", {
                    params: { search: query },
                });
                this.loading1 = false;

                this.search_stock_list = this.modifyKey(res.data).filter(
                    (item) =>
                        item.label.toLowerCase().indexOf(query.toLowerCase()) >
                        -1
                );
            }
        }, 500),

        modifyKey(data) {
            let modif = data.map((obj) => {
                return {
                    value: obj.id,
                    label: obj.product_name,
                };
            });
            return modif;
        },
    },
};
</script>
