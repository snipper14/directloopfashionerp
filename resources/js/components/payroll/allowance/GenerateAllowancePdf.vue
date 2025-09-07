<template>
    <div>
        <span class="pdf-header">
            <h2>WEAVER MANAGEMENT SYSTEM</h2>

            <div></div>
        </span>
             <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr> 
                                    <th scope="col">Employee</th>
                                    <th scope="col">Allowance Amount</th>
                                    <th scope="col"> Allowance Type</th>
                                  
                                   
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

                                  
                                    <td>{{cashFormatter( data.amount) }}</td>
                                    
                                    <td>{{ data.allowance_type }}</td>
                                    
                                   
                                    
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
