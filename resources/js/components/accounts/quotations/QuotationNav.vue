<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
            
            </div>
            <nav class="nav flex-column">
                <button
                 :class="[
                        'quotation' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('quotation')"
                >
                  Quotations
                </button>
                <hr />
                 <button
                 :class="[
                        'new_quotation' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('new_quotation')"
                >
                Generate Quote  
                </button>
                <hr />
              
               
              
            </nav>
        </div>
    </div>
</template>

<script></script>
