<ul>
    @foreach($payments->payments as $payment)
        <li>
            @include('plugins/bkashpay::detail', compact('payment'))
        </li>
    @endforeach
</ul>
