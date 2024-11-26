<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
      /* General Styles */
      body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        color: #333;
        margin: 0;
        padding: 0;
      }

      .navbar {
        background-color: #007bff;
        color: #fff;
      }

      .navbar-brand {
        font-size: 1.5rem;
        color: #fff;
        font-weight: bold;
      }

      .navbar-nav .nav-link {
        color: #fff;
        font-weight: 500;
        transition: color 0.3s ease;
      }

      .navbar-nav .nav-link:hover {
        color: #ffd700;
      }

      /* Landing Page Styles */
      .landing-page {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        height: 100vh;
        background: linear-gradient(to right, #007bff, #00c6ff);
        color: #fff;
      }

      .landing-page h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
      }

      .landing-page p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
      }

      .btn-primary {
        background-color: #ffd700;
        border: none;
        color: #333;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: bold;
        transition: background-color 0.3s ease;
      }

      .btn-primary:hover {
        background-color: #ffcc00;
        color: #fff;
      }
    </style>
    <title>Youth Ministry</title>
  </head>

  <body>
    <div class="landing-page">
      <h1>Welcome to the Youth Ministry!</h1>
      <p>Join the community and grow in faith together.</p>
      <a href="youth/register.php" class="btn btn-primary">Get Started</a>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
  </body>

</html>