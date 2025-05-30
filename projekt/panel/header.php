<?php
if (!isset($_SESSION)) session_start();
require_once "inc/security.php";
require_once'inc/config.php'; 
require_once'inc/functions.php';
requireAdmin();

 
?>
  
 <?php
 


$pendingOrdersCount = getPendingOrdersCount($pdo);
?>

 
 




<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Ikony (opcjonalne) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #fff;
          <!--  font-family: 'Segoe UI', sans-serif;
			font-family: monospace ;
			 -->
		 
  
  
  font-family: "Roboto", Arial, serif;
        }
        .navbar {
            background-color: #e0e6ed;
            box-shadow: 0 0px 6px rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: bold;
            color: #1a1a1a;
        }
        .nav-link {
            color: #333;
        }
        .nav-link:hover {
            color: #0d6efd;
        }
        .admin-meta {
            font-size: 0.9rem;
            margin-left: auto;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
		
		 .card {
            border-radius: 0px;
        }
         
    </style>
</head>
<body>

 



 
<style>
/*--====== Global Variables  #161623;======--*/
:root {
  --bg-color: #13131f;
  --bg-color-2: #fff;  
  --text-color: #a9afc3;
}
/*--====== Sidebar ======--*/
#sidebar {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 99999;
  max-width: 300px;
  width: 80%;
  height: 100%;
  padding: 2rem;
  background-color: var(--bg-color-2);
  box-shadow: 0 10px 20px -4px #000;
  overflow-x: hidden;
  overflow-y: auto;
  pointer-events: none;
  opacity: 0;
  visibility: hidden;
  transform: translateX(-100%);
  transition: opacity 0.1s ease, visibility 0.2s ease, transform 0.1s ease;
}
/* when the sidebar has 'show' class */
#sidebar.show {
  pointer-events: all;
  opacity: 1;
  visibility: visible;
  transform: translateX(0);
}
.sidebar_content {
  padding: 2.8rem 0;
  pointer-events: none;
  /* so that the Sidebar does not get closed while clicking on sidebar_content */
}
.sidebar_content a {
  pointer-events: all;
  /* so that all the a inside sidebar_content are clickable */
}
.sidebar_body {
  border-top: 1px dashed var(--text-color);
  border-bottom: 1px dashed var(--text-color);
}
.side_navlinks ul {
  display: grid;
  gap: 2rem;
}
.side_navlinks li a {
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0.8;
}
.side_navlinks a:hover {
  opacity: 1;
}
 
.sidebarm ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
}
.sidebarm li {
    list-style-type: none;
}
 

/*---- Sidebar-Toggler ----*/
.sidebar_toggler {
  position: fixed;
  top: 4vh;
  right: 6vw;
  width: 2rem;
  height: 1.7rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  cursor: pointer;
  z-index: 99999;
  overflow: hidden;
}
.sidebar_toggler span {
  background-color: #000;
  width: 100%;
  height: 2.0px;
  transition: all 0.1s ease;
  pointer-events: none;
  /* so that it doesn't overlap the sidebar_toggler */
}
/* if the sidebar has 'show' class then their adjacent-sibling (i.e., sidebar_toggler) will... */
#sidebar.show + .sidebar_toggler {
  justify-content: center;
}
#sidebar.show + .sidebar_toggler span {
  margin-top: -1.2px;
  margin-bottom: -1.2px;
}
#sidebar.show + .sidebar_toggler span:first-child {
  transform: rotate(45deg);
}
#sidebar.show + .sidebar_toggler span:nth-child(2) {
  opacity: 0;
  transform: translateX(-100%);
}
#sidebar.show + .sidebar_toggler span:last-child {
  transform: rotate(-45deg);
}
</style>


 



<aside id="sidebar"><a class="nav-link text-danger" href="logout.php">🚪 Wyloguj</a>
  <div class="sidebar_content sidebar_head">
  
    <span><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['admin']['email']) ?></span>
    <span><i class="bi bi-calendar3"></i> <?= date("d.m.Y") ?></span>
    <span><i class="bi bi-clock"></i> <span id="liveClock">--:--:--</span></span>
  </div>
  <div class="sidebar_content sidebar_body">
    <nav class="side_navlinks">
      <ul class="sidebarm">
	  
 
 
 

                 
	  
                <li class="nav-item"><a class="nav-link" href="index.php">📈 Statystyki </a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">🧾 Zamówienia <?php if ($pendingOrdersCount > 0): ?>
      <span class="badge bg-danger"><?= $pendingOrdersCount ?></span>
    <?php endif; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="users.php">👥 Użytkownicy</a></li>
                <li class="nav-item"><a class="nav-link" href="completed.php">✅ Zrealizowane</a></li>
                <li class="nav-item"><a class="nav-link" href="memories.php">🧠 Wspomnienia</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">🛍️ Produkty</a></li>
                <li class="nav-item"><a class="nav-link" href="messages.php">✉️ Wiadomości</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php">⚙️ Ustawienia</a></li>
				
				 <li class="nav-item"><a class="nav-link" href="all_qr_codes.php">🧬 Wszystkie QR</a></li>	
				
				
				
				 
			    <li class="nav-item"><a class="nav-link" href="registration_log.php">📈 Rejestracja-logi</a></li>				
				
                <li class="nav-item"><a class="nav-link" href="logins.php">🔐 Logowania-logi</a></li>
                <li class="nav-item"><a class="nav-link" href="add.admin.php">➕ Nowy admin</a></li>
              
      </ul>
  </nav>
  </div>
 
</aside>
 

  
 
 
 
 <!--  -->
<div class="sidebar_toggler">
  <span></span>
  <span></span>
  <span></span>
 
</div>
  <div id="motyw"> 
   <?php if ($pendingOrdersCount > 0): ?>
      <?= $pendingOrdersCount ?>
    <?php endif; ?>
</div> 
  <!--
<div id="session-timer" style="position:fixed; bottom:10px; right:10px; background:#f44336; color:white; padding:5px 12px; border-radius:0px; z-index:9999; font-family: monospace ;">
  Sesja: <span id="timer">60:00</span>
</div>


 -->

 
 
 
 
 
 
 
<style>
  #motyw {
    position: fixed;
    right: 1%;
    top: 5%;
     font-family: monospace ;
  color: red;
  font-size: 35px;
    z-index: 5;
</style> 

<script>
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('liveClock').textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock(); // immediate
</script>
