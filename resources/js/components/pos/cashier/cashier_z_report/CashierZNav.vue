<template>
    <div class="container ">
        <div class="col-md-12">
           
            <nav class="nav flex-column">
                <button
                    :class="[
                        'closeshift_z_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('closeshift_z_report')"
                >
                    Cashier Close Shift
                </button>
                <hr />
                <button
                    :class="[
                        'closeshift_z_reporting' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('closeshift_z_reporting')"
                >
                    Balance Report
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {};
    },
    mounted() {}
};
</script>
