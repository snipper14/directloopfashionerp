<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        {{ accomp_data.product_name }}
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div
                                    v-for="(
                                        value, i
                                    ) in accomp_data.accompaniment"
                                    :key="i"
                                >
                                    <input
                                        type="radio"
                                        v-model="form_data.accompaniment_id"
                                        :value="value.accompaniment.id"
                                    />
                                    {{ value.accompaniment.product_name }}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <v-btn
                                    color="#322312"
                                    dark
                                    small
                                    @click="addAccomp()"
                                    ><v-icon>{{ icons.mdiPlusThick }}</v-icon>
                                    Add Item</v-btn
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
import { mdiPlusThick } from "@mdi/js";
export default {
    props: ["accomp_data"],
    data() {
        return {
            accomp_array: null,
            form_data: { accompaniment_id: null },
            selected_data: null,
            icons: {
                mdiPlusThick,
            },
        };
    },
    mounted() {
        this.accomp_array = this.accomp_data.accompaniment;
    },
    methods: {
        addAccomp() {
            this.selected_data = this.accomp_array.find(
                (item) =>
                    item.accompaniment_id == this.form_data.accompaniment_id
            );
            if (!this.selected_data) {
                this.errorNotif("Select an item");
                console.log("SELECT ITEM");
                return;
            }
            this.$emit("accompSelect", this.selected_data);
        },
    },
};
</script>
