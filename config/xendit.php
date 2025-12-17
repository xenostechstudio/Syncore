<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit API Keys
    |--------------------------------------------------------------------------
    |
    | Your Xendit API keys. You can find these in your Xendit Dashboard
    | under Settings > API Keys.
    |
    */

    'secret_key' => env('XENDIT_SECRET_KEY', ''),
    'public_key' => env('XENDIT_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook Verification Token
    |--------------------------------------------------------------------------
    |
    | This token is used to verify that webhook requests are coming from Xendit.
    | You can find this in your Xendit Dashboard under Settings > Webhooks.
    |
    */

    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Production Mode
    |--------------------------------------------------------------------------
    |
    | Set this to true when you're ready to go live. When false, Xendit will
    | use the test/sandbox environment.
    |
    */

    'is_production' => env('XENDIT_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for Xendit invoices.
    |
    */

    'invoice' => [
        'currency' => 'IDR',
        'invoice_duration' => (int) env('XENDIT_INVOICE_DURATION', 86400), // 24 hours in seconds
        'payment_methods' => ($methods = trim((string) env('XENDIT_INVOICE_PAYMENT_METHODS', 'CREDIT_CARD,BCA,BNI,BSI,BRI,MANDIRI,PERMATA,ALFAMART,INDOMARET,OVO,DANA,SHOPEEPAY,LINKAJA,QRIS'))) !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $methods))))
            : ['CREDIT_CARD','BCA','BNI','BSI','BRI','MANDIRI','PERMATA','ALFAMART','INDOMARET','OVO','DANA','SHOPEEPAY','LINKAJA','QRIS'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | URLs for handling payment callbacks.
    |
    */

    'success_redirect_url' => env('APP_URL') . '/invoicing/invoices',
    'failure_redirect_url' => env('APP_URL') . '/invoicing/invoices',
];
