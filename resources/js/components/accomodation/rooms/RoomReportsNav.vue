<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>Accommodation Report</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'current_reservations_tabular' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('current_reservations_tabular')"
                >
                    General House Reports
                </button>
                <hr />
                 <button
                    :class="[
                        'daily_accommodation_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('daily_accommodation_report')"
                >
                    Daily Report
                </button>
                <hr />
                  <button
                    :class="[
                        'inhouse_guest_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('inhouse_guest_report')"
                >
                    Inhouse Guest
                </button>
                <hr />
                <button
                    :class="[
                        'fetch_pending_checkout' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('fetch_pending_checkout')"
                >
                    Pending Checkout
                </button>
                <hr />
                 <button
                    :class="[
                        'room_transfer_reports' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('room_transfer_reports')"
                >
                    Transfers
                </button>
                <hr />
                  <button
                    :class="[
                        'credit_note_reports' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('credit_note_reports')"
                >
                    Credit Notes
                </button>
                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
