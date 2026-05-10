@include('errors._shell', [
    'status'  => 503,
    'title'   => 'Service unavailable',
    'message' => "We're temporarily down for maintenance or recovering from an issue. We'll be back shortly — please try again in a few minutes.",
    'cta'     => [],
])
