<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="form-group">
                                    <Select
                                        v-model="stock_id"
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
                                <v-btn
                                    color="primary"
                                    dark
                                    :loading="save_record_loading"
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
                                            <th scope="col">Item</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(value, i) in stock_data"
                                            :key="i"
                                        >
                                            <th scope="row">
                                                {{ value.code }}
                                            </th>
                                            <td>
                                                {{ value.product_name }}
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
    props:['stock_kitchen_data'],
    data() {
        return {
            save_record_loading: false,
            search_stock_list: null,
            stock_id: null,
            loading1: false,
            stock_data: [],
        };
    },
    mounted() {
        this.stock_data=this.stock_kitchen_data},
    methods: {
           async fetchStock() {
            this.showLoader();
            const res = await this.getApi("data/kitchen_items/fetch", {
               
            });
            this.hideLoader();
            if (res.status == 200) {
                this.stock_data = res.data;
            } else {
                this.servo();
            }
        },
        async deleteDetils(id, i) {
             const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
            this.showLoader();
            const res = await this.callApi(
                "DELETE",
                "data/kitchen_items/remove/" + id,
                {}
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s('removed')
                this.stock_data = res.data;
            } else {
                this.form_error(res);
            }}
        },
        async saveRecord() {
            this.save_record_loading = true;
            const res = await this.callApi(
                "post",
                "data/kitchen_items/create",
                { stock_id: this.stock_id }
            );
            this.save_record_loading = false;
            if (res.status == 200) {
                this.successNotif("Saved");
                this.search_stock_list = [];
                this.stock_id = "";
                this.stock_data = res.data;
                this.$emit('updated_kitchen_list',this.stock_data)
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
