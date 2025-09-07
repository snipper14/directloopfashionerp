<template>
    <section>
        <center>
            <ul class="menu-category-list">
                <li
                    :data-filter="'.' + value.category"
                    v-for="(value, i) in menus_data"
                    :key="i"
                    @click="scrollTo(value.category_id)"
                >
                    <a class="nav-link " :href="'#scroll-'+value.category_id">
                        {{ value.category.toUpperCase() }}</a
                    >
                </li>
            </ul>
        </center>
    </section>
</template>

<script>
export default {
    props: ["menus_data"],
    data() {
        return {
        
        };
    },
    mounted() {},
    methods: {
        scrollTo(val){
            this.$emit('scrolTo',val)
        }
    },
};
</script>
<style scoped>
.menu-category-list {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    background-color: #333333;
}

.menu-category-list li {
    display: inline-block;
}
.menu-category-list a {
    display: block;
    color: white;
    text-align: center;
    padding: 16px;
    text-decoration: none;
}
</style>
