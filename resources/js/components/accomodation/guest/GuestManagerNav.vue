<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <center><b>Guest Manager</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'guest_manager' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('guest_manager')"
                >
                   Guests
                </button>
                <hr />
                 <button
                    :class="[
                        'visitor_company' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('visitor_company')"
                >
                    Company
                </button>
                <hr />
              
                
            </nav>
        </div>
    </div>
</template>

<script></script>
