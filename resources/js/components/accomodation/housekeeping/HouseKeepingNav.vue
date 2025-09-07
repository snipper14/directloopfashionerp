<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <h6><b>House Keeping</b></h6>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'housekeeping_dashboard' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('housekeeping_dashboard')"
                >
                    House Keeping
                </button>
                <hr />
                <button
                    :class="[
                        'housekeeping_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('housekeeping_report')"
                >
                    House Keeping Report
                </button>
                <hr />
                <button
                    :class="[
                        'maintain_room' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('maintain_room')"
                >
                    House Maintenance
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
