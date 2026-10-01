<!DOCTYPE html>
<html>
<head>
    <title>Language Test</title>
</head>
<body>
    <h1>Language Test</h1>
    
    <p>Current Locale: {{ app()->getLocale() }}</p>
    <p>Session Locale: {{ session('locale') }}</p>
    
    <h2>Translations:</h2>
    <ul>
        <li>Home: {{ __('app.home') }}</li>
        <li>Courses: {{ __('app.courses') }}</li>
        <li>Hero Title: {!! __('app.hero_title') !!}</li>
    </ul>
    
    <h2>Language Switcher:</h2>
    <a href="{{ route('language.switch', 'en') }}">English</a> | 
    <a href="{{ route('language.switch', 'my') }}">မြန်မာ</a>
    
    <h2>Config:</h2>
    <pre>{{ json_encode(config('app.supported_locales'), JSON_PRETTY_PRINT) }}</pre>
</body>
</html>