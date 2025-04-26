
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Learn More | Hospital System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f8f9fa;
      color: #333;
    }
    h2 {
      font-weight: 600;
    }

    .navbar {
      background-color: #007bff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar-brand, .nav-link {
      color: #fff !important;
    }

    .hero-learn {
      background: url('img/learn_more_bg.jpg') center/cover no-repeat;
      position: relative;
      min-height: 55vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      overflow: hidden;
    }
    .hero-learn::before {
      content: "";
      position: absolute;
      top: 0; right: 0; bottom: 0; left: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1;
    }
    .hero-learn-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      padding: 20px;
    }
    .hero-learn-content h1 {
      font-size: 3rem;
      font-weight: 700;
      animation: fadeInDown 1s ease-in-out;
    }
    .hero-learn-content p {
      font-size: 1.25rem;
      margin-bottom: 20px;
      animation: fadeInUp 1s ease-in-out;
    }
    .hero-learn-content a.btn {
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
    
    .about-section img {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .feature-icon {
      font-size: 3rem;
      color: #007bff;
      margin-bottom: 15px;
      transition: transform 0.3s ease;
    }
    .feature-icon:hover {
      transform: scale(1.1);
    }

    .accordion-button {
      background-color: #e9ecef;
      color: #333;
      font-weight: 500;
    }
    .accordion-button:not(.collapsed) {
      background-color: #007bff;
      color: #fff;
    }
    .accordion-body {
      background-color: #f8f9fa;
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

    .departments li {
      margin-bottom: 10px;
      padding-left: 1.2rem;
      position: relative;
    }
    .departments li::before {
      content: "\f111"; 
      font-family: "Font Awesome 6 Free";
      font-weight: 900;
      position: absolute;
      left: 0;
      top: 2px;
      font-size: 0.6rem;
      color: #007bff;
    }
    

    .contact-form label {
      font-weight: 500;
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
  
  <section class="hero-learn">
    <div class="hero-learn-content text-white">
      <h1 class="display-4">Learn More About Our Hospital</h1>
      <p class="lead">Committed to Excellence in Healthcare</p>
      <a href="discover.php" class="btn btn-light btn-lg mt-3">Discover More</a>
    </div>
  </section>
  
  <div class="container my-5 about-section" id="about">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h2>Who We Are</h2>
        <p>
          Our hospital is dedicated to delivering compassionate, high-quality healthcare services to our community. With a team of experienced doctors, nurses, and support staff, we ensure every patient receives personalized attention and exceptional care.
        </p>
        <p>
          Equipped with state-of-the-art technology and driven by a commitment to innovation, our facility offers everything from emergency care to specialized treatments. We believe in holistic healing and strive to create a nurturing environment for all our patients.
        </p>
      </div>
      <div class="col-lg-6">
        <img src="img/about_hospital.jpg" alt="About Our Hospital" class="img-fluid rounded shadow" />
      </div>
    </div>
  </div>
  <div class="container my-5">
    <h2 class="text-center mb-4">Key Features</h2>
    <div class="row text-center g-4">
      <div class="col-md-4">
        <i class="fa fa-user-md feature-icon"></i>
        <h5 class="mt-2">Expert Staff</h5>
        <p>
          Our team comprises top specialists and dedicated professionals focused on delivering unparalleled patient care.
        </p>
      </div>
      <div class="col-md-4">
        <i class="fa fa-heartbeat feature-icon"></i>
        <h5 class="mt-2">Comprehensive Services</h5>
        <p>
          From routine checkups to complex surgeries, our hospital offers a full spectrum of healthcare services.
        </p>
      </div>
      <div class="col-md-4">
        <i class="fa fa-laptop-medical feature-icon"></i>
        <h5 class="mt-2">Advanced Technology</h5>
        <p>
          We leverage cutting-edge medical technology and digital solutions to ensure accurate diagnoses and effective treatments.
        </p>
      </div>
    </div>
  </div>
  
  <div class="container my-5">
    <h2 class="text-center mb-4">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqOne">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
            How do I book an appointment?
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Book an appointment by calling our front desk at +234 808 155 7258 or using our online booking system available on our website.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqTwo">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            What insurance plans do you accept?
          </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            We accept most major health insurance plans. For specific details, please contact our billing department.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqThree">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            What are your visiting hours?
          </button>
        </h2>
        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Visiting hours are from 8:00 AM to 8:00 PM daily, though some departments may have specific schedules.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqFour">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
            Do I need a referral to see a specialist?
          </button>
        </h2>
        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="faqFour" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            In most cases, a referral from your primary care physician is required to see a specialist. Please verify with our appointment desk.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqFive">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
            How can I access my medical records online?
          </button>
        </h2>
        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="faqFive" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Once you create an account, log in to our patient portal to view your medical records, appointment history, and more.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqSix">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
            What COVID-19 protocols are in place?
          </button>
        </h2>
        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="faqSix" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            We adhere to strict COVID-19 safety guidelines including mandatory mask usage, social distancing, and regular sanitization across all facilities.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container my-5">
    <h2 class="text-center mb-4">Our Departments</h2>
    <div class="row">
      <div class="col-md-4">
        <h5>Emergency Care</h5>
        <p>
          Open 24/7, our emergency department is equipped with advanced life-saving technology and highly trained staff to respond to urgent situations.
        </p>
      </div>
      <div class="col-md-4">
        <h5>Surgery</h5>
        <p>
          Our surgical team utilizes modern techniques and minimally invasive procedures to ensure patient safety and a speedy recovery.
        </p>
      </div>
      <div class="col-md-4">
        <h5>Maternity</h5>
        <p>
          Our maternity ward offers comprehensive prenatal, delivery, and postnatal care in a comfortable and supportive environment.
        </p>
      </div>
    </div>
    <ul class="departments list-unstyled mt-4">
      <li><strong>Radiology:</strong> Cutting-edge imaging services for accurate diagnosis.</li>
      <li><strong>Pediatrics:</strong> Compassionate care for our youngest patients.</li>
      <li><strong>Rehabilitation:</strong> Tailored programs for recovery and wellness.</li>
    </ul>
  </div>

  <div class="container my-5">
    <h2 class="text-center mb-4">Testimonials</h2>
    <div class="row">
      <div class="col-md-6">
        <div class="testimonial">
          <p>"The care I received was extraordinary. The staff were kind, professional, and truly made me feel at home during my recovery."</p>
          <h6>- Sarah L.</h6>
        </div>
      </div>
      <div class="col-md-6">
        <div class="testimonial">
          <p>"State-of-the-art facilities and compassionate care define this hospital. I highly recommend it to anyone in need of quality healthcare."</p>
          <h6>- Michael R.</h6>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <p class="mb-0">
      &copy; <span id="year"></span> Hospital System. All rights reserved.
    </p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>
