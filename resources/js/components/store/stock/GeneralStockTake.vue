<template>
    <div class="row">
        <div class="col-9"></div>
        <div class="col-11">
            <div class="form-group">
                <b>{{ stocktake_form_data.name }}</b>
            </div>
        </div>
        <div class="col-11">
            <div class="form-group d-flex flex-column">
                <label for="">Stock Category</label>

                <Select v-model="form_data.type">
                    <Option
                        v-for="(item, i) in stock_type_array"
                        :value="item.value"
                        :key="i"
                        >{{ item.name }}</Option
                    >
                </Select>
            </div>
        </div>
        <div class="col-11">
            <div class="form-group">
                <input
                    type="number"
                    autocomplete="dfdfd-ffdfd-fd"
                    placeholder="Qty "
                    v-model="form_data.qty"
                    class="form-control"
                />
            </div>
        </div>

        <div class="col-9 d-flex justify-content-center">
            <button
                v-if="isUpdatePermitted"
                class="btn btn-secondary btn-sm mr-2"
                type="secondary"
                @click="submitData(1)"
            >
                Add Stock
            </button>
            <button
                v-if="isUpdatePermitted"
                class="btn btn-warning btn-sm"
                type="secondary"
                @click="submitData(0)"
            >
                Deduct Stock
            </button>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    props: ["stocktake_form_data"],
    data() {
        return {
            stock_type_array: [
                { name: "Main Store", value: "main_store" },
                { value: "other_depts", name: "Other  Depts" }
            ],
            form_data: {
                id: 0,
                type: "",
                qty: 0
            }
        };
    },
    mounted() {
        this.form_data.id = this.stocktake_form_data.id;
    },
    methods: {
        async submitData(stock_type) {
            this.showLoader();
            var res = null;
            if (stock_type == 0) {
                res = await this.callApi(
                    "put",
                    "data/stock/deduct_stock",
                    this.form_data
                );
            }
            if (stock_type == 1) {
                res = await this.callApi(
                    "put",
                    "data/stock/stock_take",
                    this.form_data
                );
            }

            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = 0)
                );
                this.$emit("dismss-stocktake");
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        }
    }
};
</script>
