<template>
    
        <b class="product" 
            >{{data.hs_code}} {{ data.product_name }}
            </b
        >

</template>

<script>
export default {
    props: ["data"],
    data() {
        return {};
    },
    mounted() {
   
    },
};
</script>
<style scoped>
.product {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
   
}
</style>
