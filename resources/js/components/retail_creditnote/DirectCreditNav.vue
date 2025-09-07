<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'directretail_creditnote' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('directretail_creditnote')"
                >
                    Add Direct Credit
                </button>
                <hr />
                <button
                    :class="[
                        'directretail_creditnote_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('directretail_creditnote_report')"
                >
                     Direct Creditnotes
                </button>
                <hr />
                
            </nav>
        </div>
    </div>
</template>

<script></script>
