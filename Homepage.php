<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .intro-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .intro-text {
            max-width: 45%;
        }

        .intro-image {
            max-width: 45%;
            margin: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

    </style>
</head>
<body>
<?php include 'navbar.html'; ?>


<div class="intro-container">
    <div class="intro-text">
        <h1>Welcome to the Hospital Employee Database!</h1>
        <h2>This interactive database provides comprehensive information about hospital employees. Through the navigation bar, you can access, edit, and manage various components of the database, such as employee details, facilities, infection reports, work schedules, and vaccination records. Explore the database to find the information you need, and ensure that your hospital maintains a safe and efficient work environment for all employees.</h2>
    </div>
    <img src="https://images.ctfassets.net/lbgy40h4xfb7/2f8naZ0upUmUYcbwzzRE6c/aa060cfe9951f2e570872c7fa5c13963/Doctor-holding-tablet.jpeg?w=1200&h=720&fl=progressive&q=90&fm=jpg" alt="Hospital" class="intro-image">
</div>
</body>
</html>