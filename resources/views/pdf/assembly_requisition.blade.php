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

        <div class="col-xs-3" style="">
            <img src="{{ Auth::user()->branch->img_path }}" height="70" width="100" /></div>

        <div class="col-xs-8">
           
                @if($branch->business_name)
                <p class="company-name">
                    {{ $branch->business_name}}
                </p>
                @endif

          

        </div>
    </div>
    <div class="row mt-3">


        <div class="col-xs-4 left-cont">
            <h3>Customer</h3>
            <p> {{ $branch->business_name}}</p>
            @if($branch->branch)
            <p>Branch: {{ $branch->branch}}</p>
            @endif

            @if($branch->postal_address)
            <p>Address: {{ $branch->postal_address}}</p>
            @endif
            @if($branch->address)
            <p> {{ $branch->address}}</p>
            @endif

        </div>

        <div class="col-xs-4">

        </div>
        <div class="col-xs-4 right-cont">
            <p>
                <h3> Requisition Form</h3>
            </p>
            <p> <b>Req No: </b>{{$data[0]->req_id}}</p>
           
            <p> <b>Due Date: </b>{{$data[0]->date_due}}</p>
        </div>
    </div>

   
    <div style="background:#eceff1;">
 
</div>
    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>REQUISITION LETTER</h3>
        </div>
    </div>

    <hr>
    <table style="margin-top: 2rem;margin-bottom:2rem;">
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Created  By: </b></td>
                <td><b>Department</b></td>
                <td><b>Due Date</b></td>
              
                
            </tr>
        </thead>
        <tr style="background:#eceff1; ">
            <td>
                <p  class="title_2" > {{$data[0]->user->name}}</p>
            </td>
            <td>
                <p> <b  class="title_2">{{$data[0]->user->department->department}}</p>
            </td>
            <td>
                <p  class="title_2">{{$data[0]->date_due}}</p>
            </td>
            </tr>
    </table>

    <table class="table ">
        <thead class="bg-dark">
            <tr>
                <th scope="col">Code</th>
                <th scope="col">Final Product</th>
                <td>Qty</td>
                <th scope="col">Raw Material</th>
                <th scope="col">Raw Material Qty</th>
                <th scope="col">Raw Material Price</th>

                <th scope="col">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @php($total_qty = 0)
            @foreach ($data as $item)
            <tr>
                <td>
                    <p class="item">{{ $item->stock->code}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->stock->product_name}}</p>
                </td>
                <td>
                    <p class="item">{{ number_format($item->qty,2)}}</p>
                </td>
                <td>
                    <p class="item">{{ $item->ingredient->product_name}}</p>
                </td>
                <td>
                    <p class="item">{{number_format($item->ingredient_qty,2)}}</p>
                </td>

                <td>
                    <p class="item">{{number_format($item->ingredient_price,2)}}</p>
                </td>

               
                <td>
                    <p class="item">{{ number_format(($item->sub_total),2)}}</p>
                </td>
             
                @php($total_qty += $item->ingredient_qty)
                @php($total+= $item->sub_total)
            </tr>
            @endforeach
            <tr>
                <td colspan="6">
                    <p> <b> Total Qty</b></p>
                </td>
                <td>
                    <p><b> {{number_format($total_qty,2) }}</b></p>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <p> <b>  Total Amount</b></p>
                </td>
                <td>
                    <p> <b> {{number_format($total,2) }}</b></p>
                </td>
            </tr>
            
          
        </tbody>
    </table>
     

    <div class="row">
        <div class="col-xs-4">
            <b>Approved By</b><br><br><br>
            -----------------------------------------
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
                  
              
           
               
       </table>
</body>

</html>