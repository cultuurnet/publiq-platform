<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>503 | Service Unavailable</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #F5F5F5;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            text-align: center;
            margin-top: 100px;
            flex: 1;
        }

        .header-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 30px;
        }

        .message-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .message-content {
            font-size: 24px;
            margin-bottom: 50px;
        }

        .footer {
            background-color: #009FDE;
            color: white;
            padding: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="/build/assets/Hero-DagNCfZ2.svg" alt="Maintenance Image" class="header-image">

    <div class="message-title">publiq platform is in onderhoud</div>
    <div class="message-content">
        <p>We voeren momenteel onderhoudswerkzaamheden uit.<br> We zijn ermee bezig en zijn terug op maandag 4/11/2024 om 08:00 uur.</p>
        <p>We are currently performing maintenance work.<br> We are working on it and will be back on Monday, 4/11/2024, at 8:00 AM.</p>
    </div>
</div>

<footer class="footer">
    &copy; publiq vzw
</footer>
</body>
</html>
