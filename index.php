<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="description" content="" />
  <meta name="author" content="" />
  <title>Landing Page</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
    crossorigin="anonymous">
  <link href="css/styles.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="d-flex flex-column min-vh-150 bg-dark" style="background-image: url('hiring.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
  
  <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
      
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-5 text-white" href="home.php">Microfinance</a>
    <a class="navbar-brand ps-5 ms-auto text-white" href="home.php">Get Started</a>
    <a class="navbar-brand ps-5 ms-auto text-white" href="login.php">Login</a>
   
  </nav>

 <div id="hero-carousel" class="carousel slide" data-bs-ride="carousel" style="width: 100%; height: 100%; min-height: 50vh;">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="1" aria-label="Slide 1"></button>
     
      
    </div>

    <div class="carousel-inner">
      <div class="carousel-item active" style="height: 100vh; min-height: 300px;">
        <img src="hired.jpg" class="d-block w-100" alt="Slide 1" style="height: 100%; object-fit: cover; filter: brightness(0.8);">
        <div class="carousel-caption top-0 mt-4">
          <p class="text-uppercase fs-3 mt-5">WE ARE HIRING</p>
          <p class="display-1 fw-bolder text-capitalize">BE PART OF OUR TEAM</p>
          
        </div>
      </div>

     

      <div class="carousel-item" style="height: 100vh; min-height: 300px;">
        <img src="qualifications.jpg" class="d-block w-100" alt="Slide 2" style="height: 100%; object-fit: cover; filter: brightness(0.8);">
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

  <footer class="py-4 bg-dark mt-auto">
    <div class="container-fluid px-4">
      <div class="d-flex align-items-center justify-content-between small">
        <div class="text-light">Copyright &copy; Your Website 2023</div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>