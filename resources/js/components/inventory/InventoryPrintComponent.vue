<template>
  <div>
    <v-btn color="primary" @click="printTable">Print Inventory</v-btn>

    <div id="printable-inventory" style="display: none">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Code</th>
            <th>Item Name</th>
            <th>Unit</th>
            <th>Branch</th>
            <th>Location</th>
            <th>Shelf</th>
            <th>Purchase Price</th>
            <th>Selling Price</th>
            <th>Reorder Qty</th>
            <th>Qty</th>
            <th>Value on CP</th>
            <th>Value on SP</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(data, i) in inventory" :key="i">
            <td>{{ data.stock.code }}</td>
            <td>{{ data.stock.name }}</td>
            <td>{{ data.stock.unit.name }}</td>
            <td>{{ data.branch.branch }}</td>
            <td>{{ data.department.department }}</td>
            <td>{{ data.stock.branchshelves ? data.stock.branchshelves.shelf.name : 'No Shelf' }}</td>
            <td>{{ cashFormatter(data.stock.purchase_price) }}</td>
            <td>{{ cashFormatter(data.stock.selling_price) }}</td>
            <td>{{ data.stock.reorder_qty }}</td>
            <td>{{ data.qty }}</td>
            <td>{{ cashFormatter(data.total_purchase_value) }}</td>
            <td>{{ cashFormatter(data.total_selling_value) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import printJS from 'print-js';
export default {
  props: ['inventory'],
  methods: {
 
    printTable() {
      const printable = document.getElementById('printable-inventory');
      printable.style.display = 'block';

      printJS({
        printable: 'printable-inventory',
        type: 'html',
        css: ['https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'],
      });

      printable.style.display = 'none';
    },
  },
};
</script>
