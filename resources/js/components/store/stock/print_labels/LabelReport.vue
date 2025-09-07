<template>
    <div class="">
        <div class="row justify-content-center">
            <v-btn color="primary" small @click="$emit('dashboard-active')">Back</v-btn>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Label Dimensions</div>

                    <div class="card-body">
                        <!-- Table to display the label dimensions -->
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Barcode Font Size</th>
                                    <th>Barcode Height</th>
                                    <th>Barcode Width</th>
                                    <th>Wrapper Height</th>
                                    <th>Wrapper Width</th>
                                    <th>Item Description Font Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="dimension in dimensions" :key="dimension.id">
                                    <td>{{ dimension.name }}</td>
                                    <td>{{ dimension.bar_font_size }}</td>
                                    <td>{{ dimension.bar_height }}</td>
                                    <td>{{ dimension.bar_width }}</td>
                                    <td>{{ dimension.wrapper_height }}</td>
                                    <td>{{ dimension.wrapper_width }}</td>
                                    <td>{{ dimension.item_description_fontsize }}</td>
                                      <td>
                                        <!-- Delete button -->
                                        <v-btn small color="red" @click="deleteDimension(dimension.id)">
                                            Delete
                                        </v-btn>
                                         <v-btn color="primary" small @click="editDimension(dimension)">
                                            Edit
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
          <Modal v-model="edit_modal"
            ><edit-label-modal
                v-if="edit_modal"
                :edit_data="edit_data"
                v-on:dismiss-modal="dismissModal"
        /></Modal>
    </div>
</template>

<script>
import EditLabelModal from './EditLabelModal.vue';
export default {
  components: { EditLabelModal },
    data() {
        return {
            edit_data:null,
            edit_modal:false,
            dimensions: [], // Will hold the fetched dimensions
        };
    },
    mounted() {
        this.fetchLabelDimensions(); // Fetch dimensions when the component is mounted
    },
    methods: {
        dismissModal(){
              this.edit_modal=false
               this.fetchLabelDimensions();
        },
        editDimension(data){
            this.edit_data=data
            this.edit_modal=true
        },
           async deleteDimension(id) {
            if(await this.deleteDialogue()){

            
            try {
                const response =await this.callApi('DELETE',`/data/label_dimension/deleteDimension/${id}`,{});// Replace with your API URL
                if (response.status === 200) {
                    // Remove the deleted dimension from the array
                    this.dimensions = this.dimensions.filter(dimension => dimension.id !== id);
                }
            } catch (error) {
                console.error("Error deleting dimension:", error);
            }
            }
        },
        async fetchLabelDimensions() {
            try {
                // Assuming you have an API endpoint for fetching dimensions
                const response = await this.getApi('/data/label_dimension/fetch',{}); // Replace with your actual API URL
                this.dimensions = response.data; // Store the data in dimensions
            } catch (error) {
                console.error("There was an error fetching the label dimensions:", error);
            }
        },
    },
};
</script>

<style scoped>
/* Add any custom styles for your table or card if necessary */
.table {
    margin-top: 20px;
    width: 100%;
}
</style>
