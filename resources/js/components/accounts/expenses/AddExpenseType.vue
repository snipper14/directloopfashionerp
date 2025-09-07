<template>
    <div class="container">
        <div class="card">
            <div class="card-header"></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <v-text-field
                            label="Expenses Category"
                            v-model="form_data.name"
                            :rules="rules"
                            hide-details="auto"
                        ></v-text-field>
                    </div>
                     <div class="col-md-4 form-group">
                                    <label for=""
                                        >Selecte Expense Account</label
                                    >
                                    <treeselect
                                        :load-options="fetchExpenseTypes"
                                        :auto-load-root-options="false"
                                        :options="expense_account_select_data"
                                        v-model="
                                            form_data.ledger_account_id
                                        "
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Expenses Account"
                                       
                                    />
                                </div>
                    <div class="col-md-3">
                        <v-btn
                            :loading="add_btn_loader"
                            color="primary"
                            @click="
                                form_data.id ? updateCategory() : addCategory()
                            "
                            dark
                        >
                            {{
                                form_data.id
                                    ? "Update Category"
                                    : "+ Add Category"
                            }}
                        </v-btn>
                    </div>
                </div>
                <div class="row">
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                
                                    <th scope="col">Category</th>
                                    <th scope="col">Ledger Account</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(value, i) in expenses_data"
                                    :key="i"
                                >
                                    <th scope="row">
                                        {{ value.id }}
                                    </th>
                                   
                                    <td>
                                        {{ value.original_name }}
                                    </td>
                                     <th>{{ value.ledger_account.account }}</th>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-info"
                                            v-if="isUpdatePermitted"
                                            title="edit"
                                            @click="editCategory(value)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="isDeletePermitted"
                                            title="delete"
                                            class="btn btn-sm btn-danger"
                                            @click="deleteCategory(value.id, i)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            add_btn_loader: false,
            rules: [
                (value) => !!value || "Required.",
                (value) => (value && value.length >= 3) || "Min 3 characters",
            ],
            expense_account_select_data:null,
            expenses_data: [],
            form_data: {
                id: null,
                name: "",
                ledger_account_id: null,
            },
        };
    },
    mounted() {
        this.fetch();
    },
    methods: {
         async fetchExpenseTypes() {
            const res = await this.getApi("data/ledger_account/fetchExpenseTypes", {
                params: { search: "" },
            });

            if (res.status === 200) {
                const data = res.data;

                this.expense_account_select_data = data.map((item) => ({
                   id: item.id,
                    label: `${item.account}`,
                                 
                 
                }));
              
                
            } else {
                this.form_error(res);
            }
        },
        async updateCategory() {
            this.add_btn_loader = true;

            const res = await this.callApi("PUT", "data/expense_type/update", {
                ...this.form_data,
            });

            this.add_btn_loader = false;

            if (res.status === 200) {
                this.s("Updated");
                this.form_data = { id: null, name: "" };
               this.fetch();
            } else {
                this.form_error(res);
            }
        },
        editCategory(value) {
            this.form_data.id = value.id;
            this.form_data.name = value.original_name || value.name;
        },
        async fetch() {
            const res = await this.getApi("data/expense_type/fetch", {});
            this.expenses_data = res.data;
        },
        async deleteCategory(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "DELETE",
                    "data/expense_type/delete/" + id,
                    {}
                );
                this.add_btn_loader = false;
                if (res.status === 200) {
                    this.s("Deleted");
                    this.expenses_data.splice(i, 1);
                } else {
                    this.form_error(res);
                }
            }
        },
        async addCategory() {
            this.add_btn_loader = true;
            const res = await this.callApi("POST", "data/expense_type/create", {
                ...this.form_data,
            });
            this.add_btn_loader = false;
            if (res.status === 200) {
                this.s("Added");
                this.form_data.name = "";
                this.form_data.ledger_account_id = null;
                this.expenses_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
