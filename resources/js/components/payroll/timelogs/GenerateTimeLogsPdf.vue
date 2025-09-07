<template>
    <div>
          <span class="pdf-header">
                        <h2>WEAVER MANAGEMENT SYSTEM </h2>

                        <div>
                         
                        </div>
                    </span>
                     <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Logs Date</th>
                                    <th scope="col">Employee</th>
                                    <th scope="col">Time In</th>
                                    <th scope="col">Time Out</th>
                                     <th scope="col">HRS</th>
                                    <th scope="col">Amount Total</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in pdf_data"
                                    :key="i"
                                >
                                    <td>
                                     
                                            {{ data.logged_on }}
                                        
                                    </td>
                                  
                                    <td>
                                        {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                    </td>
                                    <td>{{ data.time_in }}</td>
                                    <td>{{ data.time_out }}</td>
                                    <td>{{data.no_hours}}</td>
                                    <td>{{ cashFormatter(data.total_amount) }}</td>
                                 
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
