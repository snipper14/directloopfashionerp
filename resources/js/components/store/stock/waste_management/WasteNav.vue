<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b> Waste</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'waste_management' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('waste_management')"
                >
                    Add Waste
                </button>
                <hr />
                <button
                    :class="[
                        'waste_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('waste_report')"
                >
                    Waste report.
                </button>

              
              
                

                
            </nav>
        </div>
    </div>
</template>

<script></script>
<style scoped>
button {
    font-size: 11px !important;
}
</style>
