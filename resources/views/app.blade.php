<!--

    This Blade template is the main layout file for a Laravel application using Inertia.js and React.
    It sets up the HTML structure and includes necessary meta tags, fonts, and scripts.

    - The HTML language attribute is dynamically set based on the application's locale.
    - The document's character set is set to UTF-8.
    - The viewport meta tag ensures the page is responsive on all devices.
    - The title of the document is set using the application's name from the configuration, with a fallback to 'Laravel'.
    - Fonts are preconnected and loaded from Bunny Fonts.
    - The @routes directive generates the necessary JavaScript routes for the application.
    - The @viteReactRefresh directive enables hot module replacement for React components.
    - The @vite directive includes the main JavaScript file and dynamically includes the page-specific component.
    - The @inertiaHead directive includes Inertia.js specific head elements.
    - The body of the document uses the 'font-sans' and 'antialiased' classes for styling.
    - The @inertia directive renders the Inertia.js root component.
-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
