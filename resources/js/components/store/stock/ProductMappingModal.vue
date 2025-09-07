<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">{{
                                                product_data.product_name
                                            }}</div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="">Elements</label>
                                        <Select
                                            v-model="form_data.element_id"
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
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for=""
                                            >
                                            qty Produced</label
                                        >
                                        <input
                                            type="number"
                                            class="form-control"
                                            v-model="form_data.qty"
                                        />
                                    </div>
                                </div>
                                <div class="col-3">
                                    <v-btn
                                        small
                                        color="primary"
                                        @click="saveRecord()"
                                    >
                                        Save Records
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
                                                <th scope="col">Code</th>
                                                <th scope="col">
                                                    Element
                                                </th>
                                                <th>Qty</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    value, i
                                                ) in mappings_data"
                                                :key="i"
                                            >
                                                <th scope="row">
                                                    {{
                                                        value.element.code
                                                    }}
                                                </th>
                                                <td>
                                                    {{
                                                        value.element.product_name
                                                    }}
                                                </td>
                                                 <td>
                                                    {{
                                                        value.qty
                                                    }}
                                                </td>
                                                <td>
                                                    <v-btn
                                                        color="danger"
                                                        x-small
                                                        @click="
                                                            deleteDetils(
                                                                value.id,
                                                                i
                                                            )
                                                        "
                                                    >
                                                        Delete
                                                    </v-btn>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
export default {
    props: ["product_data"],
    data() {
        return {
            accompaniment_id: "",
            search_stock_list: [],
            loading1: false,
            mappings_data: [],
            form_data: {
                stock_id: null,
                element_id: null,

                qty: 1,
            },
        };
    },
    mounted() {
        this.form_data.stock_id = this.product_data.id;
        this.fetchAccompaniment();
    },
    methods: {
        async deleteDetils(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/product_mapping/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.mappings_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async fetchAccompaniment() {
            this.showLoader();
            const res = await this.getApi("data/product_mapping/fetch", {
                params: {
                    stock_id: this.form_data.stock_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.mappings_data = res.data;
            } else {
                this.servo();
            }
        },
        async saveRecord() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/product_mapping/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Saved");
                this.search_stock_list = [];
                this.accompaniment_id = "";
                this.mappings_data = res.data;
            } else {
                this.form_error(res);
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
<style scoped>
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
td {
    font-size: 0.8rem !important;
}
</style>
