<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark navbar-light fixed-top">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
  </ul>
  <a href="index3.html" class="navbar-brand">
    <span class="brand-text font-weight-light">Crane and Trucking Management System </span>
</a>
  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
    <div class="login-user text-light font-weight-bolder">Welcome <?= $_SESSION['display_name'] ?>!</div> 
    </li>
    <li class="nav-item">
      <img 
         src="user_images/<?php echo $_SESSION['profile_picture'];?>" class="img-circle elevation-2" alt="User Image" 
         style="width:40px; height:40px; object-fit: cover;">
        <style> 
        .main-header.navbar {
                 height: 60px;               /* force height */
                 min-height: 60px;
                 padding-top: 0 !important;  /* remove extra padding */
                 padding-bottom: 0 !important;
                 line-height: 60px;          /* vertically align text/icons */
      }
      .main-header .nav-link,
      .main-header .brand-text,
      .main-header .navbar-nav .nav-item {
               line-height: 60px;          /* make sure links fit */
       }
      </style>
    
      </li>
  </ul>
</nav>
<!-- /.navbar -->
