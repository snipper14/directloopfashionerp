<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .receipt {
            width: 100%;
            max-width: 400px;
            margin: auto;
            border: 1px solid #000;
            padding: 10px;
        }
        .header p {
           margin: 0 !important;
            padding:  0 !important; /* Reduced padding */
        }
        .header, .footer, .thank-you {
            text-align: center;
        }
        .line {
            border-top: 1px solid #000;
            margin: 10px 0;
        }
        .details, .items, .totals {
            margin: 10px 0;
        }
        .details p {
            margin: 2px 0;
            line-height: 1.5;
        }
        .items, .totals {
            width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
        }
        .items th, .items td, {
            text-align: left;
            padding: 4px 2px;
            border-bottom: 1px solid #000;
        }
        .items th, .totals th {
            font-weight: bold;
            text-align: left;
        }
        .items th, .items td {
            width: 20%; /* Adjust widths to fit content */
        }
        .items #qty, .items #qty {
            width: 5% !important; /* Adjust widths to fit content */
        }
        .totals th, .totals td {
            width: 80%;
        }
        .totals {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>{{$res->branch->business_name}}</h2>
            <p>{{$res->branch->address}}</p>
            <p>{{$res->branch->postal_address}}</p>
         
          
            <p>Phone: {{$res->branch->phone_no}}</p>
            <p> {{$res->branch->kra_pin}}</p>
            <p>Sales Receipt</p>
        </div>
        
        <div class="line"></div>
        
        <div class="details">
            <p>Date: {{$saleDate}}</p>
            <p>{{'Receipt No: ' . $res->order_no  }}</p>
            <p>Customer Name: {{$customer_info->customer->company_name}}</p>
            <p>Customer PIN: {{$customer_info->customer->pin}}</p>
            <p>Served By: {{$res->user->name }}</p>
        </div>
        
        <div class="line"></div>
        
        <table class="items">
            <thead>
                <tr>
                    <th>HS CODE</th>
                    <th id="qty">QTY</th>
                    <th >DESCRIPTION</th>
                    <th style="padding-left:2px">UNIT PRICE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php($total = 0)
                @php($total_tax = 0)
                @foreach ($res_order as $item)
                <tr>
                    <td>{{ $item->stock->hs_code}}</td>
                    <td id="qty">{{$item->qty}}</td>
                    <td>{{ $item->stock->product_name}}</td>
                    <td   style="padding-left:2px">{{ number_format($item->price, 2) }}</td>
                    <td>{{ number_format($item->row_total, 2) }}</td>
                 
                </tr>
                @php($total += $item->row_total)
                
                @endforeach
                <!-- Add more items as needed -->
            </tbody>
        </table>
        
        <div class="line"></div>
        
        <table class="totals" >
            <tbody>
                <tr>
                    <td><b>Taxable Amount:</b></td>
                    <td></td>
                    <td>{{number_format(($total-$request->total_vat),2)}}</td>
                </tr>
                <tr>
                    <td><b> VAT:</b></td>
                    <td></td>
                    <td>{{number_format($request->total_vat,2)}}</td>
                </tr>
                <tr>
                    <td><b>Total :</b></td>
                    <td></td>
                    <td>{{number_format($total,2)}}</td>
                </tr>
              
             
                <tr>
                     @if ($request->card_pay > 0)
                    <th>Card:</th>
                    <td>{{number_format($request->card_pay,2)}}</td>
                    @endif
                    @if ($request->cash_pay > 0)
                    <th>Cash:</th>
                    <td>{{number_format($request->cash_pay,2)}}</td>
                    @endif
                    @if ($request->mpesa_pay > 0)
                    <th>M-PESA:</th>
                    <td>{{number_format($request->mpesa_pay,2)}}</td>
                    @endif
            
                   
                </tr>
            </tbody>
        </table>
        
        <div class="line"></div>
        
        <div class="footer">
            <p>{{$res->branch->footer_text}}</p>
            
        </div>
        
        <div class="thank-you">
            <p>T H A N K   Y O U</p>
        </div>
    </div>
</body>
</html>
