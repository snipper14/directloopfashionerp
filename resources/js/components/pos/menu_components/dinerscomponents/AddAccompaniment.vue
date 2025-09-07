<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h2><b class="dark-blue">Accompaniments</b></h2>
                    </div>

                    <div class="card-body">
                        <div class="col-10">
                            <div class="row" id="order-menu">
                                <div
                                    class="col-md-3 col-sm-6 card"
                                    style="background: #558b2f"
                                    @click="addAccompaniment(false)"
                                >
                                    <div
                                        class="card-body d-flex flex-column align-items-start"
                                    >
                                        <b class="menu-product"> NONE</b>
                                    </div>
                                </div>
                                <div
                                    class="col-md-3 col-sm-6 card menu-item"
                                    v-for="(value, i) in accomp_data.accompaniment"
                                    :key="i"
                                    @click="addAccompaniment(value)"
                                >
                                    <div
                                        class="card-body d-flex flex-column align-items-start"
                                    >
                                        <b class="menu-product">
                                            {{
                                                value.accompaniment.product_name
                                            }}</b
                                        >
                                    </div>
                                </div>
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
    props: ["accomp_data"],
    data() {
        return {};
    },
    mounted() {
       
    },
    methods: {
        addAccompaniment(data) {
            this.$emit("send-accompaniment", {"data":data,"accomp_data":this.accomp_data});
        },
    },
};
</script>
<style scoped>
.menu-product {
    color: #00227b !important;
}
.dark-blue {
    color: #00227b !important;
}
.menu-item {
    background: #f57f17 !important;
    padding: 2px !important;
}
</style>
