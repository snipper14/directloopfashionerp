<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><promos-nav /></div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Promos Dashboard</div>

                    <div class="card-body">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lower_limit">Lower Limit</label>
                                <input
                                    type="number"
                                    id="lower_limit"
                                    v-model="promo.lower_limit"
                                    class="form-control"
                                    placeholder="Enter Lower Limit"
                                />
                            </div>

                            <div class="form-group">
                                <label for="upper_limit">Upper Limit</label>
                                <input
                                    type="number"
                                    id="upper_limit"
                                    v-model="promo.upper_limit"
                                    class="form-control"
                                    placeholder="Enter Upper Limit"
                                />
                            </div>

                            <div class="form-group">
                                <label for="discount">Discount</label>
                                <input
                                    type="number"
                                    id="discount"
                                    v-model="promo.discount"
                                    class="form-control"
                                    placeholder="Enter Discount"
                                />
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select
                                    id="status"
                                    v-model="promo.status"
                                    class="form-control"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <v-btn
                                title="WRITE"
                                v-if="isWritePermitted"
                                type="submit"
                                :loading="btn_loading"
                                color="primary"
                                small
                                @click="submitPromo"
                                >Save Promo</v-btn
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PromosNav from "./PromosNav.vue";
export default {
    components: { PromosNav },
    data() {
        return {
            btn_loading: false,
            promo: {
                lower_limit: 0,
                upper_limit: 0,
                discount: 0,
                status: null,
            },
        };
    },
    methods: {
        async submitPromo() {
            console.log("Submitting promo:", this.promo);

            
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/promos/create",
                this.promo
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.promo = {
                    lower_limit: null,
                    upper_limit: null,
                    discount: null,

                    status: null,
                };
                this.successNotif('Saved')
            } else {
                this.form_error(res);
            }
        },
    },
    mounted() {
        console.log("Component mounted.");
    },
};
</script>
