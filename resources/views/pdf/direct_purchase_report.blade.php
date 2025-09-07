<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title></title>
    <!-- Latest compiled and minified CSS -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/custom.css') }}">


    <style>
.profile-title{
font-size: 1rem !important;
}
    </style>
</head>

<body>
    <div class="row">

        <div class="col-xs-3" style="">
            <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" /></div>

        <div class="col-xs-8">
           
                @if($data[0]->branch->business_name)
                <p class="company-name" >
                    {{ $data[0]->branch->business_name}}
                </p>
                @endif
                @if($data[0]->branch->branch)
                <b >
                 Branch:   {{ $data[0]->branch->branch}}
                </b>
                @endif
                @if($data[0]->branch->address)
                <b >
                 Address:   {{ $data[0]->branch->address}}
                </b>
                @endif
                @if($data[0]->branch->phone_no)
                <b >
                 Phone:   {{ $data[0]->branch->phone_no}}
                </b>
                @endif

          

        </div>
    </div>
  

   
    <div style="background:#eceff1;">
       {{date('F d Y')}}  
</div>
    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>DIRECT PURCHASE REPORT</h3>
        </div>
    </div>

    <hr>
  

    <table class="table ">
        <thead class="bg-dark">
            <tr>
                <td><b>R.Date</b></td>
                <td><b>Code</b></td>
              
                <td><b>Item Name</b></td>
                <td><b>Unit</b></td>
                <td><b>Delivery no.</b></td>
                <td><b>Qty Order </b></td>
                <td><b>Qty Delivered </b></td>
                <td><b>Initial Stock </b></td>
                <td><b>Purchase Price</b></td>

              
                <td><b>Row Total</b></td>
          
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @php($total_qty = 0)
            @foreach ($data as $item)
            <tr>
                <td>
                    <p class="item">{{ $item->received_date}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->stock->code}}</p>
                </td>
                
                <td>
                    <p class="item">{{ $item->stock->name}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->stock->unit->name}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->delivery_no}}</p>
                </td>
                <td>
                    <p class="item">{{$item->qty_ordered}}</p>
                </td>
                <td>
                    <p class="item">{{$item->qty_delivered}}</p>
                </td>
                <td>
                    <p class="item">{{$item->opening_stock}}</p>
                </td>
               
                <td>
                    <p class="item">{{ number_format(($item->unit_price),2)}}</p>
                </td>
                <td>
                    <p class="item">{{ number_format(($item->sub_total),2)}}</p>
                </td>
             
                
            </tr>
            @php($total += $item->sub_total)
            @php($total_qty += $item->qty_delivered)
            @endforeach
            <tr>
                <td colspan="5">
                    <b>  TOTAL QTY DELIVERED</b>
                </td>
                <td>
                    <b> {{number_format($total_qty,2) }}</b>
                </td>
            </tr>
            
            <tr>
                <td colspan="5">
                    <b>  TOTAL VALUE DELIVERED</b>
                </td>
                <td>
                    <b> {{number_format($total,2) }}</b>
                </td>
            </tr>
            
          
        </tbody>
    </table>
     

  
                  
              
           
               
       </table>
</body>

</html>