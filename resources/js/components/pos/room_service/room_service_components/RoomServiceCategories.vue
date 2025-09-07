<template>
    <div class="container">
        <div class="card" v-if="active.dashboard">
            <div class="card-body">
                <div class="row">
                    <div
                        class="col-3  card categories-card"
                        @click="categoryChange(value)"
                        v-for="(value, i) in product_category_data"
                        :key="i"
                    >
                        <b>{{ value.name }}</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["product_category_data"],
    data() {
        return {
            active: {
                dashboard: true
            }
        };
    },
    mounted() {},
    methods: {
        categoryChange(val) {
          
               this.$emit("categoryChange", val);
        }
    },
    computed: {}
};
</script>
<style scoped>
.categories-card {
    min-height: 100px !important;
    text-align: center !important;
    background: #bcaaa4 !important;
}
.categories-card > b {
    margin: auto;
    color: blue;
}
.card-0 {
    background: #7c8500 !important;
}
.card-1 {
    background: #bcaaa4 !important;
}
.card-2 {
    background: #4db6ac !important;
}
.card-3 {
    background: #c0ca33 !important;
}
</style>
