<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b> Sales Report</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'stock_conversion' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('stock_conversion')"
                >
                  Convert Stock
                </button>
                <hr />
                  <button
                    :class="[
                        'stock_conversion_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('stock_conversion_report')"
                >
                 Grouped Report
                </button>
                <hr />
              
            </nav>
        </div>
    </div>
</template>

<script></script>
<style scoped>
button{
    font-size: 11px !important;
}
</style>