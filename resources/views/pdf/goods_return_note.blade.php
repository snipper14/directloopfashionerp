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
                <h3> Goods Return Note</h3>
            </p>
            <p> <b>Note   No: </b>{{$data[0]->return_code}}</p>
            <p> <b>Order No: </b>{{$data[0]->order_no}}</p>
            <p> <b>Created At: </b>{{$data[0]->return_date}}</p>
        </div>
    </div>

    <div class="row bg-dark" style="margin-top:2rem;text:center;">
        <div class="col-xs-4">
            <h4 style="float: left;margin-left:0.6rem;text:center;"><b> Vendor</b></h4>
        </div>
       
    </div>
    <div style="background:#eceff1;">
    <div class="row" style="padding-top:2rem;padding-left:1rem">
           
        <div class="col-xs-4 left-cont" >

            <p style="margin-left:0.6rem"><b>
                    <h4>{{ $data[0]->supplier->company_name}}</h4>
                </b></p>

            @if($data[0]->supplier->company_phone)
            <p>Phone: {{ $data[0]->supplier->company_phone}} </p>
            @endif
            @if($data[0]->supplier->company_email)
            <p>Email: {{ $data[0]->supplier->company_email}} </p>
            @endif
            @if($data[0]->supplier->country)
            <p>Country: {{ $data[0]->supplier->country}}/{{ $data[0]->supplier->city}} </p>
            @endif
            @if($data[0]->supplier->postal_address)
            <p>{{ $data[0]->supplier->postal_address}} / {{ $data[0]->supplier->address}} </p>
            @endif

        </div>
        <div class="col-xs-4"></div>
       

    </div>
</div>
    <div class="row">
        <div class="col-xs-12 invoice doc-type">
            <h3>Goods Return Note</h3>
        </div>
    </div>

    <hr>
    <table style="margin-top: 2rem;margin-bottom:2rem;">
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Created  By: </b></td>
                <td><b>Department</b></td>
                <td><b> Date</b></td>
               
                
            </tr>
        </thead>
        <tr style="background:#eceff1; ">
            <td>
                <p class="title_2"> {{$data[0]->user->name}}</p>
            </td>
            <td>
                <p> <b class="title_2">{{$data[0]->user->department->department}}</p>
            </td>
            <td>
                <p class="title_2">{{date('m/d/Y h:i:s ', time())}}</p>
            </td>
           
        </tr>
    </table>

    <table class="table ">
        <thead class="bg-dark">
            <tr>
                <td><b>Code</b></td>
                <td><b>Item Name</b></td>
                <td><b>Details</b></td>
                
               
                <td><b>Qty Returned</b></td>
          
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
                    <p class="item">{{ $item->details}}</p>
                </td>

                <td>
                    <p class="item">{{ $item->qty}}</p>
                </td>
               
             
                
            </tr>
            @endforeach

            
          
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
            
</body>

</html>