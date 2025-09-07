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

    </style>
</head>

<body>
    <div class="row">
        <div >
            <h3>LOCAL GOODS RECEIVED NOTE(GRN)</h3>
        </div>
    </div>
   <div class="row">
       <table>
           <tr>
               <td>
                <img src="{{ Auth::user()->branch->img_path }}"  height="70" width="100" />
               </td>
               <td>
              <div class="left-cont">  @if($data[0]->branch->business_name)
                <h3 class="company-name" >
                    {{ $data[0]->branch->business_name}}
                </h3>
                @endif
            @if($branch->branch)
            <p>Branch: {{ $branch->branch}}</p>
            @endif

            @if($branch->postal_address)
            <p>Address: {{ $branch->postal_address}}</p>
            @endif
            @if($branch->address)
            <p> {{ $branch->address}}</p>
            @endif
            @if($data[0]->branch->phone_no)
            <p>Tel: {{ $data[0]->branch->phone_no}}</p>
            @endif
        </div>
               </td>
               <td>
              <div class="right-cont">  <p>
                    <h4><b> Goods Received Note</b></h4>
                </p>
                <p> <b>GRN No: </b>{{$data[0]->delivery_no}}</p>
            
                <p> <b>Received Date: </b>{{$data[0]->received_date}}</p>
                <p> <b>Printed On: </b>{{date('m/d/Y h:i:s ', time())}}</p>
            </div>
               </td>
           </tr>
       </table>
   </div>
  
   
   

    <hr>
    <table style="margin-top: 0.4rem;margin-bottom:0.2rem;">
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Received  By: </b></td>
                <td><b>Department</b></td>
                <td><b>Order Date</b></td>
            
            </tr>
        </thead>
        <tr style="background:#eceff1;" class="title_2">
            <td>
                <p > {{$data[0]->user->name}}</p>
            </td>
            <td>
                <p> <b>{{$data[0]->user->department->department}}</p>
            </td>
            <td>
                <p>{{$data[0]->direct_purchase_order->order_date}}</p>
            </td>
            
        </tr>
    </table>

    <table class="table ">
        <thead class="bg-dark">
            <tr class="table-title">
                <td><b>Code</b></td>
                <td><b>Item Name</b></td>
                <td><b>Unit</b></td>
                <td><b>P.price</b></td>
                <td><b>Qty Ordered</b></td>
                <td><b>Qty Shipped</b></td>
                <td><b>Sub T.</b></td>
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @php($total_qty_ordered = 0)
            @php($total_qty_delivered = 0)
            @foreach ($data as $item)
            <tr  class="td-height">
                <td>
                    <p>{{ $item->stock->code}}</p>
                </td>
                <td>
                    <p>{{ $item->stock->name}}</p>
                </td>
                <td>
                    <p>{{ $item->stock->unit->name}}</p>
                </td>
                <td>
                    <p>{{ number_format(($item->unit_price),2)}}</p>
                </td>
                <td>
                    <p>{{ $item->qty_ordered}}</p>
                </td>
                <td>
                    <p>{{ $item->qty_delivered}}</p>
                </td>
                <td>
                    <p> {{number_format(($item->sub_total),2)}}</p>
                </td>
             
                @php($total += $item->sub_total)
                @php($total_qty_ordered += $item->qty_ordered)
            </tr>
            @endforeach
            <tr>
                <td colspan="5">
                    <p> <b>  QTY ORDERED</b></p>
                </td>
                <td>
                    <p> <b> {{number_format($total_qty_ordered,2) }}</b></p>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <p> <b> ORDER TOTAL</b></p>
                </td>
                <td>
                    <p> <b> {{number_format($total,2) }}</b></p>
                </td>
            </tr>
           
            
          
        </tbody>
    </table>
    <div class="row">
        <div class="col-xs-4">
            <b>Received By</b><br><br><br>
            {{$data[0]->user->name}}
        </div>

        <div class="col-xs-4">
            <b>Signature </b><br><br><br>
            ------------------------------------------
        </div>

        <div class="col-xs-4">
            <b>Date </b><br><br><br>
            -------------------------------------------
        </div>
    </div>
            
</body>

</html>