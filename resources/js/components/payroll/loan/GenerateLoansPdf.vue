<template>
    <div>
        <span class="pdf-header">
            <h2>WEAVER MANAGEMENT SYSTEM</h2>

            <div></div>
        </span>
        <table class="table table-sm table-striped table-bordered">
            <thead>
                <tr>
                    <th scope="col">Employee</th>

                    <th scope="col">Loan Date</th>

                    <th scope="col">Loan Amount</th>
                    <th scope="col">Installment Amount</th>
                    <th scope="col">Outstanding Balance</th>

                    <th scope="col">Completion Date</th>
                </tr>
            </thead>
            <tbody>
                <tr class="small-tr" v-for="(data, i) in pdf_data" :key="i">
                    <td>
                        {{
                            data.employee.first_name +
                                " " +
                                data.employee.last_name
                        }}
                    </td>

                    <td>
                        {{ data.loan_date }}
                    </td>
                    <td>{{ cashFormatter(data.loan_amount) }}</td>
                    <td>{{ cashFormatter(data.installment_amount) }}</td>
                    <td>{{ cashFormatter(data.outstanding_balance) }}</td>

                    <td>{{ data.completion_date }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
export default {
    props: ["pdf_data"]
};
</script>
