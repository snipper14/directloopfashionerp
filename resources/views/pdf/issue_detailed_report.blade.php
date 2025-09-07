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
            <h3>INTERNAL ISSUE REPORT</h3>
        </div>
    </div>

    <hr>
  

    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Date</b></td>
                <td><b>Code</b></td>
              
                <td><b>Item Name</b></td>
                <td><b>Unit</b></td>
                <td><b>Qty Issued </b></td>
                <td><b>Qty Received </b></td>
                <td><b>Purchase Price</b></td>

              
                <td><b>Total</b></td>
          
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @php($total_qty = 0)
            @foreach ($data as $item)
            <tr>
                <td>
                    <p class="item">{{ $item->date_issued}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->stock->code}}</p>
                </td>
                
                <td>
                    <p class="item">{{ $item->stock->name}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->unit}}</p>
                </td>
                <td>
                    <p class="item">{{number_format($item->qty_issued,2)}}</p>
                </td>
                <td>
                    <p class="item">{{number_format($item->final_qty,2)}}</p>
                </td>
                <td>
                    <p class="item">{{number_format($item->purchase_price,2)}}</p>
                </td>

                
                <td>
                    <p class="item">{{ number_format(($item->row_total),2)}}</p>
                </td>
             
                
            </tr>
            @php($total += $item->row_total)
            @php($total_qty += $item->qty_issued)
            @endforeach
            <tr>
                <td colspan="5">
                    <b>  TOTAL QTY ISSUED</b>
                </td>
                <td>
                    <b> {{number_format($total_qty,2) }}</b>
                </td>
            </tr>
            
            <tr>
                <td colspan="5">
                    <b>  TOTAL VALUE ISSUED</b>
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