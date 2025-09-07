<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>Rooms Manager</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'rooms_standard_dashboard' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('rooms_standard_dashboard')"
                >
                    Room Type
                </button>
                <hr />
                <button
                    :class="[
                        'accomodation_package' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('accomodation_package')"
                >
                    Accomodation Packages
                </button>
                <hr />
                <button
                    :class="[
                        'room_rates' === this.$route.name ? 'nav-active' : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('room_rates')"
                >
                    Rates
                </button>
                <hr />
                <button
                    :class="['rooms' === this.$route.name ? 'nav-active' : '']"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('rooms')"
                >
                    Rooms
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
