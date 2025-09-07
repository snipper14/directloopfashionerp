<template>
    <div class="nav-btn-wrapper">
        <button
            :class="[
                'create_product_category' === this.$route.name
                    ? 'nav-active'
                    : ''
            ]"
            @click="$router.push('create_product_category')"
            class="btn btn-primary"
            color="primary"
            dark
        >
            Product Categories
        </button>
        <button
            :class="['create_unit' === this.$route.name ? 'nav-active' : '']"
            @click="$router.push('create_unit')"
            class="btn btn-primary"
            color="primary"
            dark
        >
            Units
        </button>
        <button
            :class="[
                'stock_management' === this.$route.name ? 'nav-active' : ''
            ]"
            @click="$router.push('stock_management')"
            class="btn btn-primary"
            color="primary"
            dark
        >
            Products Management
        </button>
        <button
            :class="[
                'trashed_products' === this.$route.name ? 'nav-active' : ''
            ]"
            @click="$router.push('trashed_products')"
            class="btn btn-primary"
            color="primary"
            dark
        >
            Trashed 
        </button>
          <button
            :class="[
                'price_groups' === this.$route.name ? 'nav-active' : ''
            ]"
            @click="$router.push('price_groups')"
            class="btn btn-primary"
            color="primary"
            dark
        >
            Price Groups
        </button>
    </div>
</template>

<script>
export default {
    mounted() {}
};
</script>
