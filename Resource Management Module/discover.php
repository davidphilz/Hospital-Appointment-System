<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Discover More | Hospital System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
      color: #333;
    }
    h2 {
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .navbar {
      background-color: #007bff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar-brand, .nav-link {
      color: #fff !important;
    }
    .hero-discover {
      background: url('img/discover_bg.jpg') center/cover no-repeat;
      position: relative;
      min-height: 55vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      overflow: hidden;
    }
    .hero-discover::before {
      content: "";
      position: absolute;
      top: 0; right: 0; bottom: 0; left: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1;
    }
    .hero-discover-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      padding: 20px;
      animation: fadeInDown 1s ease-in-out;
    }
    .hero-discover-content h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #fff;
    }
    .hero-discover-content p {
      font-size: 1.25rem;
      margin-bottom: 20px;
      color: #f1f1f1;
      animation: fadeInUp 1s ease-in-out;
    }
    .hero-discover-content a.btn {
      animation: fadeInUp 1.2s ease-in-out;
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .story-section {
      background-color: #fff;
      padding: 60px 0;
    }
    .story-section img {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .innovation-section {
      background-color: #eef2f7;
      padding: 60px 0;
    }
    .innovation-section .innovation-item {
      transition: transform 0.3s ease;
    }
    .innovation-section .innovation-item:hover {
      transform: translateY(-5px);
    }
    .innovation-item img {
      height: 250px;
      object-fit: cover;
      width: 100%;
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
    footer {
      background-color: #343a40;
      color: #fff;
      padding: 20px 0;
      text-align: center;
    }
  </style>
</head>
<body>
    <?php include ("include/header.php"); ?>
  <section class="hero-discover">
    <div class="hero-discover-content text-center">
      <h1 class="display-4">Discover More About Us</h1>
      <p class="lead">Unveiling Our Journey, Innovations, and Future Vision</p>
      <a href="explore.php" class="btn btn-light btn-lg mt-3">Explore Our Story</a>
    </div>
  </section>

  <section class="story-section" id="our-story">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <h2>Our Story</h2>
          <p>
            Established with a vision to revolutionize healthcare, our hospital has grown from a modest facility into a center of excellence. Our journey is one of passion, dedication, and continuous innovation. We believe that every patient deserves compassionate care and personalized attention.
          </p>
          <p>
            Over the years, we have embraced cutting-edge technology and modern medical practices, ensuring that our community receives top-quality healthcare in a safe and nurturing environment.
          </p>
        </div>
        <div class="col-lg-6">
          <img src="img/our_story.jpg" alt="Our Story" class="img-fluid rounded shadow">
        </div>
      </div>
    </div>
  </section>

  <section class="innovation-section">
    <div class="container">
      <h2 class="text-center mb-5">Our Innovations</h2>
      <div class="row g-4">
        <div class="col-md-4 innovation-item text-center">
          <img src="img/innovation1.jpg" alt="Innovation 1" class="img-fluid rounded mb-3">
          <h5>Digital Health Solutions</h5>
          <p>
            We leverage the latest digital tools to enhance patient care and streamline operations, ensuring efficient and effective healthcare delivery.
          </p>
        </div>
        <div class="col-md-4 innovation-item text-center">
          <img src="img/innovation2.jpg" alt="Innovation 2" class="img-fluid rounded mb-3">
          <h5>State-of-the-Art Equipment</h5>
          <p>
            Our facility is equipped with advanced diagnostic and treatment tools, providing our patients with the best possible care.
          </p>
        </div>
        <div class="col-md-4 innovation-item text-center">
          <img src="img/innovation3.jpg" alt="Innovation 3" class="img-fluid rounded mb-3">
          <h5>Research and Development</h5>
          <p>
            We are constantly exploring new techniques and treatments through rigorous research, pushing the boundaries of modern medicine.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="container my-5">
    <h2 class="text-center mb-4">Patient Success Stories</h2>
    <div class="row">
      <div class="col-md-6">
        <div class="testimonial">
          <p>"The care I received was beyond exceptional. The hospital staff were kind, attentive, and truly dedicated to my recovery."</p>
          <h6>- Sarah L.</h6>
        </div>
      </div>
      <div class="col-md-6">
        <div class="testimonial">
          <p>"Innovative treatments and compassionate service made all the difference in my health journey. I highly recommend this hospital."</p>
          <h6>- Michael R.</h6>
        </div>
      </div>
    </div>
  </section>

  <section class="container my-5">
    <h2 class="text-center mb-4">Our Future Vision</h2>
    <p class="text-center">
      As we look ahead, our mission remains steadfast: to continually evolve and adapt in order to provide unparalleled healthcare. Through innovation, research, and a commitment to excellence, we strive to shape the future of medicine and improve lives.
    </p>
  </section>

  <footer>
    <p class="mb-0">&copy; <span id="year"></span> Hospital System. All rights reserved.</p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>
