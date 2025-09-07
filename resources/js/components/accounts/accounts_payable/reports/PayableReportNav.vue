<template>
    <div class="container ">
        <div class="col-md-12">
            <div>
                <b>Payable Report</b>
            </div>
            <nav class="nav flex-column">
                <button
                 :class="[
                        'supplier_payment_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('supplier_payment_report')"
                >
                  Cummulative Payment Report
                </button>
                <hr />
                 <button
                 :class="[
                        'supplier_credit_note_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('supplier_credit_note_report')"
                >
                Supplier CreditNotes Report  
                </button>
                <hr />
                <button
                 :class="[
                        'supplier_invoice_payment_report' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('supplier_invoice_payment_report')"
                >
                Supplier Payments Report  
                </button>
                <hr />
                  <button
                  :class="[
                        'supplier_debts' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('supplier_debts')"
                >
                Supplier Statement 
                </button>
                <hr />
              
            </nav>
        </div>
    </div>
</template>

<script></script>
