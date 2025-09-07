<template>
    <div class="container">
        <div class="row justify-content-center" v-if="isReadPermitted">
            <div class="col-md-8">
                <v-btn color="primary" dark @click="showModal()"
                    >Add Drinks Items</v-btn
                >
                <div class="card">
                    <div class="card-header"><h3>DRINKS ISSUE</h3></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 form-group d-flex flex-column">
                                <label for="">Department*</label>

                                <Select v-model="form_data.department_id">
                                    <Option
                                        v-for="item in dept_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.department }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="col-4">
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
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item</th>
                                            <th>Qty Issued</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in filteredStock"
                                            :key="i"
                                        >
                                            <th scope="row">
                                                {{ value.code }}
                                            </th>
                                            <td>
                                                {{ value.product_name }}
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="value.qty"
                                                    class="form-control"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <v-btn
                                    color="info"
                                    :loading="loading_btn_issue"
                                    dark
                                    @click="issueStock()"
                                    >Issue Items</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <unauthorized />
        </div>
        <Modal v-model="add_items_modal" title="Add Kitchen Items" width="60px">
            <create-drinks-items
                v-if="add_items_modal"
                :stock_kitchen_data="stock_kitchen_data"
                v-on:updated_kitchen_list="updatelist"
            />
        </Modal>
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";

import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateDrinksItems from "./CreateDrinksItems.vue";
export default {
    components: { Unauthorized, Treeselect, CreateDrinksItems },
    data() {
        return {
            add_items_modal: false,
            stock_kitchen_data: [],
            filteredStock: [],
            loading_btn_issue: false,
            dept_data: null,
            user_select_data: null,
            form_data: {
                department_id: null,
                issued_staff_id: null,
            },
        };
    },
    mounted() {
        this.fetchStock();
        this.fetchDept();
    },
    methods: {
        async issueStock() {
            this.loading_btn_issue = true;

            const res = await this.callApi(
                "post",
                "data/internal_issue/kitchenBatchIssue",
                { ...this.form_data, issue_data: this.filteredStock }
            );
            this.loading_btn_issue = false;
            if (res.status === 200) {
                this.successNotif("Items issue successfully");
                this.filteredRows();
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
        async issueKitchen() {},
        updatelist(val) {
            this.stock_kitchen_data = val;
            this.filteredRows();
        },
        showModal() {
            this.add_items_modal = true;
        },
        async fetchStock() {
            this.showLoader();
            const res = await this.getApi("data/drinks_items/fetch", {});
            this.hideLoader();
            if (res.status == 200) {
                this.stock_kitchen_data = res.data;
                this.filteredRows();
            } else {
                this.servo();
            }
        },
        filteredRows() {
            this.filteredStock = this.stock_kitchen_data.map((obj) => {
                return {
                    stock_id: obj.id,
                    product_name: obj.product_name,
                    code: obj.code,
                    qty: "",
                };
            });
        },
    },
};
</script>
