<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b> </b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'reconcialiation' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('reconcialiation')"
                >
                    New Reconciliation 
                </button>
                <hr />
                <button
                    :class="[
                        'recons_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('recons_report')"
                >
                     Reconciliation Report.
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
