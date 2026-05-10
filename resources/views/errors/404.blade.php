@include('errors._shell', [
    'status'  => 404,
    'title'   => 'Page not found',
    'message' => "The page you're looking for doesn't exist or was moved. Check the URL, or head back to the dashboard.",
    'cta'     => [
        ['label' => 'Back to home', 'href' => url('/')],
    ],
])
