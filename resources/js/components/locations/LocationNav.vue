<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>Locations</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'locations' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('locations')"
                >
                    Manage Location
                </button>
                <hr />
                <button
                    :class="['tables' === this.$route.name ? 'nav-active' : '']"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('tables')"
                >
                    Manage Table/s
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
