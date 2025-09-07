<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'location' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('location')"
                >
                  Location
                </button>
                <hr/>
                 <button
                    :class="[
                        'shelf' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('shelf')"
                >
                  Shelves
                </button>
               
               
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {}
};
</script>
<style scoped>
.flex-column button {
    border-bottom: 2px solid #fff !important;
}
</style>
