<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>Inventory</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'inventory_management' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('inventory_management')"
                >
                   Dept Inventory
                </button>
                <hr />
                <button
                    :class="[
                        'update_stock_balance' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('update_stock_balance')"
                >
                    Stocktake
                </button>
                <hr />
                <button
                    :class="[
                        'physical_update_report' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('physical_update_report')"
                >
                    Update Report
                </button>
                  <hr />
                <button
                    :class="[
                        'inventory_logs' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('inventory_logs')"
                >
                    Inventory Logs
                </button>
                <hr />
                <hr/>
                 <button
                    :class="[
                        'bulk_stock_take' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('bulk_stock_take')"
                >
                   Bulk Stocktake
                </button>
                <hr />
                
                   <button
                    :class="[
                        'stock_take_variation' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('stock_take_variation')"
                >
                Stocktake Variation Report
                </button>
                <hr />
                 
            </nav>
                 
          
        </div>
    </div>
</template>

<script></script>
