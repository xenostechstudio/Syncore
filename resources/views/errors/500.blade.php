@include('errors._shell', [
    'status'  => 500,
    'title'   => 'Something went wrong',
    'message' => "We hit an unexpected error. The team has been notified — please try again in a moment.",
    'cta'     => [
        ['label' => 'Back to home', 'href' => url('/')],
    ],
])
