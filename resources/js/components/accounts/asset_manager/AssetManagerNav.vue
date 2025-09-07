<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
            
            </div>
            <nav class="nav flex-column">
                <button
                 :class="[
                        'asset_manager' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('asset_manager')"
                >
                  Assets
                </button>
                <hr />
                 <button
                 :class="[
                        'asset_category' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('asset_category')"
                >
                Asset Category  
                </button>
                <hr />
                 <button
                 :class="[
                        'asset_allocation' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('asset_allocation')"
                >
                Asset Allocation  
                </button>
                <hr />
                  <button
                 :class="[
                        'revoked_assets' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('revoked_assets')"
                >
                Revoked Allocation  
                </button>
                <hr />
               
                <button
                 :class="[
                        'asset_maintenance_dashboard' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('asset_maintenance_dashboard')"
                >
                Asset Maintenance  
                </button>
                <hr />
               
              
            </nav>
        </div>
    </div>
</template>

<script></script>
