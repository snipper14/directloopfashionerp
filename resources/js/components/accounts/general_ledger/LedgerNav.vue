<template>
    <div class="container">
        <div class="col-md-12">
            <nav class="nav flex-column">
                <button
                    :class="[
                        'create_ledgers' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('create_ledgers')"
                >
                    Create Ledgers
                </button>
                <hr />
                <button
                    :class="[
                        'general_ledger' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('general_ledger')"
                >
                    Journals
                </button>
                <hr />
                <button
                    :class="[
                        'create_accounts' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('create_accounts')"
                >
                   Chart of Accounts
                </button>

              
               
                <hr />
                <button
                    :class="[
                        'accounts_statement' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('accounts_statement')"
                >
                    Accounts Statement
                </button>
                <hr />
                <button
                    :class="[
                        'trial_balance' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('trial_balance')"
                >
                    Trial Balance
                </button>

                <hr />
                <button
                    :class="[
                        'profit_loss' === this.$route.name ? 'nav-active' : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('profit_loss')"
                >
                    Profit $ Loss
                </button>
                <hr />
                <button
                    :class="[
                        'balance_sheet' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('balance_sheet')"
                >
                    Balance Sheet
                </button>
                <hr />

                <button
                    :class="[
                        'receivable_ageing_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('receivable_ageing_report')"
                >
                    Receivable Ageing Report
                </button>

                <hr />
                <button
                    :class="[
                        'payable_ageing_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('payable_ageing_report')"
                >
                    Payable Ageing Report
                </button>

                <hr />
                  <button
                    :class="[
                        'vat_report' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-primary btn-sm"
                    @click="$router.push('vat_report')"
                >
                    VAT Report
                </button>

                <hr />
            </nav>
        </div>
    </div>
</template>

<script></script>
