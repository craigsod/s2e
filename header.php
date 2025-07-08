<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}
?><!DOCTYPE HTML>
<html>
<head>
<link href='https://fonts.googleapis.com/css?family=Oswald:400,300,700|Oxygen:400,300,700' rel='stylesheet' type='text/css'>
<title><?php echo $_SESSION['page_title']; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="Keywords" content=""/>

<meta name="Description" content=""/>
    
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="style-kpdc.css" rel="stylesheet" type="text/css" />
<link href="custom-css.css" rel="stylesheet" type="text/css" />

</head>  
<body>
<header class="main-header bg-black">
            <nav class="navbar bg-white">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle marg-t-30" data-toggle="collapse" data-target="#mainNav">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span> 
                        </button>
                        <a class="navbar-brand" href="index.html"><img src="images/s2e_logo2.png" alt="Simple2Edit's Dance Company" width="242" height="100" border="0"></a>
                    </div>
                    <div class="collapse navbar-collapse" id="mainNav">
                        <ul class="nav navbar-nav d-marg-t-30">
 
                  <li><a href="kpdc_aboutus.htm">ABOUT US</a></li>
        <li><a href="kpdc_class-descriptions.htm">CLASSES</a></li>
        <li><a href="kpdc_class-schedule.htm">SCHEDULE</a></li>
       <li><a href="kpdc_rates-policies.htm">RATES &amp; POLICIES</a></li>
        <li><a href="kpdc_calendar.htm">CALENDAR</a></li>
        <li><a href="kpdc_recital-information.htm">RECITAL INFO</a></li>
        <li><a href="kpdc_rentals.htm">RENT THE STUDIO</a></li>
        <li><a href="kpdc_register.htm">REGISTER</a></li>

                        </ul>
                    </div>
                </div>
            </nav>
        </header>
<div class="teal-bar mobile-hide" ></div>
<div class="mobile-hide">
    <img src="images/<?php echo $_SESSION['header_image']; ?>" style="width:100%;" alt="">
</div>
<div class="blue-bar" style="height: 3px;"></div>
<div class="container-fluid home-teal-address text-center">
    <p style="padding-top: 20px;" class="headerwhitelg"><?php echo strtoupper($_SESSION['page_name']); ?></p>
</div>
<div class="blue-bar" style="height: 1px;"></div>
<div class="body-section bodytext pad-top-h pad-b-h" style="background:#fff;">
          <div class="container">