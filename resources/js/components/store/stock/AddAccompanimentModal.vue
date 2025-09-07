<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Add Accompaniment</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="form-group">
                                    <Select
                                        v-model="accompaniment_id"
                                        multiple
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
                            <div class="col-4">
                                <button
                                    class="btn btn-sm btn-primary"
                                    @click="saveRecord()"
                                >
                                    Save Records
                                </button>
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
                                            <th scope="col">Accompaniments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                value, i
                                            ) in accomapaniment_data"
                                            :key="i"
                                        >
                                            <th scope="row">
                                                {{ value.accompaniment.code }}
                                            </th>
                                            <td>
                                                {{
                                                    value.accompaniment
                                                        .product_name
                                                }}
                                            </td>
                                            <td>
                                                <button
                                                    class="btn btn-sm btn-danger"
                                                    @click="
                                                        deleteDetils(
                                                            value.id,
                                                            i
                                                        )
                                                    "
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
        </div>
    </div>
</template>

<script>
export default {
    props: ["product_data"],
    data() {
        return {
            accompaniment_id: "",
            search_stock_list: [],
            loading1: false,
            accomapaniment_data: [],
            form_data: {
                category_id: null,
                stock_id: null,
                accompaniment_array_id: [],
            },
        };
    },
    mounted() {
        this.form_data.category_id = this.product_data.product_category_id;
        this.form_data.stock_id = this.product_data.id;
        this.fetchAccompaniment();
    },
    methods: {
        async deleteDetils(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/accompaniment/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.accomapaniment_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async fetchAccompaniment() {
            this.showLoader();
            const res = await this.getApi("data/accompaniment/fetch", {
                params: {
                    stock_id: this.form_data.stock_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.accomapaniment_data = res.data;
            } else {
                this.servo();
            }
        },
        async saveRecord() {
            this.form_data.accompaniment_array_id = this.accompaniment_id;

            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/accompaniment/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Saved");
                this.search_stock_list = [];
                this.accompaniment_id = "";
                this.accomapaniment_data = res.data;
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
