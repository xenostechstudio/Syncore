@include('errors._shell', [
    'status'  => 403,
    'title'   => 'Forbidden',
    'message' => "You don't have permission to access this page. If you think this is a mistake, contact your administrator.",
    'cta'     => [
        ['label' => 'Back to home', 'href' => url('/')],
    ],
])
