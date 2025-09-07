<php
curl --request PUT \
     --url https://api.digitax.tech/items/clrpv1nyv003ss601zfupcuhb \
     --header 'X-API-Key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZXMiOlsiY2FuLWxvb2t1cC1jb25maWciLCJjYW4tbG9va3VwLWNvZGVzIiwiY2FuLWxvb2t1cC1pdGVtLWNsYXNzaWZpY2F0aW9ucyIsImNhbi1sb29rdXAtY3VzdG9tZXIiLCJjYW4tbG9va3VwLWJyYW5jaCIsImNhbi1sb29rdXAtbm90aWNlcyIsImNhbi1hZGQtYnJhbmNoLWN1c3RvbWVyIiwiY2FuLWFkZC1icmFuY2gtdXNlciIsImNhbi1sb29rdXAtcHJvZHVjdCIsImNhbi1hZGQtc2FsZXMiLCJjYW4tYWRkLXN0b2NrIiwiY2FuLWxvb2t1cC1zdG9jayIsImNhbi1hZGQtcHJvZHVjdCIsImNhbi1hZGQtbWFzdGVyLXN0b2NrIiwiY2FuLWxvb2t1cC1wdXJjaGFzZXMiLCJjYW4tYWRkLXB1cmNoYXNlcyIsImNhbi1sb29rdXAtaW1wb3J0cyIsImNhbi1yZXZpc2UtaW1wb3J0cyIsImNhbi1kZWxldGUtcHJvZHVjdCIsImNhbi11cGRhdGUtcHJvZHVjdCJdLCJhcHBJZCI6ImNsbm85czl6YjAyZnFzNjAxdTVpNzlhMDYiLCJpYXQiOjE3MDI2NjE0MjAsImV4cCI6MjAxODAyMTQyMH0.DpV-rBYfMzWJSjkzoVLlVXi5pr39qw7BfEP-MT2Hzg0' \
     --header 'accept: application/json' \
     --header 'content-type: application/json' \
     --data '
{
  "active": true,
  "item_class_code": "99010000",
  "item_type_code": "2",
  "item_name": "Cough Trihistamine V2",
  "origin_nation_code": "KE",
  "package_unit_code": "CT",
  "quantity_unit_code": "PA",
  "tax_type_code": "A",
  "default_unit_price": 1500
}



//SALES
curl --request POST \
     --url https://api.digitax.tech/sales \
     --header 'X-API-Key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZXMiOlsiY2FuLWxvb2t1cC1jb25maWciLCJjYW4tbG9va3VwLWNvZGVzIiwiY2FuLWxvb2t1cC1pdGVtLWNsYXNzaWZpY2F0aW9ucyIsImNhbi1sb29rdXAtY3VzdG9tZXIiLCJjYW4tbG9va3VwLWJyYW5jaCIsImNhbi1sb29rdXAtbm90aWNlcyIsImNhbi1hZGQtYnJhbmNoLWN1c3RvbWVyIiwiY2FuLWFkZC1icmFuY2gtdXNlciIsImNhbi1sb29rdXAtcHJvZHVjdCIsImNhbi1hZGQtc2FsZXMiLCJjYW4tYWRkLXN0b2NrIiwiY2FuLWxvb2t1cC1zdG9jayIsImNhbi1hZGQtcHJvZHVjdCIsImNhbi1hZGQtbWFzdGVyLXN0b2NrIiwiY2FuLWxvb2t1cC1wdXJjaGFzZXMiLCJjYW4tYWRkLXB1cmNoYXNlcyIsImNhbi1sb29rdXAtaW1wb3J0cyIsImNhbi1yZXZpc2UtaW1wb3J0cyIsImNhbi1kZWxldGUtcHJvZHVjdCIsImNhbi11cGRhdGUtcHJvZHVjdCJdLCJhcHBJZCI6ImNsbm85czl6YjAyZnFzNjAxdTVpNzlhMDYiLCJpYXQiOjE3MDI2NjE0MjAsImV4cCI6MjAxODAyMTQyMH0.DpV-rBYfMzWJSjkzoVLlVXi5pr39qw7BfEP-MT2Hzg0' \
     --header 'accept: application/json' \
     --header 'content-type: application/json' \
     --data '
{
  "customer_pin": "12345678901",
  "customer_name": "JANE",
  "trader_invoice_number": "344554",
  "receipt_type_code": "S",
  "payment_type_code": "07",
  "invoice_status_code": "01",
  "items": [
    {
      "id": "clrpv1nyv003ss601zfupcuhb",
      "quantity": 5,
      "unit_price": 1200,
      "discount_rate": 0,
      "discount_amount": 0
    },
    {
      "id": "clrp7ugl301s5s601vl6spc8l",
      "quantity": 1,
      "unit_price": 390,
      "discount_rate": 0,
      "discount_amount": 0
    }
  ]
}
'
//RESP
{
  "date": "23/01/2024",
  "time": "09:54:09 am",
  "trader_invoice_number": "344554",
  "digitax_id": "clrq03f4y007as60156gst1j4",
  "serial_number": "KRACU0400000044",
  "receipt_number": "560",
  "sale_detail_url": "https://api.digitax.tech/sales/clrq03f4y007as60156gst1j4",
  "customer_name": "JANE",
  "customer_pin": "12345678901",
  "sales_tax_summary": {
    "taxable_amount_a": 6000,
    "taxable_amount_b": 336.21,
    "taxable_amount_c": 0,
    "taxable_amount_d": 0,
    "taxable_amount_e": 0,
    "tax_rate_a": 0,
    "tax_rate_b": 16,
    "tax_rate_c": 0,
    "tax_rate_d": 0,
    "tax_rate_e": 8,
    "tax_amount_a": 0,
    "tax_amount_b": 53.79,
    "tax_amount_c": 0,
    "tax_amount_d": 0,
    "tax_amount_e": 0
  }
}
?>