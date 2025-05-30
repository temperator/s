<?php
require_once "inc/config.php";
require_once "inc/security.php";
require_once "inc/functions.php";

requireAdmin();








?>




<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>📊 Dashboard Administratora</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  
   
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
    body { background: #f7f7f7; font-family: 'Segoe UI', sans-serif; }
    .card { border: none; border-radius: 6px; box-shadow: 0 0 5px rgba(0,0,0,0.00); }
    .section-title { margin: 30px 0 15px; border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 1.25rem; }
    .card-title { font-weight: 500; font-size: 1rem; } 
    .metric { font-size: 2rem; font-weight: ; }
    #map { height: 300px; width: 100%; border-radius: 0px; }
  </style>
</head>
<body>


<?php include 'header.php'; ?>
<main class="container mt-5">

	
  <h2 class="mb-4"><center>📊 STATYSTYKI</center></h2>
  
  
 <div class="row text-centver">
 
 
 
  <!-- 🔷 Przegląd statystyk -->
 
    <h4 class="section-title">📊 Przegląd statystyk</h4>
    <?php include 'widgets/stats_overview.php'; ?>
  

  <!-- 👤 Użytkownicy & logowania -->

    <h4 class="section-title">🧑‍💻 Użytkownicy & logowania</h4>
    <?php include 'widgets/users_activity.php'; ?>
    <?php include 'widgets/top_users.php'; ?>


  <!-- 💳 Płatności -->

    <h4 class="section-title">💳 Płatności</h4>
    <?php include 'widgets/payments_stats.php'; ?>
  

  <!-- 🧠 Wspomnienia -->
  
    <h3 class="section-title">🧠 Wspomnienia</h3>
    <?php include 'widgets/memories_chart.php'; ?>
   
  <h3 class="section-title">⏰ Wygasające wspomnienia</h3>
    <?php include 'widgets/expiring_memories.php'; ?>
      
    
  <!-- 🌍 Odwiedziny -->
 
    <h3 class="section-title">🌍 Mapa odwiedzin & IP</h3>
    <?php include 'widgets/visitors_map.php'; ?>
  

  <!-- 💾 Dysk / pliki -->
   
    <h3 class="section-title">💾 Dysk & załączniki</h3>
    <?php include 'widgets/files.php'; ?> 
 

  
   <!-- 🚨 Alerty -->
   
    <h3 class="section-title">🚨 Alerty & Problemy</h3>
    <?php include 'widgets/anomalies.php'; ?>
 
  
   
  <!-- 🚨 Alerty  r-->
  
    <h3 class="section-title">🚨 Logi prób rejestracji</h3>
    <?php include 'widgets/registration_logs.php'; ?>
    
  
  <!-- 🚨 Alerty  r-->
 
    <h3 class="section-title">🚨 Historia logowań</h3>
    <?php include 'widgets/login_history.php'; ?>
   
   
   
  
  </div>
   
 
</main>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<!-- Można tu wstrzykiwać JS dla wykresów, map itd. -->
</body>
</html>
