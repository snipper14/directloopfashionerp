<template>
    <div class="container ">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'production_management' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('/production_management')"
                >
                  Assembly
                </button>
                <hr />

                <button
                    :class="[
                        'disassembly_production' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('/disassembly_production')"
                >
                    Disassembly
                </button>
                <hr />

              
              
            </nav>
        </div>
    </div>
</template>
<script>
export default {
    props: ["id"],
    mounted() {
        console.log("====================================");
        console.log(this.$route.name);
        console.log("====================================");
    }
};
</script>
