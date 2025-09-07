<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'promos' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('promos')"
                >
                   Add
                </button>
                <hr />
                <button
                    :class="[
                        'manage_promos' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('manage_promos')"
                >
                     Manage Promos
                </button>
                <hr />
                
            </nav>
        </div>
    </div>
</template>

<script></script>
