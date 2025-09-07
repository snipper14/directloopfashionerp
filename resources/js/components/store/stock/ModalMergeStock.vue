<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Merge {{ merge_details_data.name }} With
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <Select
                                        @change="onChange($event)"
                                        v-model="form_data.deleted_stock_id"
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
                                <Button
                                    title="Update"
                                    type="primary"
                                    :loading="isSending"
                                    :disabled="isSending"
                                    v-if="isUpdatePermitted"
                                    @click="mergeStock"
                                    >Merge</Button
                                >
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
    props: ["merge_details_data"],
    data() {
        return {
            isSending: false,
            search_stock_list: [],
            form_data: {
                deleted_stock_id: null,
                stock_id: this.merge_details_data.id,
            },
        };
    },
    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                let index = this.search_stock_list.findIndex(
                    (cp) => cp.value == this.form_data.stock_id
                );
            }, 500),
        },
    },
    mounted() {
        console.log("Component mounted.");
    },
    methods: {
        async mergeStock() {
            
            
            const proceed = await this.confirmDialogue(
                "Are you sure you want to delete this selected item"
            );
            if (proceed) {
                this.isSending = true;
                const res = await this.callApi(
                    'POST',"data/merge_stock/create",
                    this.form_data
                );
                this.isSending = false;
                if (res.status == 200) {
                    this.successNotif("Merged");
                    this.$emit("dismiss-modal");
                } else {
                    this.form_error(res);
                }
            } else {
                this.s("Dismissed");
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
