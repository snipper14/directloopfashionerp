<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
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
                    <h4>Add Production Details</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label> Production Date *</label>
                                    <date-picker
                                        v-model="form_data.produced_on"
                                        valueType="format"
                                    ></date-picker>
                                </div>

                                <div class="form-group col-md-7">
                                    <label for="">Employee</label>
                                    <div class="form-group">
                                        <treeselect
                                            v-model="form_data.employee_id"
                                            :multiple="false"
                                            :options="
                                                select_data.employee_options
                                            "
                                            :show-count="true"
                                            placeholder="Select "
                                        />
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <div class="form-group">
                                        <Select
                                            @change="onChange($event)"
                                            v-model="form_data.stock_id"
                                            filterable
                                            :remote-method="searchProducts"
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
                                <div class="form-group col-md-7">
                                    <label>Production Cost *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.production_cost"
                                        placeholder="Cost"
                                    />
                                </div>
                                <div class="form-group col-md-7">
                                    <label>QTY *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.qty"
                                        placeholder="QTY"
                                    />
                                </div>
                                <div class="form-group col-md-7">
                                    <label>Total Pay *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.total_pay"
                                        placeholder=""
                                    />
                                </div>

                                <div class="form-group col-md-7">
                                    <label>Description *</label>
                                    <textarea
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.description"
                                        placeholder="Description"
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="submitProduction()"
                        >
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CustomSelect from "../../utilities/CustomSelect.vue";
export default {
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            employee_data: [],
            select_data: {
                product_data: "",
                employee_options: "",
            },
            form_data: {
                produced_on: this.getCurrentDate(),
                stock_id: "",
                employee_id: null,
                qty: 1,
                production_cost: 0,
                total_pay: 0,
                size: "",
                colour: "",
                description: "",
                pay_rate: 0,
            },

            isOpen: false,
            results: [],
            search_item: "",
            isLoading: false,
            arrowCounter: -1,
            select_item: null,
            search_stock_list: [],
        };
    },
    components: {
        CustomSelect,
        Treeselect,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                if (this.search_stock_list.length > 0) {
                    this.form_data.total_pay = (
                        val.qty * this.form_data.production_cost
                    ).toFixed(2);
                }
            }, 500),
        },
        search_stock_list: {
            deep: true,
            handler: _.debounce(function (val, old) {
                if (this.search_stock_list.length > 0) {
                    let index = this.search_stock_list.findIndex(
                        (cp) => cp.value == this.form_data.stock_id
                    );

                    this.form_data.production_cost =
                        this.search_stock_list[index].cost_price;
                }
            }, 100),
        },
    },
    methods: {
        modifyProductKey() {
            let modif = this.employee_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.first_name + "  " + obj.last_name,
                };
            });
            return modif;
        },

        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getPieceWorkers()]).then(
                function (results) {
                    return results;
                }
            );
            this.hideLoader();

            this.employee_data = res[0].data.results;
            this.select_data.employee_options = this.modifyProductKey();
        },

        async getPieceWorkers() {
            return await this.getApi("data/employee/getPieceWorkers", "");
        },
        async submitProduction() {
            const res = await this.callApi(
                "post",
                "data/production/create",
                this.form_data
            );

            if (res.status === 200) {
                this.s("  Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.form_data.employee_id = null;
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
        searchProducts: _.debounce(async function (query) {
            if (query.length !== "") {
                const res = await this.getApi("data/stock/search_items", {
                    params: { search: query },
                });

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
                    cost_price: obj.purchase_price,
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
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
