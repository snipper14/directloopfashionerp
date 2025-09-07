<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>POS SETTINGS</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'pos_settings' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('pos_settings')"
                >
                     Void Options
                </button>
                <hr />
                <button
                    :class="[
                        'archived_reports' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('archived_reports')"
                >
                    Achived Sales
                </button>
                <hr />
                 <button
                    :class="[
                        'archived_items_reports' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('archived_items_reports')"
                >
                    Achived Sales Items
                </button>
                <hr />
                  <button
                    :class="[
                        'archived_grouped_items_reports' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('archived_grouped_items_reports')"
                >
                    Achived Grouped Items
                </button>
                <hr />
              
            </nav>
        </div>
    </div>
</template>

<script></script>
