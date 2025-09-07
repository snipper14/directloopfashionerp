<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'preorders' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('preorders')"
                >
                    Add Pre-Order
                </button>
                <hr />
                 <button
                    :class="[
                        'new_preorders' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('new_preorders')"
                >
                     Pre-Orders
                </button>
                <hr />
                   <button
                    :class="[
                        'ready_preorders' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('ready_preorders')"
                >
                    Completed Orders
                </button>
                <hr />
              
            </nav>
        </div>
    </div>
</template>

<script></script>
