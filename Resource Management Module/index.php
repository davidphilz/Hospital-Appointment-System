<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Hospital System | Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");

    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f8f9fa;
      display: flex;
      flex-direction: column;
      color: #333;
    }
    main {
      flex: 1;
    }
    h2 {
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .hero {
      background: url('img/hos.jpg') center/cover no-repeat;
      position: relative;
      min-height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      overflow: hidden;
    }
    .hero-overlay {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      background-color: rgba(0, 0, 0, 0.5);
    }
    .hero-content {
      position: relative;
      z-index: 1;
      max-width: 700px;
      padding: 20px;
      color: #fff;
    }
    .hero-content h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .hero-content p {
      font-size: 1.2rem;
      margin-bottom: 20px;
    }
    
    /* About Section */
    .about-section {
      background: #fff;
      padding: 60px 0;
    }
    .about-section .about-img {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Services Section */
    .services-section {
      background: #eef2f7;
      padding: 60px 0;
    }
    .service-card {
      transition: transform 0.3s ease;
    }
    .service-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .testimonials-section {
      background: #fff;
      padding: 60px 0;
    }
    .testimonial {
      background-color: #fff;
      border: 1px solid #ddd;
      padding: 25px;
      border-radius: 8px;
      margin-bottom: 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }
    .testimonial:hover {
      transform: translateY(-5px);
    }
    .testimonial p {
      font-style: italic;
      color: #555;
    }
    .testimonial h6 {
      margin-top: 15px;
      font-weight: 600;
      color: #007bff;
    }

    .news-section {
      background: #f1f1f1;
      padding: 60px 0;
    }
    .news-item {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 30px;
      transition: transform 0.3s ease;
    }
    .news-item:hover {
      transform: translateY(-5px);
    }
    .news-item h5 {
      font-weight: 600;
    }

    .info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    .card img {
      height: 220px;
      object-fit: cover;
    }
 
    footer {
      background-color: #343a40;
      color: #fff;
      padding: 20px 0;
      text-align: center;
    }
  </style>
</head>
<body>
  <?php include("include/header.php"); ?>

  <main>
    <section class="hero d-flex align-items-center justify-content-center">
      <div class="hero-overlay"></div>
      <div class="hero-content text-center">
        <h1>Welcome to Our Hospital</h1>
        <p class="lead">Quality Care for a Healthier Community</p>
        <a href="learn_more.php" class="btn btn-light btn-lg mt-3">Learn More</a>
      </div>
    </section>

    <section class="about-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <h2>About Our Hospital</h2>
            <p>
              Our hospital is dedicated to providing comprehensive healthcare services to the community.
              With state-of-the-art facilities and a compassionate team, we strive to ensure every patient
              receives the highest standard of care.
            </p>
            <p>
              We are committed to innovation, excellence, and continuous improvement in patient care.
              Whether for routine checkups or specialized treatments, your health is our priority.
            </p>
          </div>
          <div class="col-lg-6">
            <img src="img/about_hospital1.jpg" alt="About Our Hospital" class="img-fluid about-img">
          </div>
        </div>
      </div>
    </section>

    <section class="services-section">
      <div class="container">
        <h2 class="text-center mb-5">Our Services</h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card service-card h-100 text-center">
              <img src="img/service1.jpg" alt="General Medicine" class="img-fluid">
              <div class="card-body">
                <h5 class="card-title">General Medicine</h5>
                <p class="card-text">
                  Comprehensive healthcare services for routine checkups and chronic conditions.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card service-card h-100 text-center">
              <img src="img/service2.jpg" alt="Emergency Care" class="img-fluid">
              <div class="card-body">
                <h5 class="card-title">Emergency Care</h5>
                <p class="card-text">
                  24/7 emergency services equipped with modern technology and skilled professionals.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card service-card h-100 text-center">
              <img src="img/service3.jpg" alt="Surgery & Specialized Care" class="img-fluid">
              <div class="card-body">
                <h5 class="card-title">Surgery & Specialized Care</h5>
                <p class="card-text">
                  Advanced surgical procedures and specialized treatments delivered with precision.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="testimonials-section">
      <div class="container">
        <h2 class="text-center mb-5">Patient Testimonials</h2>
        <div class="row">
          <div class="col-md-6">
            <div class="testimonial">
              <p>"The care I received was extraordinary. The staff made me feel valued and supported throughout my treatment."</p>
              <h6>- Sarah L.</h6>
            </div>
          </div>
          <div class="col-md-6">
            <div class="testimonial">
              <p>"Innovative treatments and a compassionate team defined my experience. I highly recommend this hospital."</p>
              <h6>- Michael R.</h6>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="news-section">
      <div class="container">
        <h2 class="text-center mb-5">Latest News</h2>
        <div class="row">
          <div class="col-md-4">
            <div class="news-item">
              <h5>New Cardiology Unit Opens</h5>
              <p>
                Our hospital proudly announces the opening of our state-of-the-art Cardiology Unit,
                providing cutting-edge heart care to our community.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="news-item">
              <h5>Telemedicine Services Launched</h5>
              <p>
                Embracing digital innovation, we now offer telemedicine services to make healthcare more accessible.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="news-item">
              <h5>Annual Health Fair Scheduled</h5>
              <p>
                Join us for our Annual Health Fair, featuring free checkups, expert consultations, and wellness workshops.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="container py-5">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card info-card h-100 text-center">
            <img src="img/more information.png" alt="More Information" class="img-fluid" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">For More Information</h5>
              <p class="card-text">
                Discover everything you need to know about our hospital services, facilities, and patient care.
              </p>
              <a href="information.php" class="btn btn-success">Click Here</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card info-card h-100 text-center">
            <img src="img/patient.jpg" alt="Patient" class="img-fluid" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Create Account For More Care</h5>
              <p class="card-text">
                Sign up to access your medical records, schedule appointments, and manage your health online.
              </p>
              <a href="patientsignup.php" class="btn btn-primary">Create Account</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card info-card h-100 text-center">
            <img src="img/doctor.jpg" alt="Staff" class="img-fluid" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Staff Login</h5>
              <p class="card-text">
                Authorized personnel can sign in to manage patient data, resources, and other administrative tasks.
              </p>
              <a href="staff/staff_login.php" class="btn btn-warning">Start Now!</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <div class="container">
      <p class="mb-0">&copy; <span id="year"></span> Hospital System. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>
