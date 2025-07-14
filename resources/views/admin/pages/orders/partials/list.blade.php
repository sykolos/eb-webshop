<div class="card">
    <div class="card-header bg-dark text-white">
        <h5>Rendelések</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped"> {{-- "stripped" → helyesen: "striped" --}}
            <thead>
                <tr>
                    <th>#</th>
                    <th>Megrendelő</th>
                    <th>Tételek</th>
                    <th>Összeg</th>                         
                    <th>Dátum</th>                         
                    <th>Státusz</th>                             
                    <th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                <tr>
                    <td>{{$order->id}}</td>
                    <td>
                        @if($order->user && $order->user->user_invoice)
                            {{ $order->user->user_invoice->company_name }}
                        @else
                            <em>N/A</em>
                        @endif
                    </td>
                    <td>{{$order->items->count()}}</td>
                    <td>{{$order->total}}</td>
                    <td>{{\Carbon\Carbon::parse($order->created_at)->format('d/m/Y')}}</td>
                    <td>
                        <span class="badge bg-@if($order->status=='függőben')warning
                            @elseif($order->status=='feldolgozás')info
                            @elseif($order->status=='kiszállítva')success
                            @elseif($order->status=='törölve')warning @endif
                        ">
                        {{$order->status}}
                        </span>
                    </td>
                    <td>
                        <a href="{{route('adminpanel.orders.view',$order->id)}}" class="btn btn-secondary btn-sm">Megtekint</a> 
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>
