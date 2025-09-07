<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <v-btn
                    class="ma-2 v-btn-primary"
                    @click="$emit('dashboard-active')"
                    color="primary"
                    dark
                >
                    <v-icon dark left>
                        mdi-arrow-left
                    </v-icon>
                    Back
                </v-btn>
                <div class="card">
                    <div class="card-header d-flex">
                        <h4>
                            <strong>{{ data.company_name }}</strong>
                        </h4>
                        <button
                            v-if="isDeletePermitted"
                            class="btn btn-danger custom-button ml-4"
                            @click="deleteBalance()"
                        >
                            Clear Balances
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group ">
                                    <label for="">Opening Date</label>
                                    <date-picker
                                        v-model="form_data.entry_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group">
                                    <label for="">Dr Amount</label>
                                    <input
                                        v-model="form_data.dr_balance"
                                        type="number"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder=""
                                    />
                                </div>
                                  <div class="form-group">
                                    <label for="">Cr Amount</label>
                                    <input
                                        v-model="form_data.cr_balance"
                                        type="number"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder=""
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Description</label>
                                    <input
                                        v-model="form_data.description"
                                        type="text"
                                        class="form-control"
                                        autocomplete="off"
                                        placeholder=""
                                    />
                                </div>
                                <div class="flex">
                                    <button
                                        v-if="isWritePermitted"
                                        class="btn btn-secondary  "
                                        @click="addBalance()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    props: ["data"],
    data() {
        return {
            form_data: {
                customer_id: null,
                ref: "Opening",
                type: "OP",
                entry_date: "",
                description: "",
                debit: 0,
                amount_due: 0,
                dr_balance: 0,
                cr_balance: 0
            }
        };
    },

    mounted() {
        this.form_data.customer_id = this.data.id;
        this.form_data.entry_date = this.getCurrentDate();

        this.concurrentApiReq();
    },
    methods: {
        async addBalance() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/cust_ledger/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("has been recorded ");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        async deleteBalance() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/cust_ledger/destroy/" + this.data.id,
                    ""
                );

                if (res.status === 200) {
                    this.w("Deleted!!!! ");

                    this.$emit("dashboard-active");
                } else {
                    this.swr("Server error try again later");
                }
            }
        }
    }
};
</script>
<style scoped>
.top-menu {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
}
.order-wrapper {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
}
.date-picker {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
}
.autocomplete {
    position: relative;
}

.autocomplete-results {
    padding: 0;
    margin: 0;
    border: 1px solid #eeeeee;
    height: 120px;
    overflow: auto;
}

.autocomplete-result {
    list-style: none;
    text-align: left;
    padding: 4px 2px;
    cursor: pointer;
}

.autocomplete-result.is-active,
.autocomplete-result:hover {
    background-color: #4aae9b;
    color: white;
}
</style>
