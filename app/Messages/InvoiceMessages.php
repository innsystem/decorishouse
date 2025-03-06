<?php

return [
    'email' => [
        'payment_confirmed' => [
            'subject' => 'Pagamento Confirmado!',
            'body' => 'Seu pagamento da fatura N° {{invoice_id}} foi confirmado.',
        ],
        'invoice_reminder' => [
            'subject' => 'Lembrete de Fatura',
            'body' => 'Sua fatura N° {{invoice_id}} vence em {{due_date}}.',
        ],
    ],

    'whatsapp' => [
        'payment_confirmed' => 'Olá {{name}}, seu pagamento da fatura N° {{invoice_id}} foi confirmado!',
        'invoice_reminder' => 'Olá {{name}}, sua fatura N° {{invoice_id}} vence em {{due_date}}. ',
    ],
];
