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
            <img src="{{ Auth::user()->branch->img_path }}"  height="70" width="100" /></div>

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
            <h3>STOCK MOVEMENT LOGS REPORT</h3>
        </div>
    </div>
    <hr>
<div class="row">
    <div class="col-xs-12 ">
        DATE FROM {{$from}} TO  {{$to}}
    </div>
</div>
 
  

    <table class="table ">
        <thead class="bg-dark">
            <tr>
                <td><b>Code</b></td>
           
                <td><b>Item Name</b></td>
                <td><b>Purchase P</b></td>
                <td><b>Opening Stock </b></td>
                <td><b>Added GRN </b></td>
                <td><b>Issue Qty</b></td>

              
                <td><b>Returns</b></td>
                <td><b>Created at</b></td>
            </tr>
        </thead>
        <tbody>
        
            @foreach ($data as $item)
            <tr>
                <td>
                    <p class="item">{{ $item->stock->code}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->stock->product_name}}</p>
                </td>
                
               
                <td>
                    <p class="item">{{ number_format($item->stock->purchase_price,2)}}</p>
                </td>
                <td> 
                    <p class="item">{{ $item->opening_stock}}</p>
                </td>
                <td>
                    <p class="item">{{$item->grn_qty}}</p>
                </td>
                <td>
                    <p class="item">{{$item->issued_qty}}</p>
                </td>

                
                <td>
                    <p class="item">{{ $item->stock_returns}}</p>
                </td>
                    
                <td>
                    <p class="item">{{ $item->created_at}}</p>
                </td>
          
            </tr>
        
            @endforeach
    
            
     
            
          
        </tbody>
    </table>
     

  
                  
              
           
               
       </table>
</body>

</html>