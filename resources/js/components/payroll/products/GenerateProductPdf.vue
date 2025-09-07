<template>
    <div>
          <span class="pdf-header">
                        <h2>WEAVER Management</h2>

                        <div>
                         
                        </div>
                    </span>
                   <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr> 
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Size </th>
                                    <th scope="col">pay_rate</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data,
                                    i) in pdf_data"
                                    :key="i"
                                >
                                    <td>   {{  data.name}}</td>
                                    <td>{{ data.description }}</td>
                                    <td>{{ data.size }}</td>
                                    <td>{{ cashFormatter(data.pay_rate) }}</td>
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
