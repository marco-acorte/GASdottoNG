<?php $summary = $order->calculateSummary() ?>

<form class="form-horizontal main-form order-editor" method="PUT" action="{{ route('orders.update', $order->id) }}">
    <input type="hidden" name="order_id" value="{{ $order->id }}" />

    <div class="row">
        <div class="col-md-6 col-lg-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => _i('Fornitore')])
            @include('commons.staticstringfield', ['obj' => $order, 'name' => 'internal_number', 'label' => _i('Numero')])

            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.textfield', ['obj' => $order, 'name' => 'comment', 'label' => _i('Commento')])
                @include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => _i('Data Apertura'), 'mandatory' => true])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'end',
                    'label' => _i('Data Chiusura'),
                    'mandatory' => true,
                    'extras' => [
                        'data-enforce-after' => '.date[name=start]'
                    ]
                ])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'shipping',
                    'label' => _i('Data Consegna'),
                    'extras' => [
                        'data-enforce-after' => '.date[name=end]'
                    ]
                ])

                @if($currentgas->hasFeature('shipping_places') && $order->aggregate->orders()->count() == 1)
                    <!--
                        Se l'ordine è aggregato ad altri, i luoghi di consegna
                        si editano una volta per tutti direttamente nel pannello
                        dell'aggregato
                    -->
                    @include('commons.selectobjfield', [
                        'obj' => $order,
                        'name' => 'deliveries',
                        'label' => _i('Luoghi di Consegna'),
                        'mandatory' => false,
                        'objects' => $currentgas->deliveries,
                        'multiple_select' => true,
                        'help_text' => _i("Tenere premuto Ctrl per selezionare più luoghi di consegna. Se nessun luogo viene selezionato, l'ordine sarà visibile a tutti.")
                    ])
                @endif

                @if($currentgas->booking_contacts == 'manual')
                    <?php

                    $contactable_users = new Illuminate\Support\Collection();

                    foreach(App\Role::rolesByClass('App\Supplier') as $role) {
                        $contactable_users = $contactable_users->merge($role->usersByTarget($order->supplier));
                    }

                    $contactable_users = $contactable_users->sortBy('surname')->unique();

                    ?>

                    @include('commons.selectobjfield', [
                        'obj' => $order,
                        'name' => 'users',
                        'label' => _i('Contatti'),
                        'mandatory' => false,
                        'objects' => $contactable_users,
                        'multiple_select' => true,
                        'help_text' => _i("Tenere premuto Ctrl per selezionare più utenti. I contatti degli utenti selezionati saranno mostrati nel pannello delle prenotazioni.")
                    ])
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    @include('commons.boolfield', ['obj' => $order, 'name' => 'keep_open_packages', 'label' => _i('Forza completamento confezioni')])
                @endif
            @else
                @if(!empty($order->comment))
                    @include('commons.staticstringfield', ['obj' => $order, 'name' => 'comment', 'label' => _i('Commento')])
                @endif

                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => _i('Data Apertura')])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => _i('Data Chiusura')])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => _i('Data Consegna')])

                @if($order->deliveries()->count() != 0 && $order->aggregate->orders()->count() == 1)
                    @include('commons.staticobjectslistfield', ['obj' => $order, 'name' => 'deliveries', 'label' => _i('Luoghi di Consegna')])
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    @include('commons.staticboolfield', ['obj' => $order, 'name' => 'keep_open_packages', 'label' => _i('Forza completamento confezioni')])
                @endif
            @endif

            @include('commons.orderstatus', ['order' => $order])
        </div>
        <div class="col-md-6 col-lg-4">
            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.percentagefield', [
                    'obj' => $order,
                    'name' => 'discount',
                    'label' => _i('Sconto Globale'),
                    'help_text' => $order->products()->where('discount', '!=', 0)->count() ? _i('Alcuni prodotti in questo ordine hanno un proprio sconto, che verrà sommato allo sconto globale.') : ''
                ])
                @include('commons.percentagefield', [
                    'obj' => $order,
                    'name' => 'transport',
                    'label' => _i('Spese Trasporto'),
                    'help_text' => $order->products()->where('transport', '!=', 0)->count() ? _i('Alcuni prodotti in questo ordine hanno un proprio costo di trasporto, che verrà sommato al costo di trasporto globale.') : ''
                ])
            @else
                @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'discount', 'label' => _i('Sconto Globale')])
                @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'transport', 'label' => _i('Spese Trasporto')])
            @endif

            @if(Gate::check('movements.admin', $currentgas) || Gate::check('supplier.movements', $order->supplier))
                @include('commons.movementfield', [
                    'obj' => $order->payment,
                    'name' => 'payment_id',
                    'label' => _i('Pagamento'),
                    'default' => \App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered + $summary->transport_delivered),
                    'to_modal' => [
                        'amount_editable' => true
                    ]
                ])
            @else
                @include('commons.staticmovementfield', [
                    'obj' => $order->payment,
                    'label' => 'Pagamento'
                ])
            @endif
        </div>
        <div class="col-md-6 col-lg-4">
            @include('order.files', ['order' => $order])
        </div>
    </div>

    <hr>

    @include('order.summary', ['order' => $order, 'summary' => $summary])
    @include('order.annotations', ['order' => $order, 'summary' => $summary])

    @include('commons.formbuttons', [
        'no_delete' => $order->isActive() == false,
        'left_buttons' => [
            (object) [
                'label' => _i('Esporta'),
                'url' => $order->exportableURL(),
                'class' => ''
            ]
        ]
    ])
</form>
