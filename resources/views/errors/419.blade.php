@include('errors._shell', [
    'status'  => 419,
    'title'   => 'Session expired',
    'message' => 'Your session timed out for security. Refresh the page and try again — your work is safe.',
    'cta'     => [
        ['label' => 'Refresh', 'href' => url()->previous()],
        ['label' => 'Back to home', 'href' => url('/'), 'secondary' => true],
    ],
])
