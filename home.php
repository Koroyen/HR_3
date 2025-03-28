
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="description" content="" />
  <meta name="author" content="" />
  <title>Home Page</title>
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
  <link href="css/styles.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
    crossorigin="anonymous">
  <!-- Bootstrap JS (v5.1 or newer) -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="sb-nav-fixed bg-dark">
  
  <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="home.php">Ascenders business services</a>
       
  </nav>


   <!-- Modal for Notifications -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Your Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add the correct ID for the notification content -->
                <div id="notification-content">
                    <!-- Notifications will be dynamically loaded here via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>





<div id="hero-carousel" class="carousel slide" data-bs-ride="carousel" style="width: 100%; height: 100%; min-height: 50vh;">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="1" aria-label="Slide 1"></button>
     
      
    </div>

    <div class="carousel-inner">
      <div class="carousel-item active" style="height: 100vh; min-height: 300px;">
        <img src="hired.jpg" class="d-block w-100" alt="Slide 1" style="height: 100%; object-fit: cover; filter: brightness(0.6);">
        <div class="carousel-caption top-0 mt-4">
          <p class="text-uppercase fs-3 mt-5">WE ARE HIRING</p>
          <p class="display-1 fw-bolder text-capitalize">BE PART OF OUR TEAM</p>
          <button class="btn px-4 py-2 fs-5 mt-5"><a href="hire.php">Click here</a></button>
        </div>
      </div>

     

      <div class="carousel-item" style="height: 100vh; min-height: 300px;">
        <img src="qualifications.jpg" class="d-block w-100" alt="Slide 2" style="height: 100%; object-fit: cover; filter: brightness(0.6);">
         <div class="carousel-caption top-0 mt-4">
          <p class="mt-5 fs-3 text-uppercase"></p>
          <h1 class="display-1 fw-bolder text-capitalize"></h1>
        </div>
      </div>
    </div>
   

    <button class="carousel-control-prev" type="button" data-bs-target="#hero-carousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#hero-carousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>


  <footer class="py-4 bg-light mt-auto bg-dark">
    <div class="container-fluid px-4">
      <div class="d-flex align-items-center justify-content-between small">
        <div class="text-muted">Copyright &copy; Your Website 2023</div>
        <div>
          <!-- <a href="#" class="text-muted">Privacy Policy</a>
                        &middot;
                        <a href="#" class="text-muted">Terms &amp; Conditions</a> -->
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="js/scripts.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
  <!-- <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
  <script src="js/datatables-simple-demo.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>
</body>

</html>