<template>
    <div>
          <span class="pdf-header">
                        <h2>WEAVER EMPLOYEES</h2>

                        <div>
                         
                        </div>
                    </span>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Department</th>
                                <th scope="col">Employee Type</th>
                                <th scope="col">Salary</th>
                                <th scope="col">NHIF</th>
                                <th scope="col">NSSF</th>
                                <th scope="col">PAYE</th>
                                <th scope="col">House Allowance</th>
                                <th scope="col">Other Deductions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in pdf_data"
                                :key="i"
                            >
                                <td>
                                
                                        {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                    
                                </td>
                                <td>{{ data.department.department }}</td>
                                <td>{{ data.employee_type.name }}</td>
                                <td>{{ cashFormatter(data.salary) }}</td>
                                <td>{{ cashFormatter(data.nhif) }}</td>
                                <td>{{ cashFormatter(data.nssf) }}</td>
                                <td>{{ cashFormatter(data.paye) }}</td>
                                <td>
                                    {{ cashFormatter(data.house_allowance) }}
                                </td>
                                <td>
                                    {{ cashFormatter(data.other_deductions) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

    </div>
</template>
<script>


export default {
    props:['pdf_data'],
    
}
</script>
